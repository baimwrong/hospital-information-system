<?php

namespace Database\Seeders;

use App\Models\Admission;
use App\Models\Appointment;
use App\Models\AuditLog;
use App\Models\Bed;
use App\Models\Department;
use App\Models\Doctor;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\LabRequest;
use App\Models\LabTestResult;
use App\Models\LabTestType;
use App\Models\MedicalRecord;
use App\Models\Medicine;
use App\Models\Patient;
use App\Models\PatientVital;
use App\Models\Prescription;
use App\Models\PrescriptionItem;
use App\Models\Setting;
use App\Models\User;
use App\Models\Ward;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database with enterprise medical records.
     */
    public function run(): void
    {
        // 1. Hospital Enterprise Settings
        Setting::set('hospital_name', 'Hospital Information System');
        Setting::set('hospital_tagline', 'Advanced Tertiary Care & Clinical Information Management');
        Setting::set('hospital_address', '742 Healthcare Parkway, Medical City, MC 90210');
        Setting::set('hospital_phone', '+1 (555) 839-2000');
        Setting::set('hospital_email', 'contact@hospital.local');
        Setting::set('currency_symbol', '$');
        Setting::set('tax_rate_percent', '5.0');
        Setting::set('timezone', 'America/New_York');

        // 2. Default Staff Accounts (All Hospital Roles)
        $password = Hash::make('password');

        $admin = User::create([
            'name' => 'System Administrator',
            'email' => 'admin@hospital.local',
            'password' => $password,
            'role' => 'admin',
            'phone' => '+1 (555) 010-0001',
            'is_active' => true,
        ]);

        $docUser1 = User::create([
            'name' => 'Dr. Robert Chen, MD',
            'email' => 'dr.chen@hospital.local',
            'password' => $password,
            'role' => 'doctor',
            'phone' => '+1 (555) 010-0002',
            'is_active' => true,
        ]);

        $docUser2 = User::create([
            'name' => 'Dr. Sarah Jenkins, MD',
            'email' => 'dr.jenkins@hospital.local',
            'password' => $password,
            'role' => 'doctor',
            'phone' => '+1 (555) 010-0003',
            'is_active' => true,
        ]);

        $docUser3 = User::create([
            'name' => 'Dr. Marcus Vance, MD',
            'email' => 'dr.vance@hospital.local',
            'password' => $password,
            'role' => 'doctor',
            'phone' => '+1 (555) 010-0004',
            'is_active' => true,
        ]);

        $nurse = User::create([
            'name' => 'Nurse Emily Watson, RN',
            'email' => 'nurse@hospital.local',
            'password' => $password,
            'role' => 'nurse',
            'phone' => '+1 (555) 010-0005',
            'is_active' => true,
        ]);

        $pharmacist = User::create([
            'name' => 'James Miller, PharmD',
            'email' => 'pharmacist@hospital.local',
            'password' => $password,
            'role' => 'pharmacist',
            'phone' => '+1 (555) 010-0006',
            'is_active' => true,
        ]);

        $laboratorist = User::create([
            'name' => 'David Kim, MLS',
            'email' => 'lab@hospital.local',
            'password' => $password,
            'role' => 'laboratorist',
            'phone' => '+1 (555) 010-0007',
            'is_active' => true,
        ]);

        $receptionist = User::create([
            'name' => 'Amanda Brooks',
            'email' => 'reception@hospital.local',
            'password' => $password,
            'role' => 'receptionist',
            'phone' => '+1 (555) 010-0008',
            'is_active' => true,
        ]);

        $accountant = User::create([
            'name' => 'Michael Sterling, CPA',
            'email' => 'billing@hospital.local',
            'password' => $password,
            'role' => 'accountant',
            'phone' => '+1 (555) 010-0009',
            'is_active' => true,
        ]);

        // 3. Departments
        $cardiology = Department::create([
            'code' => 'CARD',
            'name' => 'Cardiovascular Medicine',
            'description' => 'Comprehensive diagnosis, interventional therapy, and cardiac intensive care.',
            'head_of_department_id' => $docUser1->id,
            'is_active' => true,
        ]);

        $pediatrics = Department::create([
            'code' => 'PEDI',
            'name' => 'Pediatrics & Neonatology',
            'description' => 'Complete pediatric primary care, adolescent medicine, and neonatal nursery.',
            'head_of_department_id' => $docUser2->id,
            'is_active' => true,
        ]);

        $internalMedicine = Department::create([
            'code' => 'GEN',
            'name' => 'Internal Medicine',
            'description' => 'Adult general medicine, chronic disease management, and multisystem care.',
            'head_of_department_id' => $docUser3->id,
            'is_active' => true,
        ]);

        $emergency = Department::create([
            'code' => 'EMER',
            'name' => 'Emergency & Trauma Services',
            'description' => '24/7 Level-1 Trauma response, rapid triage, and critical resuscitation unit.',
            'is_active' => true,
        ]);

        $orthopedics = Department::create([
            'code' => 'ORTH',
            'name' => 'Orthopedics & Joint Reconstruction',
            'description' => 'Surgical and non-surgical treatment of musculoskeletal conditions and trauma.',
            'is_active' => true,
        ]);

        $neurology = Department::create([
            'code' => 'NEUR',
            'name' => 'Neurology & Stroke Center',
            'description' => 'Specialized care for neurological disorders, epilepsy, stroke, and neuromuscular diseases.',
            'is_active' => true,
        ]);

        // 4. Doctors
        $doctor1 = Doctor::create([
            'user_id' => $docUser1->id,
            'department_id' => $cardiology->id,
            'specialization' => 'Senior Consultant Cardiologist',
            'license_number' => 'MD-CARD-2018-091',
            'qualification' => 'MBBS, MD (Cardiology), FACC',
            'consultation_fee' => 95.00,
            'room_number' => 'Clinic 201 (2nd Floor)',
            'biography' => 'Board-certified cardiologist specializing in coronary artery disease and echocardiography.',
            'available_days' => ['Monday', 'Tuesday', 'Wednesday', 'Thursday'],
            'status' => 'active',
        ]);

        $doctor2 = Doctor::create([
            'user_id' => $docUser2->id,
            'department_id' => $pediatrics->id,
            'specialization' => 'Consultant Pediatrician',
            'license_number' => 'MD-PEDI-2020-412',
            'qualification' => 'MBBS, DCH, FAAP',
            'consultation_fee' => 75.00,
            'room_number' => 'Clinic 105 (1st Floor)',
            'biography' => 'Specialist in pediatric development, immunization, and childhood infectious illnesses.',
            'available_days' => ['Monday', 'Tuesday', 'Wednesday', 'Friday'],
            'status' => 'active',
        ]);

        $doctor3 = Doctor::create([
            'user_id' => $docUser3->id,
            'department_id' => $internalMedicine->id,
            'specialization' => 'General Physician & Internist',
            'license_number' => 'MD-GEN-2019-338',
            'qualification' => 'MBBS, MD (Internal Med), MRCP (UK)',
            'consultation_fee' => 65.00,
            'room_number' => 'Clinic 102 (1st Floor)',
            'biography' => 'Expertise in metabolic syndrome, diabetic control, and preventive healthcare.',
            'available_days' => ['Monday', 'Wednesday', 'Thursday', 'Friday'],
            'status' => 'active',
        ]);

        // 5. Wards & Beds
        $icuWard = Ward::create([
            'name' => 'Intensive Care Unit (ICU)',
            'type' => 'icu',
            'floor' => '3rd Floor - East Wing',
            'capacity' => 4,
            'gender_preference' => 'mixed',
            'daily_rate' => 350.00,
            'is_active' => true,
        ]);

        $genWard = Ward::create([
            'name' => 'General Medical Ward A',
            'type' => 'general',
            'floor' => '2nd Floor - North Wing',
            'capacity' => 6,
            'gender_preference' => 'mixed',
            'daily_rate' => 120.00,
            'is_active' => true,
        ]);

        $pedWard = Ward::create([
            'name' => 'Pediatrics Inpatient Ward',
            'type' => 'pediatric',
            'floor' => '2nd Floor - South Wing',
            'capacity' => 4,
            'gender_preference' => 'mixed',
            'daily_rate' => 140.00,
            'is_active' => true,
        ]);

        $vipSuite = Ward::create([
            'name' => 'Executive VIP Suite',
            'type' => 'vip',
            'floor' => '4th Floor - Penthouse',
            'capacity' => 2,
            'gender_preference' => 'mixed',
            'daily_rate' => 500.00,
            'is_active' => true,
        ]);

        // Beds for ICU
        $icuBeds = [];
        for ($i = 1; $i <= 4; $i++) {
            $icuBeds[] = Bed::create([
                'ward_id' => $icuWard->id,
                'bed_number' => sprintf('ICU-%02d', $i),
                'bed_type' => 'icu',
                'status' => $i === 1 ? 'occupied' : 'available',
                'daily_rate' => $icuWard->daily_rate,
            ]);
        }

        // Beds for General Ward
        $genBeds = [];
        for ($i = 1; $i <= 6; $i++) {
            $genBeds[] = Bed::create([
                'ward_id' => $genWard->id,
                'bed_number' => sprintf('GW-A%02d', $i),
                'bed_type' => 'standard',
                'status' => $i === 1 ? 'occupied' : ($i === 2 ? 'cleaning' : 'available'),
                'daily_rate' => $genWard->daily_rate,
            ]);
        }

        // Beds for Ped Ward
        for ($i = 1; $i <= 4; $i++) {
            Bed::create([
                'ward_id' => $pedWard->id,
                'bed_number' => sprintf('PED-%02d', $i),
                'bed_type' => 'pediatric',
                'status' => 'available',
                'daily_rate' => $pedWard->daily_rate,
            ]);
        }

        // Beds for VIP
        for ($i = 1; $i <= 2; $i++) {
            Bed::create([
                'ward_id' => $vipSuite->id,
                'bed_number' => sprintf('VIP-%02d', $i),
                'bed_type' => 'electric',
                'status' => 'available',
                'daily_rate' => $vipSuite->daily_rate,
            ]);
        }

        // 6. Pharmacy Formulary (Medicines)
        $meds = [
            [
                'code' => 'MED-0001',
                'brand_name' => 'Augmentin 625mg',
                'generic_name' => 'Amoxicillin + Clavulanic Acid',
                'category' => 'Antibiotics',
                'dosage_form' => 'Tablet',
                'strength' => '625mg',
                'unit_price' => 19.50,
                'purchase_cost' => 12.00,
                'stock_quantity' => 180,
                'min_stock_alert' => 25,
                'batch_number' => 'BAT-AUG-991',
                'expiry_date' => Carbon::now()->addMonths(24)->toDateString(),
                'manufacturer' => 'GlaxoSmithKline',
                'storage_condition' => 'Store below 25°C',
            ],
            [
                'code' => 'MED-0002',
                'brand_name' => 'Panadol Extra',
                'generic_name' => 'Paracetamol + Caffeine',
                'category' => 'Analgesics & Antipyretics',
                'dosage_form' => 'Tablet',
                'strength' => '500mg / 65mg',
                'unit_price' => 4.50,
                'purchase_cost' => 2.20,
                'stock_quantity' => 350,
                'min_stock_alert' => 50,
                'batch_number' => 'BAT-PAN-882',
                'expiry_date' => Carbon::now()->addMonths(30)->toDateString(),
                'manufacturer' => 'Haleon Healthcare',
                'storage_condition' => 'Room temperature',
            ],
            [
                'code' => 'MED-0003',
                'brand_name' => 'Lipitor 20mg',
                'generic_name' => 'Atorvastatin Calcium',
                'category' => 'Cardiovascular',
                'dosage_form' => 'Tablet',
                'strength' => '20mg',
                'unit_price' => 26.00,
                'purchase_cost' => 17.00,
                'stock_quantity' => 12, // LOW STOCK TRIGGER
                'min_stock_alert' => 20,
                'batch_number' => 'BAT-LIP-773',
                'expiry_date' => Carbon::now()->addMonths(18)->toDateString(),
                'manufacturer' => 'Pfizer Inc.',
                'storage_condition' => 'Protect from direct light',
            ],
            [
                'code' => 'MED-0004',
                'brand_name' => 'Glucophage 850mg',
                'generic_name' => 'Metformin Hydrochloride',
                'category' => 'Antidiabetic',
                'dosage_form' => 'Tablet',
                'strength' => '850mg',
                'unit_price' => 11.20,
                'purchase_cost' => 6.50,
                'stock_quantity' => 140,
                'min_stock_alert' => 25,
                'batch_number' => 'BAT-GLU-664',
                'expiry_date' => Carbon::now()->addMonths(22)->toDateString(),
                'manufacturer' => 'Merck KGaA',
                'storage_condition' => 'Store below 30°C',
            ],
            [
                'code' => 'MED-0005',
                'brand_name' => 'Norvasc 5mg',
                'generic_name' => 'Amlodipine Besylate',
                'category' => 'Cardiovascular',
                'dosage_form' => 'Tablet',
                'strength' => '5mg',
                'unit_price' => 14.80,
                'purchase_cost' => 9.00,
                'stock_quantity' => 8, // LOW STOCK TRIGGER
                'min_stock_alert' => 15,
                'batch_number' => 'BAT-NOR-555',
                'expiry_date' => Carbon::now()->addMonths(16)->toDateString(),
                'manufacturer' => 'Pfizer Inc.',
                'storage_condition' => 'Room temperature',
            ],
            [
                'code' => 'MED-0006',
                'brand_name' => 'Ventolin Inhaler',
                'generic_name' => 'Salbutamol Sulfate',
                'category' => 'Respiratory',
                'dosage_form' => 'Aerosol Inhaler',
                'strength' => '100mcg/dose (200 doses)',
                'unit_price' => 22.00,
                'purchase_cost' => 14.00,
                'stock_quantity' => 45,
                'min_stock_alert' => 10,
                'batch_number' => 'BAT-VEN-446',
                'expiry_date' => Carbon::now()->addMonths(12)->toDateString(),
                'manufacturer' => 'GlaxoSmithKline',
                'storage_condition' => 'Store below 30°C, do not freeze',
            ],
            [
                'code' => 'MED-0007',
                'brand_name' => 'Nexium 40mg',
                'generic_name' => 'Esomeprazole Magnesium',
                'category' => 'Gastrointestinal',
                'dosage_form' => 'Capsule',
                'strength' => '40mg',
                'unit_price' => 28.50,
                'purchase_cost' => 18.00,
                'stock_quantity' => 95,
                'min_stock_alert' => 20,
                'batch_number' => 'BAT-NEX-337',
                'expiry_date' => Carbon::now()->addMonths(20)->toDateString(),
                'manufacturer' => 'AstraZeneca',
                'storage_condition' => 'Store below 25°C',
            ],
            [
                'code' => 'MED-0008',
                'brand_name' => 'Normal Saline 0.9% IV',
                'generic_name' => 'Sodium Chloride 0.9%',
                'category' => 'Intravenous Infusion',
                'dosage_form' => 'IV Fluid Bag',
                'strength' => '500ml',
                'unit_price' => 9.00,
                'purchase_cost' => 4.50,
                'stock_quantity' => 210,
                'min_stock_alert' => 40,
                'batch_number' => 'BAT-NS-228',
                'expiry_date' => Carbon::now()->addMonths(36)->toDateString(),
                'manufacturer' => 'Baxter Healthcare',
                'storage_condition' => 'Store at 20-25°C',
            ],
        ];

        $medicineModels = [];
        foreach ($meds as $m) {
            $medicineModels[] = Medicine::create($m);
        }

        // 7. Laboratory Test Catalog
        $tests = [
            [
                'test_code' => 'CBC-01',
                'name' => 'Complete Blood Count (CBC) with Differential',
                'category' => 'Hematology',
                'price' => 30.00,
                'sample_type' => 'Whole Blood (EDTA Tube)',
                'reference_range' => 'WBC: 4.5-11.0 10^3/uL, RBC: 4.3-5.9 10^6/uL, Hb: 13.5-17.5 g/dL, Platelets: 150-450 10^3/uL',
                'turnaround_time_hours' => 2,
            ],
            [
                'test_code' => 'LIP-02',
                'name' => 'Fasting Lipid Panel Profile',
                'category' => 'Biochemistry',
                'price' => 42.00,
                'sample_type' => 'Serum (Gold SST Tube)',
                'reference_range' => 'Total Cholesterol: <200 mg/dL, HDL: >40 mg/dL, LDL: <100 mg/dL, Triglycerides: <150 mg/dL',
                'turnaround_time_hours' => 4,
            ],
            [
                'test_code' => 'CMP-03',
                'name' => 'Comprehensive Metabolic Panel (CMP-14)',
                'category' => 'Biochemistry',
                'price' => 55.00,
                'sample_type' => 'Serum (Gold SST Tube)',
                'reference_range' => 'Glucose: 70-99 mg/dL, Calcium: 8.5-10.2 mg/dL, Sodium: 135-145 mEq/L, Potassium: 3.5-5.0 mEq/L, Creatinine: 0.6-1.2 mg/dL',
                'turnaround_time_hours' => 4,
            ],
            [
                'test_code' => 'HBA1C-04',
                'name' => 'Glycated Hemoglobin (HbA1c)',
                'category' => 'Endocrinology',
                'price' => 38.00,
                'sample_type' => 'Whole Blood (EDTA Tube)',
                'reference_range' => 'Normal: <5.7%, Prediabetes: 5.7-6.4%, Diabetes: >=6.5%',
                'turnaround_time_hours' => 3,
            ],
            [
                'test_code' => 'URIN-05',
                'name' => 'Urinalysis Complete Routine & Microscopy',
                'category' => 'Clinical Pathology',
                'price' => 22.00,
                'sample_type' => 'Clean-catch Midstream Urine',
                'reference_range' => 'Color: Pale Yellow, pH: 5.0-8.0, Protein: Negative, Glucose: Negative, Ketones: Negative',
                'turnaround_time_hours' => 2,
            ],
            [
                'test_code' => 'TROP-06',
                'name' => 'High-Sensitivity Cardiac Troponin-I (hs-cTnI)',
                'category' => 'Cardiology Biomarkers',
                'price' => 60.00,
                'sample_type' => 'Plasma (Heparin Tube)',
                'reference_range' => 'Normal: <14 ng/L, Elevated (>14 ng/L indicates myocardial injury)',
                'turnaround_time_hours' => 1,
            ],
        ];

        $testModels = [];
        foreach ($tests as $t) {
            $testModels[] = LabTestType::create($t);
        }

        // 8. Patients Master Records
        $patient1 = Patient::create([
            'mrn' => 'MRN-2026-0001',
            'first_name' => 'Eleanor',
            'last_name' => 'Patterson',
            'email' => 'eleanor.patterson@example.com',
            'phone' => '+1 (555) 301-4491',
            'date_of_birth' => '1968-07-14',
            'gender' => 'female',
            'blood_group' => 'A+',
            'genotype' => 'AA',
            'national_id' => 'US-SSN-8812',
            'address' => '344 Oakview Terrace, Springfield',
            'emergency_contact_name' => 'Arthur Patterson',
            'emergency_contact_phone' => '+1 (555) 301-8844',
            'emergency_contact_relation' => 'Husband',
            'allergies' => ['Penicillin', 'Sulfa drugs'],
            'chronic_diseases' => ['Essential Hypertension', 'Type 2 Diabetes'],
            'insurance_provider' => 'Blue Cross Blue Shield',
            'insurance_policy_number' => 'BCBS-994102-A',
            'status' => 'active',
        ]);

        $patient2 = Patient::create([
            'mrn' => 'MRN-2026-0002',
            'first_name' => 'Daniel',
            'last_name' => 'Kovacs',
            'email' => 'daniel.kovacs@example.com',
            'phone' => '+1 (555) 412-9982',
            'date_of_birth' => '1984-11-23',
            'gender' => 'male',
            'blood_group' => 'O+',
            'genotype' => 'AA',
            'national_id' => 'US-SSN-4491',
            'address' => '182 Riverbend Road, Northfield',
            'emergency_contact_name' => 'Klara Kovacs',
            'emergency_contact_phone' => '+1 (555) 412-0033',
            'emergency_contact_relation' => 'Wife',
            'allergies' => ['Aspirin'],
            'chronic_diseases' => ['Hypercholesterolemia'],
            'insurance_provider' => 'UnitedHealthcare',
            'insurance_policy_number' => 'UHC-481200-Z',
            'status' => 'active',
        ]);

        $patient3 = Patient::create([
            'mrn' => 'MRN-2026-0003',
            'first_name' => 'Sophia',
            'last_name' => 'Martinez',
            'email' => 'sophia.martinez@example.com',
            'phone' => '+1 (555) 782-1144',
            'date_of_birth' => '2018-03-09',
            'gender' => 'female',
            'blood_group' => 'B+',
            'genotype' => 'AA',
            'national_id' => 'US-SSN-1928',
            'address' => '514 Sunshine Boulevard, Fairview',
            'emergency_contact_name' => 'Carlos Martinez',
            'emergency_contact_phone' => '+1 (555) 782-9901',
            'emergency_contact_relation' => 'Father',
            'allergies' => ['Peanuts'],
            'chronic_diseases' => ['Mild Asthma'],
            'insurance_provider' => 'Aetna Health',
            'insurance_policy_number' => 'AET-771402-C',
            'status' => 'active',
        ]);

        $patient4 = Patient::create([
            'mrn' => 'MRN-2026-0004',
            'first_name' => 'William',
            'last_name' => 'Thorne',
            'email' => 'william.thorne@example.com',
            'phone' => '+1 (555) 919-6632',
            'date_of_birth' => '1955-09-02',
            'gender' => 'male',
            'blood_group' => 'AB-',
            'genotype' => 'AS',
            'national_id' => 'US-SSN-6621',
            'address' => '88 Highland Crest, Kingsport',
            'emergency_contact_name' => 'Grace Thorne',
            'emergency_contact_phone' => '+1 (555) 919-7711',
            'emergency_contact_relation' => 'Daughter',
            'allergies' => ['Codeine'],
            'chronic_diseases' => ['Coronary Artery Disease', 'Osteoarthritis'],
            'insurance_provider' => 'Medicare Part B',
            'insurance_policy_number' => 'MED-009124-T',
            'status' => 'active',
        ]);

        // 9. Vitals Recording
        $vital1 = PatientVital::create([
            'patient_id' => $patient1->id,
            'recorded_by_id' => $nurse->id,
            'temperature' => 37.1,
            'blood_pressure_systolic' => 148,
            'blood_pressure_diastolic' => 92,
            'heart_rate' => 78,
            'respiratory_rate' => 17,
            'oxygen_saturation' => 98,
            'weight' => 74.5,
            'height' => 165.0,
            'bmi' => 27.4,
            'triage_level' => 'urgent',
            'clinical_notes' => 'Patient complains of recurrent morning headaches and blurred vision episodes.',
            'recorded_at' => Carbon::now()->subHours(2),
        ]);

        $vital2 = PatientVital::create([
            'patient_id' => $patient2->id,
            'recorded_by_id' => $nurse->id,
            'temperature' => 36.8,
            'blood_pressure_systolic' => 122,
            'blood_pressure_diastolic' => 80,
            'heart_rate' => 70,
            'respiratory_rate' => 15,
            'oxygen_saturation' => 99,
            'weight' => 82.0,
            'height' => 180.0,
            'bmi' => 25.3,
            'triage_level' => 'semi_urgent',
            'clinical_notes' => 'Routine follow-up for lipid management.',
            'recorded_at' => Carbon::now()->subDays(1),
        ]);

        $vital3 = PatientVital::create([
            'patient_id' => $patient4->id,
            'recorded_by_id' => $nurse->id,
            'temperature' => 37.4,
            'blood_pressure_systolic' => 165,
            'blood_pressure_diastolic' => 102,
            'heart_rate' => 96,
            'respiratory_rate' => 22,
            'oxygen_saturation' => 94,
            'weight' => 88.0,
            'height' => 176.0,
            'bmi' => 28.4,
            'triage_level' => 'emergency',
            'clinical_notes' => 'Substernal chest pressure radiating to left arm. Admitted to ICU.',
            'recorded_at' => Carbon::now()->subHours(8),
        ]);

        // 10. Appointments
        $apt1 = Appointment::create([
            'appointment_number' => 'APT-2026-0001',
            'patient_id' => $patient1->id,
            'doctor_id' => $doctor1->id,
            'department_id' => $cardiology->id,
            'appointment_date' => Carbon::today()->toDateString(),
            'appointment_time' => '09:30',
            'priority' => 'urgent',
            'status' => 'completed',
            'reason_for_visit' => 'Elevated blood pressure checks and palpitations.',
        ]);

        $apt2 = Appointment::create([
            'appointment_number' => 'APT-2026-0002',
            'patient_id' => $patient2->id,
            'doctor_id' => $doctor3->id,
            'department_id' => $internalMedicine->id,
            'appointment_date' => Carbon::today()->toDateString(),
            'appointment_time' => '11:00',
            'priority' => 'routine',
            'status' => 'in_consultation',
            'reason_for_visit' => 'Three-month cholesterol medication follow-up.',
        ]);

        $apt3 = Appointment::create([
            'appointment_number' => 'APT-2026-0003',
            'patient_id' => $patient3->id,
            'doctor_id' => $doctor2->id,
            'department_id' => $pediatrics->id,
            'appointment_date' => Carbon::today()->addDay()->toDateString(),
            'appointment_time' => '10:00',
            'priority' => 'routine',
            'status' => 'scheduled',
            'reason_for_visit' => 'Annual developmental check-up and seasonal allergy evaluation.',
        ]);

        // 11. Clinical Encounters (EMR)
        $enc1 = MedicalRecord::create([
            'encounter_number' => 'ENC-2026-0001',
            'patient_id' => $patient1->id,
            'doctor_id' => $doctor1->id,
            'appointment_id' => $apt1->id,
            'chief_complaint' => 'Occipital throbbing headache with exertional shortness of breath.',
            'history_of_present_illness' => '57-year-old female presents with 2 weeks of worsening blood pressure readings at home (150-160s systolic). Reports no chest pain, syncope, or numbness.',
            'physical_examination' => 'Alert, oriented x3. Cardiac: Regular rate, S1/S2 present, no murmurs. Lungs: Clear bilaterally. Lower extremities: Trace pedal edema.',
            'diagnosis_primary' => 'Stage 2 Essential Hypertension',
            'diagnosis_icd10_code' => 'I10',
            'diagnosis_secondary' => 'Type 2 Diabetes Mellitus without complications (E11.9)',
            'treatment_plan' => 'Up-titrate Amlodipine 5mg to 10mg daily. Continue Metformin 850mg twice daily with meals. Repeat CBC and Renal Function Panel in 2 weeks. Low sodium diet.',
            'doctor_notes' => 'Discussed lifestyle modifications and blood pressure journaling.',
            'follow_up_date' => Carbon::today()->addWeeks(2)->toDateString(),
        ]);

        // 12. Prescriptions
        $rx1 = Prescription::create([
            'prescription_number' => 'RX-2026-0001',
            'patient_id' => $patient1->id,
            'doctor_id' => $doctor1->id,
            'medical_record_id' => $enc1->id,
            'status' => 'dispensed',
            'dispensed_by_id' => $pharmacist->id,
            'dispensed_at' => Carbon::now()->subHour(),
            'notes' => 'Patient advised regarding ankle swelling as potential amlodipine side effect.',
        ]);

        PrescriptionItem::create([
            'prescription_id' => $rx1->id,
            'medicine_id' => $medicineModels[4]->id, // Norvasc
            'dosage' => '5mg daily in morning',
            'frequency' => 'Once daily',
            'duration_days' => 30,
            'quantity_prescribed' => 30,
            'quantity_dispensed' => 30,
            'instructions' => 'Take with water before breakfast',
            'unit_price' => $medicineModels[4]->unit_price,
            'subtotal' => 30 * $medicineModels[4]->unit_price,
            'status' => 'dispensed',
        ]);

        // 13. Lab Requests & Results
        $labReq1 = LabRequest::create([
            'request_number' => 'LAB-2026-0001',
            'patient_id' => $patient1->id,
            'doctor_id' => $doctor1->id,
            'medical_record_id' => $enc1->id,
            'clinical_notes' => 'Assess renal clearance and baseline electrolyte status on anti-hypertensives.',
            'priority' => 'routine',
            'status' => 'completed',
            'technician_id' => $laboratorist->id,
            'collected_at' => Carbon::now()->subHours(3),
            'completed_at' => Carbon::now()->subHour(),
        ]);

        LabTestResult::create([
            'lab_request_id' => $labReq1->id,
            'lab_test_type_id' => $testModels[0]->id, // CBC
            'parameter_name' => 'Hemoglobin (Hb)',
            'result_value' => '13.8',
            'unit' => 'g/dL',
            'reference_range' => '12.0 - 16.0 g/dL',
            'flag' => 'normal',
        ]);

        LabTestResult::create([
            'lab_request_id' => $labReq1->id,
            'lab_test_type_id' => $testModels[0]->id, // CBC
            'parameter_name' => 'White Blood Cell (WBC)',
            'result_value' => '6.4',
            'unit' => '10^3/uL',
            'reference_range' => '4.5 - 11.0 10^3/uL',
            'flag' => 'normal',
        ]);

        LabTestResult::create([
            'lab_request_id' => $labReq1->id,
            'lab_test_type_id' => $testModels[2]->id, // CMP
            'parameter_name' => 'Serum Creatinine',
            'result_value' => '0.9',
            'unit' => 'mg/dL',
            'reference_range' => '0.6 - 1.2 mg/dL',
            'flag' => 'normal',
        ]);

        // 14. Inpatient Admissions
        $admission1 = Admission::create([
            'admission_number' => 'ADM-2026-0001',
            'patient_id' => $patient4->id,
            'doctor_id' => $doctor1->id,
            'bed_id' => $icuBeds[0]->id,
            'admission_date' => Carbon::now()->subDays(2),
            'admission_reason' => 'Acute Coronary Syndrome - NSTEMI with hemodynamic monitoring required.',
            'status' => 'admitted',
        ]);

        // 15. Invoices & Billing
        $inv1 = Invoice::create([
            'invoice_number' => 'INV-2026-0001',
            'patient_id' => $patient1->id,
            'appointment_id' => $apt1->id,
            'total_amount' => 180.00,
            'discount_amount' => 0.00,
            'tax_amount' => 0.00,
            'paid_amount' => 180.00,
            'balance_due' => 0.00,
            'payment_status' => 'paid',
            'payment_method' => 'credit_card',
            'invoice_date' => Carbon::today()->toDateString(),
            'due_date' => Carbon::today()->toDateString(),
            'generated_by_id' => $accountant->id,
            'notes' => 'Settled via Visa credit card terminal at checkout.',
        ]);

        InvoiceItem::create([
            'invoice_id' => $inv1->id,
            'item_type' => 'consultation',
            'description' => 'Cardiology Specialist Consultation (Dr. Chen)',
            'quantity' => 1,
            'unit_price' => 95.00,
            'subtotal' => 95.00,
        ]);

        InvoiceItem::create([
            'invoice_id' => $inv1->id,
            'item_type' => 'lab_test',
            'description' => 'Complete Blood Count (CBC) with Differential',
            'quantity' => 1,
            'unit_price' => 30.00,
            'subtotal' => 30.00,
        ]);

        InvoiceItem::create([
            'invoice_id' => $inv1->id,
            'item_type' => 'lab_test',
            'description' => 'Comprehensive Metabolic Panel (CMP-14)',
            'quantity' => 1,
            'unit_price' => 55.00,
            'subtotal' => 55.00,
        ]);

        // Audit Log
        AuditLog::log('SEED', 'System', 'Populated initial hospital departments, medical staff, patients, inventory, and clinical data.');
    }
}
