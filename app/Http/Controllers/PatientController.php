<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\MedicalDocument;
use App\Models\Patient;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

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
            'mrn' => 'nullable|string|max:50',
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

        if (empty($validated['mrn']) || Patient::where('mrn', $validated['mrn'])->exists()) {
            $validated['mrn'] = Patient::generateMRN();
        }

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
        AuditLog::log('VIEW', 'Patients', "Accessed patient medical record: {$patient->full_name} ({$patient->mrn})", (string) $patient->id);

        $patient->load([
            'vitals.recordedBy',
            'appointments.doctor.user',
            'medicalRecords.doctor.user',
            'prescriptions.items.medicine',
            'labRequests.results',
            'admissions.bed.ward',
            'invoices.items',
            'documents.uploadedBy',
        ]);

        return view('patients.show', compact('patient'));
    }

    public function uploadDocument(Request $request, Patient $patient): RedirectResponse
    {
        $validated = $request->validate([
            'title' => 'required|string|max:150',
            'document_type' => 'required|in:referral_letter,lab_report,imaging_scan,pathology,discharge_summary,general',
            'medical_record_id' => 'nullable|exists:medical_records,id',
            'notes' => 'nullable|string',
            'file' => 'required|file|max:20480|mimes:pdf,jpg,jpeg,png,webp,txt,doc,docx',
        ]);

        $file = $request->file('file');
        $fileName = $file->getClientOriginalName();
        $storedPath = $file->store("medical_documents/{$patient->id}", 'local');

        $doc = MedicalDocument::create([
            'patient_id' => $patient->id,
            'medical_record_id' => $validated['medical_record_id'] ?? null,
            'title' => $validated['title'],
            'document_type' => $validated['document_type'],
            'file_path' => $storedPath,
            'file_name' => $fileName,
            'file_size' => $file->getSize(),
            'mime_type' => $file->getClientMimeType() ?: 'application/octet-stream',
            'uploaded_by_id' => Auth::id(),
            'notes' => $validated['notes'] ?? null,
        ]);

        AuditLog::log('CREATE', 'Documents', "Uploaded document '{$doc->title}' ({$doc->document_type}) for patient {$patient->mrn}", (string) $doc->id);

        return back()->with('success', "Medical document '{$doc->title}' attached successfully to patient record.");
    }

    public function downloadDocument(Patient $patient, MedicalDocument $document): BinaryFileResponse|RedirectResponse
    {
        if ($document->patient_id !== $patient->id) {
            abort(404, 'Document does not belong to this patient.');
        }

        if (! Storage::disk('local')->exists($document->file_path)) {
            return back()->withErrors(['error' => 'File not found on storage disk.']);
        }

        AuditLog::log('VIEW', 'Documents', "Downloaded document '{$document->title}' for patient {$patient->mrn}", (string) $document->id);

        return response()->download(
            Storage::disk('local')->path($document->file_path),
            $document->file_name
        );
    }

    public function destroyDocument(Patient $patient, MedicalDocument $document): RedirectResponse
    {
        if ($document->patient_id !== $patient->id) {
            abort(404);
        }

        $title = $document->title;
        $document->delete();

        AuditLog::log('DELETE', 'Documents', "Archived medical document '{$title}' for patient {$patient->mrn}", (string) $document->id);

        return back()->with('success', "Document '{$title}' removed.");
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

        $patient->update(['status' => 'archived']);
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
