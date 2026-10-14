<?php

namespace App\Http\Controllers;

use App\Models\Admission;
use App\Models\AuditLog;
use App\Models\Bed;
use App\Models\Doctor;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Patient;
use App\Models\Ward;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class InpatientController extends Controller
{
    public function index(Request $request): View
    {
        $query = Admission::with(['patient', 'doctor.user', 'bed.ward']);

        if ($status = $request->input('status')) {
            $query->where('status', $status);
        } else {
            // Default to admitted
            $query->where('status', 'admitted');
        }

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('admission_number', 'like', "%{$search}%")
                    ->orWhereHas('patient', function ($pq) use ($search) {
                        $pq->where('first_name', 'like', "%{$search}%")
                            ->orWhere('last_name', 'like', "%{$search}%")
                            ->orWhere('mrn', 'like', "%{$search}%");
                    });
            });
        }

        $admissions = $query->latest('admission_date')->paginate(15)->withQueryString();
        $totalAdmitted = Admission::where('status', 'admitted')->count();
        $totalBeds = Bed::count();
        $availableBeds = Bed::where('status', 'available')->count();
        $occupiedBeds = Bed::where('status', 'occupied')->count();

        return view('inpatient.index', compact('admissions', 'totalAdmitted', 'totalBeds', 'availableBeds', 'occupiedBeds'));
    }

    public function wards(): View
    {
        $wards = Ward::with(['beds' => fn ($q) => $q->with('currentAdmission.patient')])->get();

        return view('inpatient.wards', compact('wards'));
    }

    public function storeWard(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'type' => 'required|in:general,icu,maternity,pediatric,surgical,vip,isolation',
            'floor' => 'nullable|string|max:50',
            'capacity' => 'required|integer|min:1',
            'gender_preference' => 'required|in:mixed,male,female',
            'daily_rate' => 'required|numeric|min:0',
        ]);

        $ward = Ward::create($validated);

        // Automatically create beds for the ward
        for ($i = 1; $i <= $ward->capacity; $i++) {
            Bed::create([
                'ward_id' => $ward->id,
                'bed_number' => sprintf('%s-%02d', strtoupper(substr($ward->type, 0, 3)), $i),
                'bed_type' => $ward->type === 'icu' ? 'icu' : 'standard',
                'status' => 'available',
                'daily_rate' => $ward->daily_rate,
            ]);
        }

        AuditLog::log('CREATE', 'IPD', "Created ward {$ward->name} with {$ward->capacity} beds", (string) $ward->id);

        return back()->with('success', "Ward {$ward->name} created with {$ward->capacity} beds.");
    }

    public function createAdmission(Request $request): View
    {
        $patients = Patient::where('status', 'active')->orderBy('first_name')->get();
        $doctors = Doctor::with('user')->where('status', 'active')->get();
        $availableBeds = Bed::with('ward')->where('status', 'available')->get();
        $nextNumber = Admission::generateNumber();

        $selectedPatient = null;
        if ($pId = $request->input('patient_id')) {
            $selectedPatient = Patient::find($pId);
        }

        return view('inpatient.create-admission', compact('patients', 'doctors', 'availableBeds', 'nextNumber', 'selectedPatient'));
    }

    public function storeAdmission(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'patient_id' => 'required|exists:patients,id',
            'doctor_id' => 'required|exists:doctors,id',
            'bed_id' => 'required|exists:beds,id',
            'admission_reason' => 'required|string',
        ]);

        DB::beginTransaction();

        try {
            $bed = Bed::findOrFail($validated['bed_id']);
            if ($bed->status !== 'available') {
                return back()->withInput()->withErrors(['bed_id' => 'This bed is currently occupied or under maintenance.']);
            }

            $validated['admission_number'] = Admission::generateNumber();
            $validated['admission_date'] = now();
            $validated['status'] = 'admitted';

            $admission = Admission::create($validated);
            $bed->update(['status' => 'occupied']);

            DB::commit();

            AuditLog::log('ADMISSION', 'IPD', "Admitted patient #{$admission->patient_id} to Bed {$bed->bed_number} ({$admission->admission_number})", (string) $admission->id);

            return redirect()->route('inpatient.show', $admission)
                ->with('success', "Patient admitted successfully. Assigned to Bed {$bed->bed_number}.");
        } catch (\Throwable $e) {
            DB::rollBack();

            return back()->withInput()->withErrors(['error' => 'Failed to admit patient: '.$e->getMessage()]);
        }
    }

    public function show(Admission $admission): View
    {
        $admission->load(['patient.vitals', 'doctor.user', 'bed.ward']);

        return view('inpatient.show', compact('admission'));
    }

    public function discharge(Request $request, Admission $admission): RedirectResponse
    {
        $validated = $request->validate([
            'discharge_summary' => 'required|string',
            'condition_at_discharge' => 'required|in:recovered,improved,referred,against_medical_advice',
        ]);

        DB::beginTransaction();

        try {
            $admission->discharge_date = now();
            $admission->status = 'discharged';
            $admission->discharge_summary = $validated['discharge_summary'];
            $admission->condition_at_discharge = $validated['condition_at_discharge'];
            $admission->save();

            // Release bed
            $admission->bed->update(['status' => 'cleaning']);

            // Calculate inpatient bed charges and generate invoice
            $days = $admission->duration_days;
            $dailyRate = $admission->bed->effective_rate;
            $totalBedCost = $days * $dailyRate;

            $invoice = Invoice::create([
                'invoice_number' => Invoice::generateNumber(),
                'patient_id' => $admission->patient_id,
                'admission_id' => $admission->id,
                'total_amount' => $totalBedCost,
                'paid_amount' => 0.00,
                'balance_due' => $totalBedCost,
                'payment_status' => 'unpaid',
                'invoice_date' => now()->toDateString(),
                'due_date' => now()->addDays(3)->toDateString(),
                'generated_by_id' => Auth::id(),
                'notes' => "Inpatient accommodation: {$days} day(s) in {$admission->bed->ward->name} (Bed {$admission->bed->bed_number})",
            ]);

            InvoiceItem::create([
                'invoice_id' => $invoice->id,
                'item_type' => 'bed_charge',
                'description' => "Ward Accommodation: {$admission->bed->ward->name} (Bed {$admission->bed->bed_number}) - {$days} days",
                'quantity' => $days,
                'unit_price' => $dailyRate,
                'subtotal' => $totalBedCost,
            ]);

            DB::commit();

            AuditLog::log('DISCHARGE', 'IPD', "Discharged patient from admission {$admission->admission_number}. Bed charge invoice generated: {$invoice->invoice_number}", (string) $admission->id);

            return redirect()->route('inpatient.show', $admission)
                ->with('success', "Patient discharged successfully. Discharge invoice {$invoice->invoice_number} created.");
        } catch (\Throwable $e) {
            DB::rollBack();

            return back()->withErrors(['error' => 'Failed to discharge patient: '.$e->getMessage()]);
        }
    }
}
