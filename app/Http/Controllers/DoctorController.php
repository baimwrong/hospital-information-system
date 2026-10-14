<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Department;
use App\Models\Doctor;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class DoctorController extends Controller
{
    public function index(Request $request): View
    {
        $query = Doctor::with(['user', 'department']);

        if ($search = $request->input('search')) {
            $query->whereHas('user', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            })->orWhere('specialization', 'like', "%{$search}%")
                ->orWhere('license_number', 'like', "%{$search}%");
        }

        if ($dept = $request->input('department_id')) {
            $query->where('department_id', $dept);
        }

        $doctors = $query->paginate(12)->withQueryString();
        $departments = Department::where('is_active', true)->orderBy('name')->get();

        return view('doctors.index', compact('doctors', 'departments'));
    }

    public function create(): View
    {
        $departments = Department::where('is_active', true)->orderBy('name')->get();

        return view('doctors.create', compact('departments'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:150',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8',
            'phone' => 'nullable|string|max:30',
            'department_id' => 'required|exists:departments,id',
            'specialization' => 'required|string|max:150',
            'license_number' => 'required|string|max:100|unique:doctors,license_number',
            'qualification' => 'nullable|string|max:200',
            'consultation_fee' => 'required|numeric|min:0',
            'room_number' => 'nullable|string|max:50',
            'biography' => 'nullable|string',
            'available_days' => 'nullable|array',
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => 'doctor',
            'phone' => $validated['phone'] ?? null,
            'is_active' => true,
        ]);

        $doctor = Doctor::create([
            'user_id' => $user->id,
            'department_id' => $validated['department_id'],
            'specialization' => $validated['specialization'],
            'license_number' => $validated['license_number'],
            'qualification' => $validated['qualification'] ?? null,
            'consultation_fee' => $validated['consultation_fee'],
            'room_number' => $validated['room_number'] ?? null,
            'biography' => $validated['biography'] ?? null,
            'available_days' => $validated['available_days'] ?? ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'],
            'status' => 'active',
        ]);

        AuditLog::log('CREATE', 'Doctors', "Added physician profile for {$user->name} ({$doctor->specialization})", (string) $doctor->id);

        return redirect()->route('doctors.show', $doctor)
            ->with('success', "Doctor {$user->name} profile created successfully.");
    }

    public function show(Doctor $doctor): View
    {
        $doctor->load([
            'user',
            'department',
            'appointments' => fn ($q) => $q->with('patient')->latest()->limit(10),
            'medicalRecords' => fn ($q) => $q->with('patient')->latest()->limit(10),
        ]);

        return view('doctors.show', compact('doctor'));
    }

    public function edit(Doctor $doctor): View
    {
        $doctor->load(['user', 'department']);
        $departments = Department::where('is_active', true)->orderBy('name')->get();

        return view('doctors.edit', compact('doctor', 'departments'));
    }

    public function update(Request $request, Doctor $doctor): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:150',
            'email' => "required|email|unique:users,email,{$doctor->user_id}",
            'phone' => 'nullable|string|max:30',
            'department_id' => 'required|exists:departments,id',
            'specialization' => 'required|string|max:150',
            'license_number' => "required|string|max:100|unique:doctors,license_number,{$doctor->id}",
            'qualification' => 'nullable|string|max:200',
            'consultation_fee' => 'required|numeric|min:0',
            'room_number' => 'nullable|string|max:50',
            'biography' => 'nullable|string',
            'available_days' => 'nullable|array',
            'status' => 'required|in:active,on_leave,inactive',
        ]);

        $doctor->user->update([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?? null,
        ]);

        $doctor->update([
            'department_id' => $validated['department_id'],
            'specialization' => $validated['specialization'],
            'license_number' => $validated['license_number'],
            'qualification' => $validated['qualification'] ?? null,
            'consultation_fee' => $validated['consultation_fee'],
            'room_number' => $validated['room_number'] ?? null,
            'biography' => $validated['biography'] ?? null,
            'available_days' => $validated['available_days'] ?? $doctor->available_days,
            'status' => $validated['status'],
        ]);

        AuditLog::log('UPDATE', 'Doctors', "Updated doctor profile for {$doctor->user->name}", (string) $doctor->id);

        return redirect()->route('doctors.show', $doctor)
            ->with('success', 'Doctor profile updated successfully.');
    }
}
