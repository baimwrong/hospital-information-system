<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\AuditLog;
use App\Models\Department;
use App\Models\Doctor;
use App\Models\Patient;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AppointmentController extends Controller
{
    public function index(Request $request): View
    {
        $query = Appointment::with(['patient', 'doctor.user', 'department']);

        if ($date = $request->input('date')) {
            $query->whereDate('appointment_date', $date);
        }

        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        if ($doctor = $request->input('doctor_id')) {
            $query->where('doctor_id', $doctor);
        }

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('appointment_number', 'like', "%{$search}%")
                    ->orWhereHas('patient', function ($pq) use ($search) {
                        $pq->where('first_name', 'like', "%{$search}%")
                            ->orWhere('last_name', 'like', "%{$search}%")
                            ->orWhere('mrn', 'like', "%{$search}%");
                    });
            });
        }

        $appointments = $query->orderBy('appointment_date', 'desc')
            ->orderBy('appointment_time', 'asc')
            ->paginate(15)
            ->withQueryString();

        $doctors = Doctor::with('user')->where('status', 'active')->get();

        return view('appointments.index', compact('appointments', 'doctors'));
    }

    public function create(Request $request): View
    {
        $selectedPatient = null;
        if ($patientId = $request->input('patient_id')) {
            $selectedPatient = Patient::find($patientId);
        }

        $patients = Patient::where('status', 'active')->orderBy('first_name')->get();
        $doctors = Doctor::with(['user', 'department'])->where('status', 'active')->get();
        $departments = Department::where('is_active', true)->get();
        $nextNumber = Appointment::generateNumber();

        return view('appointments.create', compact('patients', 'doctors', 'departments', 'selectedPatient', 'nextNumber'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'patient_id' => 'required|exists:patients,id',
            'doctor_id' => 'required|exists:doctors,id',
            'department_id' => 'nullable|exists:departments,id',
            'appointment_date' => 'required|date|after_or_equal:today',
            'appointment_time' => 'required|date_format:H:i',
            'priority' => 'required|in:routine,urgent,emergency',
            'reason_for_visit' => 'required|string',
            'notes' => 'nullable|string',
        ]);

        $doctor = Doctor::with('user')->findOrFail($validated['doctor_id']);

        // Verify physician availability days
        $dayOfWeek = Carbon::parse($validated['appointment_date'])->format('l');
        $availableDays = $doctor->available_days ?? [];
        if (! empty($availableDays) && is_array($availableDays)) {
            $isAvailable = false;
            foreach ($availableDays as $d) {
                if (strcasecmp(trim($d), $dayOfWeek) === 0) {
                    $isAvailable = true;
                    break;
                }
            }
            if (! $isAvailable) {
                return back()->withInput()->withErrors([
                    'appointment_date' => "Dr. {$doctor->user->name} is not available on {$dayOfWeek}s. Scheduled working days: ".implode(', ', $availableDays).'.',
                ]);
            }
        }

        // Prevent double booking / scheduling conflict
        $conflict = Appointment::where('doctor_id', $doctor->id)
            ->whereDate('appointment_date', $validated['appointment_date'])
            ->where('appointment_time', $validated['appointment_time'])
            ->whereNotIn('status', ['cancelled', 'no_show'])
            ->exists();

        if ($conflict) {
            return back()->withInput()->withErrors([
                'appointment_time' => "Dr. {$doctor->user->name} already has a consultation scheduled at {$validated['appointment_time']} on {$validated['appointment_date']}.",
            ]);
        }

        $validated['department_id'] = $validated['department_id'] ?? $doctor->department_id;
        $validated['appointment_number'] = Appointment::generateNumber();
        $validated['status'] = 'scheduled';

        $appointment = Appointment::create($validated);

        AuditLog::log('CREATE', 'Appointments', "Scheduled appointment {$appointment->appointment_number} for patient #{$appointment->patient_id}", (string) $appointment->id);

        return redirect()->route('appointments.show', $appointment)
            ->with('success', "Appointment {$appointment->appointment_number} scheduled successfully.");
    }

    public function show(Appointment $appointment): View
    {
        $appointment->load(['patient.vitals', 'doctor.user', 'department', 'vitals', 'medicalRecord']);

        return view('appointments.show', compact('appointment'));
    }

    public function updateStatus(Request $request, Appointment $appointment): RedirectResponse
    {
        $request->validate([
            'status' => 'required|in:scheduled,checked_in,in_consultation,completed,cancelled,no_show',
        ]);

        $oldStatus = $appointment->status;
        $appointment->status = $request->input('status');
        $appointment->save();

        AuditLog::log('UPDATE', 'Appointments', "Changed appointment {$appointment->appointment_number} status from {$oldStatus} to {$appointment->status}", (string) $appointment->id);

        return back()->with('success', "Appointment status updated to {$appointment->status}.");
    }
}
