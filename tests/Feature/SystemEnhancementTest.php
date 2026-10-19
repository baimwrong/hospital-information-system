<?php

namespace Tests\Feature;

use App\Models\Admission;
use App\Models\Appointment;
use App\Models\Bed;
use App\Models\Department;
use App\Models\Doctor;
use App\Models\Invoice;
use App\Models\Medicine;
use App\Models\Patient;
use App\Models\Payment;
use App\Models\Prescription;
use App\Models\User;
use App\Models\Ward;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\RateLimiter;
use Tests\TestCase;

class SystemEnhancementTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected User $doctorUser;

    protected Doctor $doctor;

    protected Department $department;

    protected Patient $patient;

    protected Medicine $medicine;

    protected function setUp(): void
    {
        parent::setUp();

        RateLimiter::clear('login:test@hospital.local|127.0.0.1');

        $this->admin = User::factory()->create([
            'role' => 'admin',
            'is_active' => true,
        ]);

        $this->doctorUser = User::factory()->create([
            'role' => 'doctor',
            'is_active' => true,
        ]);

        $this->department = Department::create([
            'code' => 'GENM',
            'name' => 'General Medicine',
            'is_active' => true,
        ]);

        $this->doctor = Doctor::create([
            'user_id' => $this->doctorUser->id,
            'department_id' => $this->department->id,
            'specialization' => 'Internist',
            'license_number' => 'MD-TEST-777',
            'consultation_fee' => 50.00,
            'available_days' => ['Monday', 'Wednesday', 'Friday'],
            'status' => 'active',
        ]);

        $this->patient = Patient::create([
            'mrn' => 'MRN-2026-7777',
            'first_name' => 'Alice',
            'last_name' => 'Wonderland',
            'phone' => '+1-555-7777',
            'date_of_birth' => '1995-03-15',
            'gender' => 'female',
            'allergies' => ['Penicillin', 'Ciprofloxacin'],
            'status' => 'active',
        ]);

        $this->medicine = Medicine::create([
            'code' => 'MED-TEST-PEN',
            'brand_name' => 'Amoxicillin Trihydrate',
            'generic_name' => 'Penicillin derivative',
            'category' => 'Antibiotics',
            'dosage_form' => 'Capsule',
            'strength' => '250mg',
            'unit_price' => 15.00,
            'stock_quantity' => 100,
            'min_stock_alert' => 10,
            'status' => 'in_stock',
        ]);
    }

    public function test_payment_ledger_records_immutable_payment_receipt(): void
    {
        $accountant = User::factory()->create(['role' => 'accountant', 'is_active' => true]);

        $invoice = Invoice::create([
            'invoice_number' => Invoice::generateNumber(),
            'patient_id' => $this->patient->id,
            'total_amount' => 200.00,
            'paid_amount' => 0.00,
            'balance_due' => 200.00,
            'payment_status' => 'unpaid',
            'invoice_date' => now()->toDateString(),
            'generated_by_id' => $accountant->id,
        ]);

        // First partial payment
        $response1 = $this->actingAs($accountant)->post(route('billing.payment', $invoice), [
            'amount' => 80.00,
            'payment_method' => 'cash',
            'transaction_reference' => 'TXN-CASH-001',
            'notes' => 'First installment',
        ]);

        $response1->assertRedirect();
        $invoice->refresh();

        $this->assertEquals(80.00, $invoice->paid_amount);
        $this->assertEquals(120.00, $invoice->balance_due);
        $this->assertEquals('partial', $invoice->payment_status);

        $this->assertDatabaseHas('payments', [
            'invoice_id' => $invoice->id,
            'patient_id' => $this->patient->id,
            'amount' => 80.00,
            'payment_method' => 'cash',
            'transaction_reference' => 'TXN-CASH-001',
        ]);

        // Second payment clearing invoice
        $response2 = $this->actingAs($accountant)->post(route('billing.payment', $invoice), [
            'amount' => 120.00,
            'payment_method' => 'credit_card',
            'transaction_reference' => 'POS-CARD-999',
            'notes' => 'Settled in full',
        ]);

        $response2->assertRedirect();
        $invoice->refresh();

        $this->assertEquals(200.00, $invoice->paid_amount);
        $this->assertEquals(0.00, $invoice->balance_due);
        $this->assertEquals('paid', $invoice->payment_status);

        $this->assertCount(2, $invoice->payments);
    }

    public function test_allergy_contraindication_prevents_prescription_dispensing(): void
    {
        $pharmacist = User::factory()->create(['role' => 'pharmacist', 'is_active' => true]);

        $prescription = Prescription::create([
            'prescription_number' => Prescription::generateNumber(),
            'patient_id' => $this->patient->id,
            'doctor_id' => $this->doctor->id,
            'status' => 'pending',
        ]);

        $prescription->items()->create([
            'medicine_id' => $this->medicine->id,
            'dosage' => '250mg tid',
            'quantity_prescribed' => 10,
            'quantity_dispensed' => 0,
            'unit_price' => 15.00,
            'subtotal' => 150.00,
            'status' => 'pending',
        ]);

        $response = $this->actingAs($pharmacist)->post(route('pharmacy.prescriptions.dispense', $prescription));

        $response->assertSessionHasErrors(['error']);
        $prescription->refresh();
        $this->assertEquals('pending', $prescription->status);
        $this->assertEquals(100, $this->medicine->fresh()->stock_quantity);
    }

    public function test_appointment_prevents_booking_outside_physician_available_days_and_double_booking(): void
    {
        $receptionist = User::factory()->create(['role' => 'receptionist', 'is_active' => true]);

        // Find a Tuesday (doctor works Monday, Wednesday, Friday)
        $tuesday = Carbon::now()->next(Carbon::TUESDAY)->toDateString();

        $responseTuesday = $this->actingAs($receptionist)->post(route('appointments.store'), [
            'patient_id' => $this->patient->id,
            'doctor_id' => $this->doctor->id,
            'appointment_date' => $tuesday,
            'appointment_time' => '10:00',
            'priority' => 'routine',
            'reason_for_visit' => 'Regular checkup',
        ]);

        $responseTuesday->assertSessionHasErrors(['appointment_date']);

        // Find a Wednesday (doctor is available)
        $wednesday = Carbon::now()->next(Carbon::WEDNESDAY)->toDateString();

        $responseWed1 = $this->actingAs($receptionist)->post(route('appointments.store'), [
            'patient_id' => $this->patient->id,
            'doctor_id' => $this->doctor->id,
            'appointment_date' => $wednesday,
            'appointment_time' => '10:00',
            'priority' => 'routine',
            'reason_for_visit' => 'Routine cardiology consultation',
        ]);

        $responseWed1->assertRedirect();
        $createdAppointment = Appointment::where('doctor_id', $this->doctor->id)->first();
        $this->assertNotNull($createdAppointment);
        $this->assertEquals($wednesday, $createdAppointment->appointment_date->toDateString());
        $this->assertEquals('10:00', $createdAppointment->appointment_time);

        // Attempt double-booking on same doctor, date and time
        $patient2 = Patient::create([
            'mrn' => 'MRN-2026-8888',
            'first_name' => 'Bob',
            'last_name' => 'Dylan',
            'phone' => '+1-555-8888',
            'date_of_birth' => '1960-05-24',
            'gender' => 'male',
            'status' => 'active',
        ]);

        $responseWed2 = $this->actingAs($receptionist)->post(route('appointments.store'), [
            'patient_id' => $patient2->id,
            'doctor_id' => $this->doctor->id,
            'appointment_date' => $wednesday,
            'appointment_time' => '10:00',
            'priority' => 'routine',
            'reason_for_visit' => 'Second patient trying same time slot',
        ]);

        $responseWed2->assertSessionHasErrors(['appointment_time']);
    }

    public function test_inpatient_admission_requires_available_bed_and_prevents_double_discharge(): void
    {
        $nurse = User::factory()->create(['role' => 'nurse', 'is_active' => true]);

        $ward = Ward::create([
            'name' => 'Ward A',
            'type' => 'general',
            'capacity' => 2,
            'gender_preference' => 'mixed',
            'daily_rate' => 100.00,
        ]);

        $bed = Bed::create([
            'ward_id' => $ward->id,
            'bed_number' => 'W-A-01',
            'bed_type' => 'standard',
            'status' => 'available',
            'daily_rate' => 100.00,
        ]);

        // Admit patient
        $responseAdmit = $this->actingAs($nurse)->post(route('inpatient.admissions.store'), [
            'patient_id' => $this->patient->id,
            'doctor_id' => $this->doctor->id,
            'bed_id' => $bed->id,
            'admission_reason' => 'Acute pneumonia observation',
        ]);

        $responseAdmit->assertRedirect();
        $bed->refresh();
        $this->assertEquals('occupied', $bed->status);

        $admission = Admission::where('patient_id', $this->patient->id)->where('status', 'admitted')->first();
        $this->assertNotNull($admission);

        // Attempting to admit another patient to the same occupied bed fails
        $patient2 = Patient::create([
            'mrn' => 'MRN-2026-9991',
            'first_name' => 'Charlie',
            'last_name' => 'Brown',
            'phone' => '+1-555-9991',
            'date_of_birth' => '1988-08-08',
            'gender' => 'male',
            'status' => 'active',
        ]);

        $responseAdmit2 = $this->actingAs($nurse)->post(route('inpatient.admissions.store'), [
            'patient_id' => $patient2->id,
            'doctor_id' => $this->doctor->id,
            'bed_id' => $bed->id,
            'admission_reason' => 'Should fail due to occupied bed',
        ]);

        $responseAdmit2->assertSessionHasErrors(['bed_id']);

        // Discharge patient
        $responseDischarge = $this->actingAs($nurse)->post(route('inpatient.discharge', $admission), [
            'discharge_summary' => 'Patient stabilized and fully recovered.',
            'condition_at_discharge' => 'recovered',
        ]);

        $responseDischarge->assertRedirect();
        $admission->refresh();
        $this->assertEquals('discharged', $admission->status);

        // Second discharge attempt is guarded against idempotently
        $responseDischarge2 = $this->actingAs($nurse)->post(route('inpatient.discharge', $admission), [
            'discharge_summary' => 'Duplicate attempt.',
            'condition_at_discharge' => 'recovered',
        ]);

        $responseDischarge2->assertSessionHas('warning');
    }

    public function test_patient_soft_delete_archives_and_preserves_records(): void
    {
        $patient = Patient::create([
            'mrn' => 'MRN-2026-SOFT',
            'first_name' => 'David',
            'last_name' => 'Copperfield',
            'phone' => '+1-555-4321',
            'date_of_birth' => '1980-01-01',
            'gender' => 'male',
            'status' => 'active',
        ]);

        $response = $this->actingAs($this->admin)->delete(route('patients.destroy', $patient));

        $response->assertRedirect();
        $this->assertSoftDeleted('patients', ['id' => $patient->id]);

        $archivedPatient = Patient::withTrashed()->find($patient->id);
        $this->assertEquals('archived', $archivedPatient->status);
        $this->assertNotNull($archivedPatient->deleted_at);
    }
}
