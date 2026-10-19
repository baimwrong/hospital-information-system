<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\BulkImport;
use App\Models\Department;
use App\Models\Doctor;
use App\Models\LabTestType;
use App\Models\Medicine;
use App\Models\Patient;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class BulkImportService
{
    /**
     * Process an uploaded CSV file for the given entity type.
     *
     * @return array{success: bool, import: BulkImport, message: string}
     */
    public function import(UploadedFile $file, string $entityType, int $userId): array
    {
        $originalFilename = $file->getClientOriginalName();
        $path = $file->getRealPath();

        $rows = $this->parseCsv($path);

        if (empty($rows)) {
            $import = BulkImport::create([
                'user_id' => $userId,
                'entity_type' => $entityType,
                'original_filename' => $originalFilename,
                'total_rows' => 0,
                'successful_rows' => 0,
                'failed_rows' => 0,
                'status' => 'failed',
                'error_summary' => [['row' => 0, 'error' => 'The uploaded file is empty or contains only headers.']],
            ]);

            return [
                'success' => false,
                'import' => $import,
                'message' => 'The uploaded CSV file is empty.',
            ];
        }

        $totalRows = count($rows);
        $successful = 0;
        $failed = 0;
        $errors = [];

        DB::beginTransaction();

        try {
            foreach ($rows as $index => $row) {
                $rowNum = $index + 2; // Row 1 is header
                try {
                    DB::transaction(function () use ($entityType, $row, $rowNum) {
                        switch ($entityType) {
                            case 'patients':
                                $this->importPatient($row, $rowNum);
                                break;
                            case 'doctors':
                                $this->importDoctor($row, $rowNum);
                                break;
                            case 'medicines':
                                $this->importMedicine($row, $rowNum);
                                break;
                            case 'lab_test_types':
                                $this->importLabTestType($row, $rowNum);
                                break;
                            case 'departments':
                                $this->importDepartment($row, $rowNum);
                                break;
                            default:
                                throw new \InvalidArgumentException("Unsupported entity type: {$entityType}");
                        }
                    });
                    $successful++;
                } catch (\Throwable $e) {
                    $failed++;
                    $errors[] = [
                        'row' => $rowNum,
                        'data' => array_slice($row, 0, 4),
                        'error' => $e->getMessage(),
                    ];
                }
            }

            $status = $failed === 0 ? 'completed' : ($successful > 0 ? 'completed_with_errors' : 'failed');

            $import = BulkImport::create([
                'user_id' => $userId,
                'entity_type' => $entityType,
                'original_filename' => $originalFilename,
                'total_rows' => $totalRows,
                'successful_rows' => $successful,
                'failed_rows' => $failed,
                'status' => $status,
                'error_summary' => $errors,
            ]);

            DB::commit();

            AuditLog::log(
                'BULK_IMPORT',
                'Admin',
                "Bulk imported {$successful}/{$totalRows} {$entityType} from {$originalFilename}",
                (string) $import->id
            );

            return [
                'success' => $successful > 0,
                'import' => $import,
                'message' => "Import processed: {$successful} imported successfully, {$failed} errors.",
            ];
        } catch (\Throwable $e) {
            DB::rollBack();

            $import = BulkImport::create([
                'user_id' => $userId,
                'entity_type' => $entityType,
                'original_filename' => $originalFilename,
                'total_rows' => $totalRows,
                'successful_rows' => 0,
                'failed_rows' => $totalRows,
                'status' => 'failed',
                'error_summary' => [['row' => 'All', 'error' => 'Fatal import transaction failure: '.$e->getMessage()]],
            ]);

            return [
                'success' => false,
                'import' => $import,
                'message' => 'Fatal error during import: '.$e->getMessage(),
            ];
        }
    }

    /**
     * Import a single patient record.
     */
    protected function importPatient(array $data, int $rowNum): void
    {
        $validator = Validator::make($data, [
            'first_name' => 'required|string|max:100',
            'last_name' => 'required|string|max:100',
            'phone' => 'required|string|max:30',
            'date_of_birth' => 'required|date',
            'gender' => 'required|in:male,female,other,Male,Female,Other',
            'email' => 'nullable|email|max:150',
            'blood_group' => 'nullable|string|max:10',
            'mrn' => 'nullable|string|unique:patients,mrn',
        ]);

        if ($validator->fails()) {
            throw new \Exception(implode(', ', $validator->errors()->all()));
        }

        $mrn = ! empty($data['mrn']) ? trim($data['mrn']) : Patient::generateMRN();

        $allergies = ! empty($data['allergies'])
            ? array_map('trim', explode(';', $data['allergies']))
            : [];

        $chronicDiseases = ! empty($data['chronic_diseases'])
            ? array_map('trim', explode(';', $data['chronic_diseases']))
            : [];

        Patient::create([
            'mrn' => $mrn,
            'first_name' => trim($data['first_name']),
            'last_name' => trim($data['last_name']),
            'email' => ! empty($data['email']) ? trim($data['email']) : null,
            'phone' => trim($data['phone']),
            'date_of_birth' => Carbon::parse($data['date_of_birth'])->format('Y-m-d'),
            'gender' => strtolower(trim($data['gender'])),
            'blood_group' => ! empty($data['blood_group']) ? trim($data['blood_group']) : 'Unknown',
            'genotype' => $data['genotype'] ?? null,
            'national_id' => $data['national_id'] ?? null,
            'address' => $data['address'] ?? null,
            'emergency_contact_name' => $data['emergency_contact_name'] ?? null,
            'emergency_contact_phone' => $data['emergency_contact_phone'] ?? null,
            'emergency_contact_relation' => $data['emergency_contact_relation'] ?? null,
            'allergies' => $allergies,
            'chronic_diseases' => $chronicDiseases,
            'insurance_provider' => $data['insurance_provider'] ?? null,
            'insurance_policy_number' => $data['insurance_policy_number'] ?? null,
            'status' => 'active',
        ]);
    }

    /**
     * Import a single doctor / medical specialist record.
     */
    protected function importDoctor(array $data, int $rowNum): void
    {
        $validator = Validator::make($data, [
            'name' => 'required|string|max:150',
            'email' => 'required|email|unique:users,email',
            'phone' => 'nullable|string|max:30',
            'specialization' => 'required|string|max:150',
            'license_number' => 'required|string|max:100|unique:doctors,license_number',
            'department_code' => 'required|string|exists:departments,code',
            'consultation_fee' => 'nullable|numeric|min:0',
        ]);

        if ($validator->fails()) {
            throw new \Exception(implode(', ', $validator->errors()->all()));
        }

        $department = Department::where('code', trim($data['department_code']))->firstOrFail();

        $user = User::create([
            'name' => trim($data['name']),
            'email' => trim($data['email']),
            'password' => Hash::make($data['password'] ?? 'Doctor@123'),
            'role' => 'doctor',
            'phone' => $data['phone'] ?? null,
            'is_active' => true,
        ]);

        Doctor::create([
            'user_id' => $user->id,
            'department_id' => $department->id,
            'specialization' => trim($data['specialization']),
            'license_number' => trim($data['license_number']),
            'qualification' => $data['qualification'] ?? 'MBBS, MD',
            'consultation_fee' => $data['consultation_fee'] ?? 50.00,
            'room_number' => $data['room_number'] ?? 'Clinic 101',
            'biography' => $data['biography'] ?? null,
            'available_days' => ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'],
            'status' => 'active',
        ]);
    }

    /**
     * Import a single medicine / pharmacy stock item.
     */
    protected function importMedicine(array $data, int $rowNum): void
    {
        $validator = Validator::make($data, [
            'brand_name' => 'required|string|max:150',
            'generic_name' => 'required|string|max:150',
            'category' => 'required|string|max:100',
            'dosage_form' => 'required|string|max:50',
            'strength' => 'required|string|max:50',
            'unit_price' => 'required|numeric|min:0',
            'stock_quantity' => 'required|integer|min:0',
            'code' => 'nullable|string|unique:medicines,code',
        ]);

        if ($validator->fails()) {
            throw new \Exception(implode(', ', $validator->errors()->all()));
        }

        $code = ! empty($data['code']) ? trim($data['code']) : Medicine::generateCode();

        Medicine::create([
            'code' => $code,
            'brand_name' => trim($data['brand_name']),
            'generic_name' => trim($data['generic_name']),
            'category' => trim($data['category']),
            'dosage_form' => trim($data['dosage_form']),
            'strength' => trim($data['strength']),
            'unit_price' => (float) $data['unit_price'],
            'purchase_cost' => (float) ($data['purchase_cost'] ?? ((float) $data['unit_price'] * 0.7)),
            'stock_quantity' => (int) $data['stock_quantity'],
            'min_stock_alert' => (int) ($data['min_stock_alert'] ?? 20),
            'batch_number' => $data['batch_number'] ?? 'BAT-'.date('Ymd'),
            'expiry_date' => ! empty($data['expiry_date']) ? Carbon::parse($data['expiry_date'])->format('Y-m-d') : now()->addYears(2)->format('Y-m-d'),
            'manufacturer' => $data['manufacturer'] ?? 'Global Pharma Ltd',
            'storage_condition' => $data['storage_condition'] ?? 'Store below 25°C',
            'status' => (int) $data['stock_quantity'] > 0 ? 'in_stock' : 'out_of_stock',
        ]);
    }

    /**
     * Import a single laboratory test type catalog item.
     */
    protected function importLabTestType(array $data, int $rowNum): void
    {
        $validator = Validator::make($data, [
            'test_code' => 'required|string|max:50|unique:lab_test_types,test_code',
            'name' => 'required|string|max:150',
            'category' => 'required|string|max:100',
            'price' => 'required|numeric|min:0',
        ]);

        if ($validator->fails()) {
            throw new \Exception(implode(', ', $validator->errors()->all()));
        }

        LabTestType::create([
            'test_code' => strtoupper(trim($data['test_code'])),
            'name' => trim($data['name']),
            'category' => trim($data['category']),
            'price' => (float) $data['price'],
            'sample_type' => $data['sample_type'] ?? 'Blood',
            'reference_range' => $data['reference_range'] ?? 'Standard reference interval',
            'turnaround_time_hours' => (int) ($data['turnaround_time_hours'] ?? 24),
            'is_active' => true,
        ]);
    }

    /**
     * Import a single hospital department.
     */
    protected function importDepartment(array $data, int $rowNum): void
    {
        $validator = Validator::make($data, [
            'code' => 'required|string|max:20|unique:departments,code',
            'name' => 'required|string|max:150',
        ]);

        if ($validator->fails()) {
            throw new \Exception(implode(', ', $validator->errors()->all()));
        }

        Department::create([
            'code' => strtoupper(trim($data['code'])),
            'name' => trim($data['name']),
            'description' => $data['description'] ?? null,
            'is_active' => true,
        ]);
    }

    /**
     * Parse CSV into an array of associative rows.
     *
     * @return list<array<string, string>>
     */
    public function parseCsv(string $filePath): array
    {
        if (! file_exists($filePath) || ! is_readable($filePath)) {
            return [];
        }

        $handle = fopen($filePath, 'r');
        if (! $handle) {
            return [];
        }

        // Peek first line to detect delimiter and handle BOM
        $firstLine = fgets($handle);
        if ($firstLine === false) {
            fclose($handle);

            return [];
        }

        // Strip UTF-8 BOM if present
        $cleanFirstLine = preg_replace('/^\xEF\xBB\xBF/', '', $firstLine);
        $delimiter = str_contains($cleanFirstLine, ';') ? ';' : ',';

        // Parse headers from the first cleaned line
        $headers = str_getcsv($cleanFirstLine, $delimiter);
        if (empty($headers) || empty(array_filter($headers))) {
            fclose($handle);

            return [];
        }

        // Normalize headers: lowercase, trim, replace spaces with underscores
        $normalizedHeaders = array_map(function ($h) {
            return strtolower(trim(str_replace([' ', '-'], '_', (string) $h)));
        }, $headers);

        $rows = [];
        while (($cols = fgetcsv($handle, 0, $delimiter)) !== false) {
            // Skip empty rows
            if (count($cols) === 1 && trim((string) $cols[0]) === '') {
                continue;
            }

            if (count($cols) < count($normalizedHeaders)) {
                $cols = array_pad($cols, count($normalizedHeaders), '');
            }

            $row = [];
            foreach ($normalizedHeaders as $idx => $key) {
                $row[$key] = trim((string) ($cols[$idx] ?? ''));
            }
            $rows[] = $row;
        }

        fclose($handle);

        return $rows;
    }

    /**
     * Generate template CSV content with headers and sample records.
     */
    public function generateTemplate(string $entityType): string
    {
        switch ($entityType) {
            case 'patients':
                $headers = [
                    'mrn', 'first_name', 'last_name', 'email', 'phone', 'date_of_birth',
                    'gender', 'blood_group', 'genotype', 'national_id', 'address',
                    'emergency_contact_name', 'emergency_contact_phone', 'emergency_contact_relation',
                    'allergies', 'chronic_diseases', 'insurance_provider', 'insurance_policy_number',
                ];
                $samples = [
                    ['MRN-2026-9001', 'Alexander', 'Wright', 'alex.wright@example.com', '+1-555-0143', '1985-04-12', 'male', 'O+', 'AA', 'NID-884129', '742 Evergreen Terrace, Springfield', 'Mary Wright', '+1-555-0199', 'Spouse', 'Penicillin; Aspirin', 'Hypertension', 'BlueCross Health', 'BC-889211'],
                    ['MRN-2026-9002', 'Elena', 'Rostova', 'elena.rostova@example.com', '+1-555-0182', '1992-09-24', 'female', 'A+', 'AA', 'NID-772194', '124 Maple Drive, Fairview', 'Ivan Rostov', '+1-555-0114', 'Brother', 'Sulfa drugs', 'Asthma', 'Aetna Global', 'AET-441209'],
                ];
                break;

            case 'doctors':
                $headers = [
                    'name', 'email', 'password', 'phone', 'department_code', 'specialization',
                    'license_number', 'qualification', 'consultation_fee', 'room_number', 'biography',
                ];
                $samples = [
                    ['Dr. Robert Chen, MD', 'robert.chen@hospital.local', 'DocPass@2026', '+1-555-9011', 'CARD', 'Consultant Cardiologist', 'MD-CARD-9012', 'MBBS, MD (Cardiology), FACC', '80.00', 'Clinic 204', 'Senior interventional cardiologist with 15+ years experience.'],
                    ['Dr. Sarah Jenkins, MD', 'sarah.jenkins@hospital.local', 'DocPass@2026', '+1-555-9022', 'PEDI', 'Pediatric Specialist', 'MD-PEDI-7741', 'MBBS, DCH, FAAP', '65.00', 'Clinic 108', 'Dedicated specialist in pediatric neonatology and developmental medicine.'],
                ];
                break;

            case 'medicines':
                $headers = [
                    'code', 'brand_name', 'generic_name', 'category', 'dosage_form', 'strength',
                    'unit_price', 'purchase_cost', 'stock_quantity', 'min_stock_alert', 'batch_number',
                    'expiry_date', 'manufacturer', 'storage_condition',
                ];
                $samples = [
                    ['MED-9001', 'Augmentin 625mg', 'Amoxicillin + Clavulanic Acid', 'Antibiotic', 'Tablet', '625mg', '18.50', '12.00', '150', '25', 'BAT-AUG-2026', '2028-11-30', 'GSK Healthcare', 'Store below 25°C in dry place'],
                    ['MED-9002', 'Lipitor 20mg', 'Atorvastatin Calcium', 'Cardiovascular', 'Tablet', '20mg', '22.00', '14.50', '200', '30', 'BAT-LIP-2026', '2028-08-15', 'Pfizer Inc.', 'Protect from light and moisture'],
                ];
                break;

            case 'lab_test_types':
                $headers = [
                    'test_code', 'name', 'category', 'price', 'sample_type', 'reference_range', 'turnaround_time_hours',
                ];
                $samples = [
                    ['CBC-01', 'Complete Blood Count (CBC)', 'Hematology', '25.00', 'Whole Blood (EDTA)', 'WBC: 4.0-11.0 10^9/L, Hb: 13.5-17.5 g/dL, PLT: 150-450 10^9/L', '4'],
                    ['LFT-01', 'Comprehensive Liver Function Test', 'Biochemistry', '45.00', 'Serum', 'ALT: 7-56 U/L, AST: 10-40 U/L, Total Bilirubin: 0.2-1.2 mg/dL', '6'],
                ];
                break;

            case 'departments':
                $headers = ['code', 'name', 'description'];
                $samples = [
                    ['NEUR', 'Neurology Department', 'Comprehensive diagnosis and medical care of nervous system disorders.'],
                    ['ORTH', 'Orthopedic Surgery', 'Specialized surgical and conservative management of musculoskeletal conditions.'],
                ];
                break;

            default:
                throw new \InvalidArgumentException("Unknown template entity type: {$entityType}");
        }

        $output = fopen('php://temp', 'r+');
        fputcsv($output, $headers);
        foreach ($samples as $sample) {
            fputcsv($output, $sample);
        }
        rewind($output);
        $csv = stream_get_contents($output);
        fclose($output);

        return $csv;
    }
}
