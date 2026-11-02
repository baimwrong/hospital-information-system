<?php

namespace Tests\Feature;

use App\Models\Admission;
use App\Models\Bed;
use App\Models\Department;
use App\Models\Doctor;
use App\Models\MedicalDocument;
use App\Models\Medicine;
use App\Models\MedicineBatch;
use App\Models\Patient;
use App\Models\PatientVital;
use App\Models\Prescription;
use App\Models\PrescriptionItem;
use App\Models\User;
use App\Models\Ward;
use App\Services\SequenceGenerator;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AdvancedHospitalFeaturesTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected User $doctorUser;

    protected Doctor $doctor;

    protected Patient $patient;

    protected Ward $ward;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create([
            'role' => 'admin',
            'is_active' => true,
        ]);

        $this->doctorUser = User::factory()->create([
            'role' => 'doctor',
            'is_active' => true,
        ]);

        $dept = Department::create([
            'code' => 'SURG',
            'name' => 'Surgery',
            'is_active' => true,
        ]);

        $this->doctor = Doctor::create([
            'user_id' => $this->doctorUser->id,
            'department_id' => $dept->id,
            'specialization' => 'General Surgeon',
            'license_number' => 'SURG-LIC-100',
            'consultation_fee' => 80.00,
            'status' => 'active',
        ]);

        $this->patient = Patient::create([
            'mrn' => 'MRN-2026-0001',
            'first_name' => 'Edward',
            'last_name' => 'Kenway',
            'phone' => '+1-555-1234',
            'date_of_birth' => '1982-03-10',
            'gender' => 'male',
            'status' => 'active',
        ]);

        $this->ward = Ward::create([
            'name' => 'Surgical Inpatient Ward',
            'type' => 'surgical',
            'capacity' => 2,
            'daily_rate' => 150.00,
            'is_active' => true,
        ]);
    }

    public function test_staff_can_update_bed_status_from_cleaning_to_available(): void
    {
        $bed = Bed::create([
            'ward_id' => $this->ward->id,
            'bed_number' => 'SUR-01',
            'status' => 'cleaning',
            'daily_rate' => 150.00,
        ]);

        $response = $this->actingAs($this->admin)->post(route('inpatient.beds.status', $bed), [
            'status' => 'available',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('beds', [
            'id' => $bed->id,
            'status' => 'available',
        ]);

        // Attempting to directly update an occupied bed should be blocked
        $bed->update(['status' => 'occupied']);
        $badResponse = $this->actingAs($this->admin)->post(route('inpatient.beds.status', $bed), [
            'status' => 'available',
        ]);
        $badResponse->assertSessionHasErrors('error');
    }

    public function test_staff_can_transfer_patient_to_available_bed(): void
    {
        $bed1 = Bed::create([
            'ward_id' => $this->ward->id,
            'bed_number' => 'SUR-01',
            'status' => 'occupied',
            'daily_rate' => 150.00,
        ]);

        $bed2 = Bed::create([
            'ward_id' => $this->ward->id,
            'bed_number' => 'SUR-02',
            'status' => 'available',
            'daily_rate' => 150.00,
        ]);

        $admission = Admission::create([
            'admission_number' => 'ADM-2026-0001',
            'patient_id' => $this->patient->id,
            'doctor_id' => $this->doctor->id,
            'bed_id' => $bed1->id,
            'admission_date' => now(),
            'admission_reason' => 'Post-operative recovery',
            'status' => 'admitted',
        ]);

        $response = $this->actingAs($this->doctorUser)->post(route('inpatient.admissions.transfer', $admission), [
            'destination_bed_id' => $bed2->id,
            'transfer_reason' => 'Patient requested window bed in ward',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('beds', ['id' => $bed1->id, 'status' => 'cleaning']);
        $this->assertDatabaseHas('beds', ['id' => $bed2->id, 'status' => 'occupied']);
        $this->assertDatabaseHas('admissions', ['id' => $admission->id, 'bed_id' => $bed2->id]);
    }

    public function test_sequence_generator_produces_atomic_sequential_numbers(): void
    {
        $mrn1 = SequenceGenerator::generate('MRN', 'patients', 'mrn', 4, true);
        $mrn2 = SequenceGenerator::generate('MRN', 'patients', 'mrn', 4, true);

        $this->assertNotEquals($mrn1, $mrn2);
        $this->assertMatchesRegularExpression('/^MRN-\d{4}-\d{4}$/', $mrn1);
        $this->assertMatchesRegularExpression('/^MRN-\d{4}-\d{4}$/', $mrn2);
    }

    public function test_pharmacy_dispensing_deducts_across_batches_using_fefo(): void
    {
        $pharmacist = User::factory()->create(['role' => 'pharmacist', 'is_active' => true]);

        $medicine = Medicine::create([
            'code' => 'MED-TEST-FEFO',
            'brand_name' => 'Ceftriaxone 1g IV',
            'generic_name' => 'Ceftriaxone',
            'category' => 'Antibiotics',
            'dosage_form' => 'Vial',
            'strength' => '1g',
            'unit_price' => 20.00,
            'stock_quantity' => 15,
            'min_stock_alert' => 5,
            'status' => 'in_stock',
        ]);

        // Batch A expires in 1 month (quantity: 5)
        $batchA = MedicineBatch::create([
            'medicine_id' => $medicine->id,
            'batch_number' => 'LOT-EARLY',
            'expiry_date' => Carbon::now()->addMonth(),
            'quantity' => 5,
            'purchase_cost' => 10.00,
            'status' => 'active',
        ]);

        // Batch B expires in 12 months (quantity: 10)
        $batchB = MedicineBatch::create([
            'medicine_id' => $medicine->id,
            'batch_number' => 'LOT-LATER',
            'expiry_date' => Carbon::now()->addMonths(12),
            'quantity' => 10,
            'purchase_cost' => 10.00,
            'status' => 'active',
        ]);

        $prescription = Prescription::create([
            'prescription_number' => 'RX-2026-TEST',
            'patient_id' => $this->patient->id,
            'doctor_id' => $this->doctor->id,
            'status' => 'pending',
        ]);

        PrescriptionItem::create([
            'prescription_id' => $prescription->id,
            'medicine_id' => $medicine->id,
            'dosage' => '1 vial IV daily',
            'quantity_prescribed' => 8, // Needs 5 from Batch A + 3 from Batch B
            'unit_price' => 20.00,
            'subtotal' => 160.00,
            'status' => 'pending',
        ]);

        $response = $this->actingAs($pharmacist)->post(route('pharmacy.prescriptions.dispense', $prescription));
        $response->assertRedirect();
        $response->assertSessionHas('success');

        // Verify FEFO deduction: Batch A depleted, Batch B reduced to 7
        $this->assertDatabaseHas('medicine_batches', [
            'id' => $batchA->id,
            'quantity' => 0,
            'status' => 'depleted',
        ]);

        $this->assertDatabaseHas('medicine_batches', [
            'id' => $batchB->id,
            'quantity' => 7,
            'status' => 'active',
        ]);

        $this->assertDatabaseHas('medicines', [
            'id' => $medicine->id,
            'stock_quantity' => 7,
        ]);
    }

    public function test_news2_score_is_calculated_and_stored_on_vitals_intake(): void
    {
        $nurse = User::factory()->create(['role' => 'nurse', 'is_active' => true]);

        // Deteriorating clinical parameters: RR 26 (3pts), SpO2 90 (3pts), HR 115 (2pts), Temp 39.5 (2pts) = 10 pts (High Risk)
        $response = $this->actingAs($nurse)->post(route('vitals.store'), [
            'patient_id' => $this->patient->id,
            'temperature' => 39.5,
            'blood_pressure_systolic' => 120,
            'blood_pressure_diastolic' => 80,
            'heart_rate' => 115,
            'respiratory_rate' => 26,
            'oxygen_saturation' => 90,
            'triage_level' => 'emergency',
            'weight' => 70,
            'height' => 175,
        ]);

        $response->assertRedirect();

        $vital = PatientVital::where('patient_id', $this->patient->id)->latest('recorded_at')->first();
        $this->assertNotNull($vital);
        $this->assertGreaterThanOrEqual(7, $vital->news2_score);
        $this->assertEquals('high', $vital->news2_risk_level);
    }

    public function test_viewing_patient_chart_generates_hipaa_compliant_view_audit_log(): void
    {
        $response = $this->actingAs($this->doctorUser)->get(route('patients.show', $this->patient));
        $response->assertStatus(200);

        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $this->doctorUser->id,
            'action' => 'VIEW',
            'module' => 'Patients',
            'record_id' => (string) $this->patient->id,
        ]);
    }

    public function test_medical_documents_can_be_attached_downloaded_and_archived(): void
    {
        Storage::fake('local');

        $file = UploadedFile::fake()->create('head_ct_scan.pdf', 300, 'application/pdf');

        $response = $this->actingAs($this->doctorUser)->post(route('patients.documents.upload', $this->patient), [
            'title' => 'Emergency Brain CT Scan',
            'document_type' => 'imaging_scan',
            'file' => $file,
            'notes' => 'No signs of acute hemorrhage',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('medical_documents', [
            'patient_id' => $this->patient->id,
            'title' => 'Emergency Brain CT Scan',
            'document_type' => 'imaging_scan',
            'file_name' => 'head_ct_scan.pdf',
        ]);

        $doc = MedicalDocument::where('patient_id', $this->patient->id)->first();
        Storage::disk('local')->assertExists($doc->file_path);

        // Download document
        $dlResponse = $this->actingAs($this->doctorUser)->get(route('patients.documents.download', [$this->patient, $doc]));
        $dlResponse->assertStatus(200);

        // Delete/Archive document
        $delResponse = $this->actingAs($this->doctorUser)->delete(route('patients.documents.destroy', [$this->patient, $doc]));
        $delResponse->assertRedirect();
        $this->assertSoftDeleted('medical_documents', ['id' => $doc->id]);
    }
}
