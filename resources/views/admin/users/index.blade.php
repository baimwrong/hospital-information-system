@extends('layouts.app')

@section('title', 'Hospital Staff & User Accounts')
@section('page_title', 'Hospital Staff Directory & Access Control')

@section('content')
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body p-3">
        <form action="{{ route('admin.users.index') }}" method="GET" class="row g-2 align-items-center">
            <div class="col-md-4">
                <input type="text" name="search" class="form-control" placeholder="Search staff by name, email, phone..." value="{{ request('search') }}">
            </div>
            <div class="col-md-3">
                <select name="role" class="form-select">
                    <option value="">All Hospital Roles</option>
                    @foreach($roles as $r)
                        <option value="{{ $r }}" {{ request('role') === $r ? 'selected' : '' }}>
                            {{ ucfirst($r) }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <select name="status" class="form-select">
                    <option value="">All Statuses</option>
                    <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active Only</option>
                    <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Deactivated</option>
                </select>
            </div>
            <div class="col-md-1">
                <button type="submit" class="btn btn-primary w-100">Filter</button>
            </div>
            <div class="col-md-2 text-end">
                <a href="{{ route('admin.users.create') }}" class="btn btn-primary w-100">
                    <i class="bi bi-person-plus me-1"></i> Add Staff
                </a>
            </div>
        </form>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-header bg-white py-3 d-flex align-items-center justify-content-between">
        <h5 class="mb-0 fw-bold">Active Medical Staff & Administrative Personnel</h5>
        <span class="badge bg-light text-dark border">{{ $users->total() }} Total Accounts</span>
    </div>
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>Staff Name & Email</th>
                    <th>Role</th>
                    <th>Phone</th>
                    <th>Status</th>
                    <th>Password Status</th>
                    <th>Joined</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($users as $user)
                    <tr>
                        <td>
                            <div class="d-flex align-items-center gap-2">
                                <div class="rounded-circle bg-primary bg-opacity-10 text-primary d-flex align-items-center justify-content-center fw-bold" style="width: 36px; height: 36px;">
                                    {{ strtoupper(substr($user->name, 0, 1)) }}
                                </div>
                                <div>
                                    <div class="fw-bold text-dark">{{ $user->name }}</div>
                                    <small class="text-muted">{{ $user->email }}</small>
                                </div>
                            </div>
                        </td>
                        <td>
                            @php
                                $badgeClass = match($user->role) {
                                    'admin' => 'bg-danger',
                                    'doctor' => 'bg-primary',
                                    'nurse' => 'bg-info text-dark',
                                    'pharmacist' => 'bg-success',
                                    'laboratorist' => 'bg-warning text-dark',
                                    'accountant' => 'bg-secondary',
                                    default => 'bg-light text-dark border',
                                };
                            @endphp
                            <span class="badge {{ $badgeClass }} text-capitalize px-2 py-1">
                                {{ $user->role }}
                            </span>
                        </td>
                        <td>{{ $user->phone ?? '—' }}</td>
                        <td>
                            @if($user->is_active)
                                <span class="badge bg-success bg-opacity-10 text-success border border-success">Active</span>
                            @else
                                <span class="badge bg-danger bg-opacity-10 text-danger border border-danger">Deactivated</span>
                            @endif
                        </td>
                        <td>
                            @if($user->must_change_password)
                                <span class="badge bg-warning text-dark" title="User must change password on next login">Reset Required</span>
                            @else
                                <span class="text-muted small"><i class="bi bi-shield-check text-success me-1"></i>Secure</span>
                            @endif
                        </td>
                        <td class="small text-muted">{{ $user->created_at?->format('M d, Y') ?? '—' }}</td>
                        <td class="text-end">
                            <div class="btn-group btn-group-sm">
                                <a href="{{ route('admin.users.edit', $user) }}" class="btn btn-outline-secondary" title="Edit Staff Record">
                                    <i class="bi bi-pencil"></i>
                                </a>

                                <button type="button" class="btn btn-outline-warning" data-bs-toggle="modal" data-bs-target="#resetModal-{{ $user->id }}" title="Reset Password">
                                    <i class="bi bi-key"></i>
                                </button>

                                @if($user->id !== auth()->id())
                                    <form action="{{ route('admin.users.toggle-status', $user) }}" method="POST" class="d-inline" onsubmit="return confirm('Change status for {{ $user->name }}?');">
                                        @csrf
                                        <button type="submit" class="btn btn-outline-{{ $user->is_active ? 'danger' : 'success' }}" title="{{ $user->is_active ? 'Deactivate Account' : 'Activate Account' }}">
                                            <i class="bi bi-{{ $user->is_active ? 'person-x' : 'person-check' }}"></i>
                                        </button>
                                    </form>
                                @endif
                            </div>

                            <!-- Modal: Reset Password -->
                            <div class="modal fade text-start" id="resetModal-{{ $user->id }}" tabindex="-1" aria-hidden="true">
                                <div class="modal-dialog modal-sm">
                                    <form action="{{ route('admin.users.reset-password', $user) }}" method="POST">
                                        @csrf
                                        <div class="modal-content">
                                            <div class="modal-header py-2">
                                                <h6 class="modal-title fw-bold">Reset Password: {{ $user->name }}</h6>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                            </div>
                                            <div class="modal-body">
                                                <label class="form-label small fw-semibold">New Temporary Password *</label>
                                                <input type="text" name="new_password" class="form-control form-control-sm" value="Staff@{{ rand(1000, 9999) }}" required minlength="8">
                                                <small class="text-muted d-block mt-1" style="font-size: 0.72rem;">User will be prompted to change this on next login.</small>
                                            </div>
                                            <div class="modal-footer py-1">
                                                <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                <button type="submit" class="btn btn-sm btn-warning fw-bold">Reset</button>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center py-5 text-muted">No staff accounts found matching query.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($users->hasPages())
        <div class="card-footer bg-white py-3">
            {{ $users->links() }}
        </div>
    @endif
</div>
@endsection
