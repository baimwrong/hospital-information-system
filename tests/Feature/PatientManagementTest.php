<?php

namespace Tests\Feature;

use App\Models\Patient;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PatientManagementTest extends TestCase
{
    use RefreshDatabase;

    protected User $staff;

    protected function setUp(): void
    {
        parent::setUp();
        $this->staff = User::factory()->create([
            'role' => 'receptionist',
            'is_active' => true,
        ]);
    }

    public function test_patients_index_page_can_be_rendered(): void
    {
        $patient = Patient::create([
            'mrn' => 'MRN-2026-0001',
            'first_name' => 'John',
            'last_name' => 'Doe',
            'phone' => '+1-555-0101',
            'date_of_birth' => '1990-01-01',
            'gender' => 'male',
            'status' => 'active',
        ]);

        $response = $this->actingAs($this->staff)->get(route('patients.index'));

        $response->assertStatus(200);
        $response->assertSee('John Doe');
        $response->assertSee('MRN-2026-0001');
    }

    public function test_patient_can_be_created_with_allergies_and_demographics(): void
    {
        $response = $this->actingAs($this->staff)->post(route('patients.store'), [
            'mrn' => 'MRN-2026-9999',
            'first_name' => 'Jane',
            'last_name' => 'Smith',
            'phone' => '+1-555-9090',
            'date_of_birth' => '1985-05-15',
            'gender' => 'female',
            'blood_group' => 'O+',
            'allergies' => 'Penicillin, Aspirin',
            'chronic_diseases' => 'Hypertension',
            'insurance_provider' => 'CareHealth',
            'insurance_policy_number' => 'CH-12345',
        ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('patients', [
            'mrn' => 'MRN-2026-9999',
            'first_name' => 'Jane',
            'last_name' => 'Smith',
            'blood_group' => 'O+',
        ]);

        $patient = Patient::where('mrn', 'MRN-2026-9999')->first();
        $this->assertContains('Penicillin', $patient->allergies);
        $this->assertContains('Aspirin', $patient->allergies);
    }

    public function test_patient_ehr_profile_view_renders_successfully(): void
    {
        $patient = Patient::create([
            'mrn' => 'MRN-2026-0002',
            'first_name' => 'Arthur',
            'last_name' => 'Morgan',
            'phone' => '+1-555-0102',
            'date_of_birth' => '1975-02-14',
            'gender' => 'male',
            'blood_group' => 'A+',
            'status' => 'active',
        ]);

        $response = $this->actingAs($this->staff)->get(route('patients.show', $patient));

        $response->assertStatus(200);
        $response->assertSee('Arthur Morgan');
        $response->assertSee('Electronic Health Record (EHR)');
    }
}
