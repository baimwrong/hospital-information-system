<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\AuditLog;
use App\Models\Doctor;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\LabRequest;
use App\Models\LabTestResult;
use App\Models\LabTestType;
use App\Models\MedicalRecord;
use App\Models\Medicine;
use App\Models\Patient;
use App\Models\Prescription;
use App\Models\PrescriptionItem;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class MedicalRecordController extends Controller
{
    public function index(Request $request): View
    {
        $query = MedicalRecord::with(['patient', 'doctor.user']);

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('encounter_number', 'like', "%{$search}%")
                    ->orWhere('diagnosis_primary', 'like', "%{$search}%")
                    ->orWhere('diagnosis_icd10_code', 'like', "%{$search}%")
                    ->orWhereHas('patient', function ($pq) use ($search) {
                        $pq->where('first_name', 'like', "%{$search}%")
                            ->orWhere('last_name', 'like', "%{$search}%")
                            ->orWhere('mrn', 'like', "%{$search}%");
                    });
            });
        }

        $records = $query->latest()->paginate(15)->withQueryString();

        return view('encounters.index', compact('records'));
    }

    public function create(Request $request): View
    {
        $patient = null;
        if ($patientId = $request->input('patient_id')) {
            $patient = Patient::findOrFail($patientId);
        }

        $appointment = null;
        if ($aptId = $request->input('appointment_id')) {
            $appointment = Appointment::with(['patient.vitals', 'doctor.user'])->findOrFail($aptId);
            if (! $patient) {
                $patient = $appointment->patient;
            }
        }

        $doctors = Doctor::with('user')->where('status', 'active')->get();
        $patients = Patient::where('status', 'active')->orderBy('first_name')->get();
        $medicines = Medicine::where('stock_quantity', '>', 0)->orderBy('brand_name')->get();
        $labTestTypes = LabTestType::where('is_active', true)->orderBy('name')->get();

        $currentDoctor = Auth::user()->doctor ?? null;

        return view('encounters.create', compact('patient', 'appointment', 'doctors', 'patients', 'medicines', 'labTestTypes', 'currentDoctor'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'patient_id' => 'required|exists:patients,id',
            'doctor_id' => 'required|exists:doctors,id',
            'appointment_id' => 'nullable|exists:appointments,id',
            'chief_complaint' => 'required|string',
            'history_of_present_illness' => 'nullable|string',
            'physical_examination' => 'nullable|string',
            'diagnosis_primary' => 'required|string',
            'diagnosis_icd10_code' => 'nullable|string|max:30',
            'diagnosis_secondary' => 'nullable|string',
            'treatment_plan' => 'nullable|string',
            'doctor_notes' => 'nullable|string',
            'follow_up_date' => 'nullable|date',

            // Optional Prescriptions
            'medicines' => 'nullable|array',
            'medicines.*.id' => 'nullable|exists:medicines,id',
            'medicines.*.dosage' => 'nullable|string',
            'medicines.*.quantity' => 'nullable|integer|min:1',
            'medicines.*.instructions' => 'nullable|string',

            // Optional Lab Tests
            'lab_test_type_ids' => 'nullable|array',
            'lab_test_type_ids.*' => 'exists:lab_test_types,id',
        ]);

        DB::beginTransaction();

        try {
            $validated['encounter_number'] = MedicalRecord::generateNumber();
            $encounter = MedicalRecord::create($validated);

            // Update appointment to completed
            if (! empty($validated['appointment_id'])) {
                $appointment = Appointment::find($validated['appointment_id']);
                if ($appointment) {
                    $appointment->update(['status' => 'completed']);
                }
            }

            // Create Prescription if medicines specified
            if (! empty($request->input('medicines'))) {
                $prescribedMeds = array_filter($request->input('medicines'), fn ($m) => ! empty($m['id']) && ! empty($m['quantity']));
                if (! empty($prescribedMeds)) {
                    $prescription = Prescription::create([
                        'prescription_number' => Prescription::generateNumber(),
                        'patient_id' => $encounter->patient_id,
                        'doctor_id' => $encounter->doctor_id,
                        'medical_record_id' => $encounter->id,
                        'status' => 'pending',
                        'notes' => 'Prescribed during clinical encounter '.$encounter->encounter_number,
                    ]);

                    foreach ($prescribedMeds as $medItem) {
                        $medicine = Medicine::find($medItem['id']);
                        if ($medicine) {
                            PrescriptionItem::create([
                                'prescription_id' => $prescription->id,
                                'medicine_id' => $medicine->id,
                                'dosage' => $medItem['dosage'] ?? '1 tablet daily',
                                'duration_days' => 5,
                                'quantity_prescribed' => (int) $medItem['quantity'],
                                'instructions' => $medItem['instructions'] ?? 'Follow doctor orders',
                                'unit_price' => $medicine->unit_price,
                                'subtotal' => (float) $medicine->unit_price * (int) $medItem['quantity'],
                                'status' => 'pending',
                            ]);
                        }
                    }
                }
            }

            // Create Lab Request if tests specified
            if (! empty($request->input('lab_test_type_ids'))) {
                $testIds = array_filter($request->input('lab_test_type_ids'));
                if (! empty($testIds)) {
                    $labRequest = LabRequest::create([
                        'request_number' => LabRequest::generateNumber(),
                        'patient_id' => $encounter->patient_id,
                        'doctor_id' => $encounter->doctor_id,
                        'medical_record_id' => $encounter->id,
                        'clinical_notes' => 'Clinical indication: '.$encounter->diagnosis_primary,
                        'priority' => 'routine',
                        'status' => 'pending',
                    ]);

                    foreach ($testIds as $testTypeId) {
                        $testType = LabTestType::find($testTypeId);
                        if ($testType) {
                            LabTestResult::create([
                                'lab_request_id' => $labRequest->id,
                                'lab_test_type_id' => $testType->id,
                                'parameter_name' => $testType->name,
                                'result_value' => 'Pending Analysis',
                                'unit' => '-',
                                'reference_range' => $testType->reference_range ?? 'Standard',
                                'flag' => 'normal',
                            ]);
                        }
                    }
                }
            }

            // Create consultation invoice
            $doctor = Doctor::find($encounter->doctor_id);
            if ($doctor && $doctor->consultation_fee > 0) {
                $invoice = Invoice::create([
                    'invoice_number' => Invoice::generateNumber(),
                    'patient_id' => $encounter->patient_id,
                    'appointment_id' => $encounter->appointment_id,
                    'total_amount' => $doctor->consultation_fee,
                    'paid_amount' => 0.00,
                    'balance_due' => $doctor->consultation_fee,
                    'payment_status' => 'unpaid',
                    'invoice_date' => now()->toDateString(),
                    'due_date' => now()->addDays(7)->toDateString(),
                    'generated_by_id' => Auth::id(),
                    'notes' => 'Consultation fee for '.$doctor->specialization,
                ]);

                InvoiceItem::create([
                    'invoice_id' => $invoice->id,
                    'item_type' => 'consultation',
                    'description' => "Doctor Consultation ({$doctor->specialization})",
                    'quantity' => 1,
                    'unit_price' => $doctor->consultation_fee,
                    'subtotal' => $doctor->consultation_fee,
                ]);
            }

            DB::commit();

            AuditLog::log('CREATE', 'EMR', "Completed clinical encounter {$encounter->encounter_number} for patient #{$encounter->patient_id}", (string) $encounter->id);

            return redirect()->route('encounters.show', $encounter)
                ->with('success', "Clinical encounter {$encounter->encounter_number} documented successfully.");
        } catch (\Throwable $e) {
            DB::rollBack();

            return back()->withInput()->withErrors(['error' => 'Failed to record encounter: '.$e->getMessage()]);
        }
    }

    public function show(MedicalRecord $encounter): View
    {
        $encounter->load([
            'patient.vitals',
            'doctor.user',
            'doctor.department',
            'appointment',
            'prescription.items.medicine',
            'labRequests.results.labTestType',
        ]);

        return view('encounters.show', compact('encounter'));
    }
}
