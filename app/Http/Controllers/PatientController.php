<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Patient;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PatientController extends Controller
{
    public function index(Request $request): View
    {
        $query = Patient::query();

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('mrn', 'like', "%{$search}%")
                    ->orWhere('first_name', 'like', "%{$search}%")
                    ->orWhere('last_name', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('national_id', 'like', "%{$search}%");
            });
        }

        if ($gender = $request->input('gender')) {
            $query->where('gender', $gender);
        }

        if ($blood = $request->input('blood_group')) {
            $query->where('blood_group', $blood);
        }

        $patients = $query->latest()->paginate(15)->withQueryString();

        return view('patients.index', compact('patients'));
    }

    public function create(): View
    {
        $nextMrn = Patient::generateMRN();

        return view('patients.create', compact('nextMrn'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'mrn' => 'required|string|unique:patients,mrn',
            'first_name' => 'required|string|max:100',
            'last_name' => 'required|string|max:100',
            'email' => 'nullable|email|max:150',
            'phone' => 'required|string|max:30',
            'date_of_birth' => 'required|date',
            'gender' => 'required|in:male,female,other',
            'blood_group' => 'nullable|string|max:10',
            'genotype' => 'nullable|string|max:10',
            'national_id' => 'nullable|string|max:50',
            'address' => 'nullable|string',
            'emergency_contact_name' => 'nullable|string|max:100',
            'emergency_contact_phone' => 'nullable|string|max:30',
            'emergency_contact_relation' => 'nullable|string|max:50',
            'allergies' => 'nullable|string',
            'chronic_diseases' => 'nullable|string',
            'insurance_provider' => 'nullable|string|max:100',
            'insurance_policy_number' => 'nullable|string|max:100',
        ]);

        if (! empty($validated['allergies'])) {
            $validated['allergies'] = array_filter(array_map('trim', explode(',', $validated['allergies'])));
        } else {
            $validated['allergies'] = [];
        }

        if (! empty($validated['chronic_diseases'])) {
            $validated['chronic_diseases'] = array_filter(array_map('trim', explode(',', $validated['chronic_diseases'])));
        } else {
            $validated['chronic_diseases'] = [];
        }

        $patient = Patient::create($validated);

        AuditLog::log('CREATE', 'Patients', "Registered new patient: {$patient->full_name} ({$patient->mrn})", (string) $patient->id);

        return redirect()->route('patients.show', $patient)
            ->with('success', "Patient {$patient->full_name} registered successfully with MRN {$patient->mrn}.");
    }

    public function show(Patient $patient): View
    {
        $patient->load([
            'vitals.recordedBy',
            'appointments.doctor.user',
            'medicalRecords.doctor.user',
            'prescriptions.items.medicine',
            'labRequests.results',
            'admissions.bed.ward',
            'invoices.items',
        ]);

        return view('patients.show', compact('patient'));
    }

    public function edit(Patient $patient): View
    {
        return view('patients.edit', compact('patient'));
    }

    public function update(Request $request, Patient $patient): RedirectResponse
    {
        $validated = $request->validate([
            'first_name' => 'required|string|max:100',
            'last_name' => 'required|string|max:100',
            'email' => 'nullable|email|max:150',
            'phone' => 'required|string|max:30',
            'date_of_birth' => 'required|date',
            'gender' => 'required|in:male,female,other',
            'blood_group' => 'nullable|string|max:10',
            'genotype' => 'nullable|string|max:10',
            'national_id' => 'nullable|string|max:50',
            'address' => 'nullable|string',
            'emergency_contact_name' => 'nullable|string|max:100',
            'emergency_contact_phone' => 'nullable|string|max:30',
            'emergency_contact_relation' => 'nullable|string|max:50',
            'allergies' => 'nullable|string',
            'chronic_diseases' => 'nullable|string',
            'insurance_provider' => 'nullable|string|max:100',
            'insurance_policy_number' => 'nullable|string|max:100',
            'status' => 'required|in:active,deceased,archived',
        ]);

        if (! empty($validated['allergies'])) {
            $validated['allergies'] = array_filter(array_map('trim', explode(',', $validated['allergies'])));
        } else {
            $validated['allergies'] = [];
        }

        if (! empty($validated['chronic_diseases'])) {
            $validated['chronic_diseases'] = array_filter(array_map('trim', explode(',', $validated['chronic_diseases'])));
        } else {
            $validated['chronic_diseases'] = [];
        }

        $patient->update($validated);

        AuditLog::log('UPDATE', 'Patients', "Updated patient record: {$patient->full_name} ({$patient->mrn})", (string) $patient->id);

        return redirect()->route('patients.show', $patient)
            ->with('success', 'Patient record updated successfully.');
    }

    public function destroy(Patient $patient): RedirectResponse
    {
        $name = $patient->full_name;
        $mrn = $patient->mrn;

        $patient->delete();

        AuditLog::log('DELETE', 'Patients', "Deleted patient record {$name} ({$mrn})");

        return redirect()->route('patients.index')
            ->with('success', "Patient {$name} has been archived/removed.");
    }

    public function quickSearch(Request $request): JsonResponse
    {
        $term = $request->input('q', '');
        $patients = Patient::where('mrn', 'like', "%{$term}%")
            ->orWhere('first_name', 'like', "%{$term}%")
            ->orWhere('last_name', 'like', "%{$term}%")
            ->orWhere('phone', 'like', "%{$term}%")
            ->limit(10)
            ->get(['id', 'mrn', 'first_name', 'last_name', 'phone', 'blood_group', 'gender']);

        return response()->json($patients);
    }
}
