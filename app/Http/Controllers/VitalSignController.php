<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\AuditLog;
use App\Models\Patient;
use App\Models\PatientVital;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class VitalSignController extends Controller
{
    public function index(Request $request): View
    {
        $query = PatientVital::with(['patient', 'recordedBy', 'appointment']);

        if ($level = $request->input('triage_level')) {
            $query->where('triage_level', $level);
        }

        $vitals = $query->latest('recorded_at')->paginate(20)->withQueryString();

        return view('vitals.index', compact('vitals'));
    }

    public function create(Request $request): View
    {
        $patient = null;
        if ($patientId = $request->input('patient_id')) {
            $patient = Patient::findOrFail($patientId);
        }

        $appointment = null;
        if ($aptId = $request->input('appointment_id')) {
            $appointment = Appointment::find($aptId);
            if ($appointment && ! $patient) {
                $patient = $appointment->patient;
            }
        }

        $patients = Patient::where('status', 'active')->orderBy('first_name')->get();

        return view('vitals.create', compact('patient', 'appointment', 'patients'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'patient_id' => 'required|exists:patients,id',
            'appointment_id' => 'nullable|exists:appointments,id',
            'temperature' => 'nullable|numeric|between:30,45',
            'blood_pressure_systolic' => 'nullable|integer|between:50,260',
            'blood_pressure_diastolic' => 'nullable|integer|between:30,160',
            'heart_rate' => 'nullable|integer|between:30,220',
            'respiratory_rate' => 'nullable|integer|between:5,60',
            'oxygen_saturation' => 'nullable|integer|between:50,100',
            'weight' => 'nullable|numeric|between:1,300',
            'height' => 'nullable|numeric|between:30,250',
            'triage_level' => 'required|in:resuscitation,emergency,urgent,semi_urgent,non_urgent',
            'clinical_notes' => 'nullable|string',
        ]);

        $validated['recorded_by_id'] = Auth::id();
        $validated['recorded_at'] = now();
        $validated['bmi'] = PatientVital::computeBMI($validated['weight'] ?? null, $validated['height'] ?? null);

        $vital = PatientVital::create($validated);

        // If associated with appointment, update status to checked_in if scheduled
        if (! empty($validated['appointment_id'])) {
            $appointment = Appointment::find($validated['appointment_id']);
            if ($appointment && $appointment->status === 'scheduled') {
                $appointment->update(['status' => 'checked_in']);
            }
        }

        AuditLog::log('CREATE', 'Triage', "Recorded vital signs for patient #{$vital->patient_id} (Triage: {$vital->triage_level})", (string) $vital->id);

        return redirect()->route('patients.show', $vital->patient_id)
            ->with('success', 'Patient vital signs recorded and triage classification assigned.');
    }
}
