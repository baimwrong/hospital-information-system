<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class BulkImportTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->create([
            'role' => 'admin',
            'is_active' => true,
        ]);
    }

    public function test_template_download_generates_valid_csv(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.bulk-import.template', 'patients'));

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'text/csv; charset=UTF-8');
        $this->assertStringContainsString('first_name', $response->getContent());
        $this->assertStringContainsString('blood_group', $response->getContent());
    }

    public function test_admin_can_bulk_import_patients_from_csv(): void
    {
        $csvContent = "mrn,first_name,last_name,email,phone,date_of_birth,gender,blood_group\n".
                      "MRN-TEST-001,Lucas,Scott,lucas@example.com,+1-555-1111,1992-06-10,male,O+\n".
                      "MRN-TEST-002,Nathan,Scott,nathan@example.com,+1-555-2222,1994-08-20,male,A+\n";

        $file = UploadedFile::fake()->createWithContent('patients_batch.csv', $csvContent);

        $response = $this->actingAs($this->admin)->post(route('admin.bulk-import.import'), [
            'entity_type' => 'patients',
            'file' => $file,
        ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('patients', [
            'mrn' => 'MRN-TEST-001',
            'first_name' => 'Lucas',
            'last_name' => 'Scott',
        ]);

        $this->assertDatabaseHas('patients', [
            'mrn' => 'MRN-TEST-002',
            'first_name' => 'Nathan',
            'last_name' => 'Scott',
        ]);

        $this->assertDatabaseHas('bulk_imports', [
            'entity_type' => 'patients',
            'status' => 'completed',
            'successful_rows' => 2,
            'failed_rows' => 0,
        ]);
    }

    public function test_bulk_import_flags_invalid_rows_and_creates_audit_log(): void
    {
        // 1 valid row, 1 row missing required first_name
        $csvContent = "mrn,first_name,last_name,phone,date_of_birth,gender\n".
                      "MRN-TEST-003,Brooke,Davis,+1-555-3333,1993-01-01,female\n".
                      "MRN-TEST-004,,MissingName,+1-555-4444,1995-02-02,female\n";

        $file = UploadedFile::fake()->createWithContent('patients_invalid.csv', $csvContent);

        $response = $this->actingAs($this->admin)->post(route('admin.bulk-import.import'), [
            'entity_type' => 'patients',
            'file' => $file,
        ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('patients', ['mrn' => 'MRN-TEST-003']);
        $this->assertDatabaseMissing('patients', ['mrn' => 'MRN-TEST-004']);

        $this->assertDatabaseHas('bulk_imports', [
            'entity_type' => 'patients',
            'status' => 'completed_with_errors',
            'successful_rows' => 1,
            'failed_rows' => 1,
        ]);
    }
}
