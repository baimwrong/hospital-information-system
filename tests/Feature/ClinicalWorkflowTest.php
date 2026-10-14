<?php

namespace Tests\Feature;

use App\Models\Department;
use App\Models\Doctor;
use App\Models\Invoice;
use App\Models\LabTestType;
use App\Models\Medicine;
use App\Models\Patient;
use App\Models\Prescription;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ClinicalWorkflowTest extends TestCase
{
    use RefreshDatabase;

    protected User $doctorUser;

    protected Doctor $doctor;

    protected Department $department;

    protected Patient $patient;

    protected Medicine $medicine;

    protected LabTestType $testType;

    protected function setUp(): void
    {
        parent::setUp();

        $this->doctorUser = User::factory()->create([
            'role' => 'doctor',
            'is_active' => true,
        ]);

        $this->department = Department::create([
            'code' => 'CARD',
            'name' => 'Cardiology',
            'is_active' => true,
        ]);

        $this->doctor = Doctor::create([
            'user_id' => $this->doctorUser->id,
            'department_id' => $this->department->id,
            'specialization' => 'Cardiologist',
            'license_number' => 'MD-TEST-900',
            'consultation_fee' => 75.00,
            'status' => 'active',
        ]);

        $this->patient = Patient::create([
            'mrn' => 'MRN-2026-0099',
            'first_name' => 'Michael',
            'last_name' => 'Corleone',
            'phone' => '+1-555-8888',
            'date_of_birth' => '1980-04-08',
            'gender' => 'male',
            'status' => 'active',
        ]);

        $this->medicine = Medicine::create([
            'code' => 'MED-TEST-01',
            'brand_name' => 'Amoxicillin 500mg',
            'generic_name' => 'Amoxicillin',
            'category' => 'Antibiotics',
            'dosage_form' => 'Capsule',
            'strength' => '500mg',
            'unit_price' => 12.00,
            'stock_quantity' => 50,
            'min_stock_alert' => 10,
            'status' => 'in_stock',
        ]);

        $this->testType = LabTestType::create([
            'test_code' => 'CBC-TEST',
            'name' => 'Complete Blood Count',
            'category' => 'Hematology',
            'price' => 25.00,
            'is_active' => true,
        ]);
    }

    public function test_nurse_can_record_vital_signs_and_assign_triage(): void
    {
        $nurse = User::factory()->create(['role' => 'nurse', 'is_active' => true]);

        $response = $this->actingAs($nurse)->post(route('vitals.store'), [
            'patient_id' => $this->patient->id,
            'temperature' => 37.2,
            'blood_pressure_systolic' => 130,
            'blood_pressure_diastolic' => 85,
            'heart_rate' => 74,
            'triage_level' => 'urgent',
            'weight' => 75,
            'height' => 178,
            'clinical_notes' => 'Patient in moderate distress.',
        ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('patient_vitals', [
            'patient_id' => $this->patient->id,
            'blood_pressure_systolic' => 130,
            'triage_level' => 'urgent',
        ]);
    }

    public function test_doctor_can_document_clinical_encounter_with_prescription_and_lab(): void
    {
        $response = $this->actingAs($this->doctorUser)->post(route('encounters.store'), [
            'patient_id' => $this->patient->id,
            'doctor_id' => $this->doctor->id,
            'chief_complaint' => 'Persistent productive cough and fever.',
            'diagnosis_primary' => 'Acute Bronchitis',
            'diagnosis_icd10_code' => 'J20.9',
            'treatment_plan' => 'Start course of Amoxicillin. Rest and oral hydration.',
            'medicines' => [
                [
                    'id' => $this->medicine->id,
                    'dosage' => '500mg tid',
                    'quantity' => 15,
                    'instructions' => 'Take with full glass of water',
                ],
            ],
            'lab_test_type_ids' => [$this->testType->id],
        ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('medical_records', [
            'patient_id' => $this->patient->id,
            'diagnosis_primary' => 'Acute Bronchitis',
            'diagnosis_icd10_code' => 'J20.9',
        ]);

        $this->assertDatabaseHas('prescriptions', [
            'patient_id' => $this->patient->id,
            'status' => 'pending',
        ]);

        $this->assertDatabaseHas('lab_requests', [
            'patient_id' => $this->patient->id,
            'status' => 'pending',
        ]);
    }

    public function test_pharmacist_can_dispense_prescription_and_inventory_is_decremented(): void
    {
        $pharmacist = User::factory()->create(['role' => 'pharmacist', 'is_active' => true]);

        $prescription = Prescription::create([
            'prescription_number' => 'RX-TEST-0001',
            'patient_id' => $this->patient->id,
            'doctor_id' => $this->doctor->id,
            'status' => 'pending',
        ]);

        $prescription->items()->create([
            'medicine_id' => $this->medicine->id,
            'dosage' => '500mg tid',
            'quantity_prescribed' => 10,
            'quantity_dispensed' => 0,
            'unit_price' => 12.00,
            'subtotal' => 120.00,
            'status' => 'pending',
        ]);

        $this->assertEquals(50, $this->medicine->stock_quantity);

        $response = $this->actingAs($pharmacist)->post(route('pharmacy.prescriptions.dispense', $prescription));

        $response->assertRedirect();

        $prescription->refresh();
        $this->assertEquals('dispensed', $prescription->status);

        $this->medicine->refresh();
        $this->assertEquals(40, $this->medicine->stock_quantity);

        // Verify billing invoice was generated
        $this->assertDatabaseHas('invoices', [
            'patient_id' => $this->patient->id,
            'total_amount' => 120.00,
        ]);
    }

    public function test_cashier_can_record_payment_against_invoice(): void
    {
        $accountant = User::factory()->create(['role' => 'accountant', 'is_active' => true]);

        $invoice = Invoice::create([
            'invoice_number' => 'INV-TEST-0001',
            'patient_id' => $this->patient->id,
            'total_amount' => 100.00,
            'paid_amount' => 0.00,
            'balance_due' => 100.00,
            'payment_status' => 'unpaid',
            'invoice_date' => now()->toDateString(),
            'generated_by_id' => $accountant->id,
        ]);

        $response = $this->actingAs($accountant)->post(route('billing.payment', $invoice), [
            'amount' => 100.00,
            'payment_method' => 'cash',
            'notes' => 'Settled in full at front desk cashier.',
        ]);

        $response->assertRedirect();

        $invoice->refresh();
        $this->assertEquals(100.00, $invoice->paid_amount);
        $this->assertEquals(0.00, $invoice->balance_due);
        $this->assertEquals('paid', $invoice->payment_status);
    }
}
