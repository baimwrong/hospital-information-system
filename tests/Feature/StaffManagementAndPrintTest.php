<?php

namespace Tests\Feature;

use App\Models\Department;
use App\Models\Doctor;
use App\Models\LabRequest;
use App\Models\LabTestResult;
use App\Models\LabTestType;
use App\Models\Medicine;
use App\Models\Patient;
use App\Models\Prescription;
use App\Models\PrescriptionItem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class StaffManagementAndPrintTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected User $doctorUser;

    protected Doctor $doctor;

    protected Patient $patient;

    protected Department $department;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create([
            'name' => 'Dr. Administrator',
            'email' => 'admin@hospital.test',
            'role' => 'admin',
            'is_active' => true,
        ]);

        $this->doctorUser = User::factory()->create([
            'name' => 'Dr. Robert Smith',
            'email' => 'doctor@hospital.test',
            'role' => 'doctor',
            'is_active' => true,
        ]);

        $this->department = Department::create([
            'code' => 'CARDIO',
            'name' => 'Cardiology',
            'is_active' => true,
        ]);

        $this->doctor = Doctor::create([
            'user_id' => $this->doctorUser->id,
            'department_id' => $this->department->id,
            'specialization' => 'Cardiologist',
            'license_number' => 'DOC-CARD-001',
            'consultation_fee' => 120.00,
            'status' => 'active',
        ]);

        $this->patient = Patient::create([
            'mrn' => 'MRN-2026-9001',
            'first_name' => 'Alice',
            'last_name' => 'Wonderland',
            'phone' => '+1-555-4321',
            'date_of_birth' => '1990-05-15',
            'gender' => 'female',
            'status' => 'active',
        ]);
    }

    public function test_admin_can_view_staff_list_and_create_staff_member(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.users.index'));
        $response->assertStatus(200);
        $response->assertSee('Staff &amp; User Accounts', false);
        $response->assertSee('Dr. Robert Smith');

        $createPageResponse = $this->actingAs($this->admin)->get(route('admin.users.create'));
        $createPageResponse->assertStatus(200);

        $payload = [
            'name' => 'Nurse Clara Barton',
            'email' => 'clara.barton@hospital.test',
            'role' => 'nurse',
            'department_id' => $this->department->id,
            'phone' => '+1-555-7788',
            'password' => 'SecurePass123!',
            'password_confirmation' => 'SecurePass123!',
            'must_change_password' => '1',
        ];

        $postResponse = $this->actingAs($this->admin)->post(route('admin.users.store'), $payload);
        $postResponse->assertRedirect(route('admin.users.index'));
        $postResponse->assertSessionHas('success');

        $this->assertDatabaseHas('users', [
            'email' => 'clara.barton@hospital.test',
            'role' => 'nurse',
            'is_active' => 1,
            'must_change_password' => 1,
        ]);
    }

    public function test_non_admin_cannot_access_staff_management(): void
    {
        $response = $this->actingAs($this->doctorUser)->get(route('admin.users.index'));
        $response->assertStatus(403);

        $postResponse = $this->actingAs($this->doctorUser)->post(route('admin.users.store'), [
            'name' => 'Unauthorized User',
            'email' => 'unauth@hospital.test',
            'role' => 'admin',
        ]);
        $postResponse->assertStatus(403);
    }

    public function test_admin_can_toggle_staff_active_status_and_cannot_deactivate_self(): void
    {
        // Toggle doctor status to inactive
        $toggleResponse = $this->actingAs($this->admin)
            ->post(route('admin.users.toggle-status', $this->doctorUser));

        $toggleResponse->assertRedirect(route('admin.users.index'));
        $this->assertDatabaseHas('users', [
            'id' => $this->doctorUser->id,
            'is_active' => 0,
        ]);

        // Attempt to deactivate self
        $selfToggleResponse = $this->actingAs($this->admin)
            ->post(route('admin.users.toggle-status', $this->admin));

        $selfToggleResponse->assertRedirect(route('admin.users.index'));
        $selfToggleResponse->assertSessionHasErrors(['error']);
        $this->assertDatabaseHas('users', [
            'id' => $this->admin->id,
            'is_active' => 1,
        ]);
    }

    public function test_admin_can_reset_staff_temporary_password(): void
    {
        $resetResponse = $this->actingAs($this->admin)
            ->post(route('admin.users.reset-password', $this->doctorUser), [
                'new_password' => 'TempPass2026!',
            ]);

        $resetResponse->assertRedirect(route('admin.users.index'));
        $resetResponse->assertSessionHas('success');

        $this->doctorUser->refresh();
        $this->assertTrue(Hash::check('TempPass2026!', $this->doctorUser->password));
        $this->assertTrue((bool) $this->doctorUser->must_change_password);
    }

    public function test_user_can_view_profile_and_update_contact_information(): void
    {
        $response = $this->actingAs($this->doctorUser)->get(route('profile'));
        $response->assertStatus(200);
        $response->assertSee('Staff Profile &amp; Security', false);

        $updateResponse = $this->actingAs($this->doctorUser)->put(route('profile.update'), [
            'name' => 'Dr. Robert Smith Jr.',
            'phone' => '+1-555-9999',
        ]);

        $updateResponse->assertRedirect(route('profile'));
        $updateResponse->assertSessionHas('success');

        $this->assertDatabaseHas('users', [
            'id' => $this->doctorUser->id,
            'name' => 'Dr. Robert Smith Jr.',
            'phone' => '+1-555-9999',
        ]);
    }

    public function test_user_can_change_password_and_clears_must_change_flag(): void
    {
        $this->doctorUser->update([
            'password' => Hash::make('OldPassword123!'),
            'must_change_password' => true,
        ]);

        $changePassResponse = $this->actingAs($this->doctorUser)->put(route('profile.password'), [
            'current_password' => 'OldPassword123!',
            'password' => 'NewStrongPass2026!',
            'password_confirmation' => 'NewStrongPass2026!',
        ]);

        $changePassResponse->assertRedirect(route('profile'));
        $changePassResponse->assertSessionHas('success');

        $this->doctorUser->refresh();
        $this->assertTrue(Hash::check('NewStrongPass2026!', $this->doctorUser->password));
        $this->assertFalse((bool) $this->doctorUser->must_change_password);
    }

    public function test_must_change_password_redirects_to_profile_on_login(): void
    {
        $staffUser = User::factory()->create([
            'email' => 'newstaff@hospital.test',
            'password' => Hash::make('TemporaryPass123!'),
            'is_active' => true,
            'must_change_password' => true,
        ]);

        $loginResponse = $this->post(route('login'), [
            'email' => 'newstaff@hospital.test',
            'password' => 'TemporaryPass123!',
        ]);

        $loginResponse->assertRedirect(route('profile'));
        $loginResponse->assertSessionHas('warning');
    }

    public function test_pharmacy_and_laboratory_print_views_render_successfully(): void
    {
        $medicine = Medicine::create([
            'code' => 'MED-PRN-001',
            'brand_name' => 'Lopressor',
            'generic_name' => 'Metoprolol Tartrate',
            'category' => 'Cardiovascular',
            'dosage_form' => 'tablet',
            'strength' => '50mg',
            'unit_price' => 1.50,
            'purchase_cost' => 0.80,
            'stock_quantity' => 100,
            'min_stock_alert' => 20,
            'status' => 'active',
        ]);

        $prescription = Prescription::create([
            'prescription_number' => 'RX-PRN-2026-0001',
            'patient_id' => $this->patient->id,
            'doctor_id' => $this->doctor->id,
            'status' => 'pending',
        ]);

        PrescriptionItem::create([
            'prescription_id' => $prescription->id,
            'medicine_id' => $medicine->id,
            'quantity_prescribed' => 30,
            'quantity_dispensed' => 0,
            'dosage' => '50mg',
            'frequency' => 'Once daily with meals',
            'duration_days' => 30,
            'instructions' => 'Take in the morning',
            'unit_price' => 1.50,
            'subtotal' => 45.00,
            'status' => 'pending',
        ]);

        $rxPrintResponse = $this->actingAs($this->doctorUser)
            ->get(route('pharmacy.prescriptions.print', $prescription));

        $rxPrintResponse->assertStatus(200);
        $rxPrintResponse->assertSee('Official Medical Prescription (Rx)');
        $rxPrintResponse->assertSee('RX-PRN-2026-0001');
        $rxPrintResponse->assertSee('Lopressor');
        $rxPrintResponse->assertSee('Alice Wonderland');

        // Lab Request Print View
        $labTest = LabTestType::create([
            'test_code' => 'LIPID-PRN',
            'name' => 'Lipid Profile',
            'category' => 'Biochemistry',
            'price' => 45.00,
            'sample_type' => 'Serum',
            'reference_range' => '< 200 mg/dL',
            'turnaround_time_hours' => 4,
            'is_active' => true,
        ]);

        $labRequest = LabRequest::create([
            'request_number' => 'LAB-PRN-2026-0001',
            'patient_id' => $this->patient->id,
            'doctor_id' => $this->doctor->id,
            'priority' => 'routine',
            'status' => 'completed',
            'clinical_notes' => 'Routine hyperlipidemia follow up',
            'completed_at' => now(),
        ]);

        LabTestResult::create([
            'lab_request_id' => $labRequest->id,
            'lab_test_type_id' => $labTest->id,
            'parameter_name' => 'Total Cholesterol',
            'result_value' => '210',
            'unit' => 'mg/dL',
            'reference_range' => '< 200 mg/dL',
            'flag' => 'high',
        ]);

        $labPrintResponse = $this->actingAs($this->doctorUser)
            ->get(route('laboratory.requests.print', $labRequest));

        $labPrintResponse->assertStatus(200);
        $labPrintResponse->assertSee('Department of Pathology');
        $labPrintResponse->assertSee('Clinical Diagnostics');
        $labPrintResponse->assertSee('LAB-PRN-2026-0001');
        $labPrintResponse->assertSee('Total Cholesterol');
        $labPrintResponse->assertSee('210');
        $labPrintResponse->assertSee('high');
    }
}
