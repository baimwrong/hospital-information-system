<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class UserController extends Controller
{
    public function index(Request $request): View
    {
        $query = User::query();

        if ($role = $request->input('role')) {
            $query->where('role', $role);
        }

        if ($status = $request->input('status')) {
            if ($status === 'active') {
                $query->where('is_active', true);
            } elseif ($status === 'inactive') {
                $query->where('is_active', false);
            }
        }

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        $users = $query->orderBy('name')->paginate(15)->withQueryString();
        $roles = ['admin', 'doctor', 'nurse', 'pharmacist', 'laboratorist', 'receptionist', 'accountant'];

        return view('admin.users.index', compact('users', 'roles'));
    }

    public function create(): View
    {
        $roles = [
            'admin' => 'Hospital Administrator',
            'nurse' => 'Staff Nurse / Triage Specialist',
            'receptionist' => 'Front Desk / Patient Receptionist',
            'pharmacist' => 'Clinical Pharmacist',
            'laboratorist' => 'Laboratory Technologist',
            'accountant' => 'Billing Specialist / Accountant',
            'doctor' => 'Physician / Medical Specialist',
        ];

        return view('admin.users.create', compact('roles'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:150',
            'email' => 'required|email|max:150|unique:users,email',
            'phone' => 'nullable|string|max:30',
            'role' => 'required|in:admin,doctor,nurse,pharmacist,laboratorist,receptionist,accountant',
            'password' => 'required|string|min:8',
            'is_active' => 'boolean',
        ]);

        $validated['password'] = Hash::make($validated['password']);
        $validated['is_active'] = $request->boolean('is_active', true);
        $validated['must_change_password'] = true;

        $user = User::create($validated);

        AuditLog::log('CREATE', 'Users', "Created staff account for {$user->name} ({$user->email}) with role: {$user->role}", (string) $user->id);

        return redirect()->route('admin.users.index')
            ->with('success', "Staff account for {$user->name} created successfully.");
    }

    public function edit(User $user): View
    {
        $roles = [
            'admin' => 'Hospital Administrator',
            'nurse' => 'Staff Nurse / Triage Specialist',
            'receptionist' => 'Front Desk / Patient Receptionist',
            'pharmacist' => 'Clinical Pharmacist',
            'laboratorist' => 'Laboratory Technologist',
            'accountant' => 'Billing Specialist / Accountant',
            'doctor' => 'Physician / Medical Specialist',
        ];

        return view('admin.users.edit', compact('user', 'roles'));
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:150',
            'email' => "required|email|max:150|unique:users,email,{$user->id}",
            'phone' => 'nullable|string|max:30',
            'role' => 'required|in:admin,doctor,nurse,pharmacist,laboratorist,receptionist,accountant',
            'is_active' => 'boolean',
        ]);

        $validated['is_active'] = $request->boolean('is_active');

        // Prevent admin from deactivating themselves
        if ($user->id === Auth::id() && ! $validated['is_active']) {
            return back()->withErrors(['error' => 'You cannot deactivate your own administrative account.']);
        }

        $user->update($validated);

        AuditLog::log('UPDATE', 'Users', "Updated staff account for {$user->name} (Role: {$user->role}, Active: ".($user->is_active ? 'Yes' : 'No').')', (string) $user->id);

        return redirect()->route('admin.users.index')
            ->with('success', "Staff account for {$user->name} updated successfully.");
    }

    public function toggleStatus(User $user): RedirectResponse
    {
        if ($user->id === Auth::id()) {
            return redirect()->back(fallback: route('admin.users.index'))->withErrors(['error' => 'You cannot deactivate your own account.']);
        }

        $user->is_active = ! $user->is_active;
        $user->save();

        $statusStr = $user->is_active ? 'activated' : 'deactivated';
        AuditLog::log('UPDATE', 'Users', "Staff account {$user->name} was {$statusStr}", (string) $user->id);

        return redirect()->back(fallback: route('admin.users.index'))->with('success', "Staff account {$user->name} has been {$statusStr}.");
    }

    public function resetPassword(Request $request, User $user): RedirectResponse
    {
        $validated = $request->validate([
            'new_password' => 'required|string|min:8',
        ]);

        $user->password = Hash::make($validated['new_password']);
        $user->must_change_password = true;
        $user->save();

        AuditLog::log('UPDATE', 'Users', "Administrative password reset for {$user->name}", (string) $user->id);

        return redirect()->back(fallback: route('admin.users.index'))->with('success', "Password for {$user->name} has been reset. They will be prompted to change it on login.");
    }
}
