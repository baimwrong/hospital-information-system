@extends('layouts.app')

@section('title', 'Staff Profile & Security')
@section('page_title', 'Staff Account Settings & Password Security')

@section('content')
<div class="row g-4" style="max-width: 960px; margin: 0 auto;">
    @if($user->must_change_password)
        <div class="col-12">
            <div class="alert alert-warning border-warning d-flex align-items-center mb-0">
                <i class="bi bi-shield-exclamation fs-3 me-3 text-warning"></i>
                <div>
                    <strong class="d-block">Security Required: Update Temporary Password</strong>
                    <span class="small">Your account is using a system-generated or reset password. To protect sensitive hospital health information, please update your password below.</span>
                </div>
            </div>
        </div>
    @endif

    <!-- Profile Info Card -->
    <div class="col-md-5">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white py-3">
                <h6 class="mb-0 fw-bold"><i class="bi bi-person-badge text-primary me-2"></i>Staff Credentials</h6>
            </div>
            <div class="card-body p-4 text-center">
                <div class="rounded-circle bg-primary bg-opacity-10 text-primary d-inline-flex align-items-center justify-content-center fw-bold fs-2 mb-3" style="width: 80px; height: 80px;">
                    {{ strtoupper(substr($user->name, 0, 1)) }}
                </div>
                <h5 class="fw-bold mb-1">{{ $user->name }}</h5>
                <span class="badge bg-primary text-capitalize px-3 py-1 mb-3">{{ $user->role }}</span>
                <div class="text-muted small mb-3">{{ $user->email }}</div>

                <div class="p-3 bg-light rounded text-start border small">
                    <div class="mb-2"><strong>Status:</strong> <span class="badge bg-success">Active Personnel</span></div>
                    <div class="mb-2"><strong>Account ID:</strong> #STAFF-{{ sprintf('%04d', $user->id) }}</div>
                    <div><strong>Member Since:</strong> {{ $user->created_at?->format('M d, Y') ?? 'N/A' }}</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Profile & Change Password -->
    <div class="col-md-7">
        <!-- Update Contact Info -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white py-3">
                <h6 class="mb-0 fw-bold"><i class="bi bi-pencil text-secondary me-2"></i>Personal Contact Information</h6>
            </div>
            <div class="card-body p-4">
                <form action="{{ route('profile.update') }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Display / Legal Name *</label>
                        <input type="text" name="name" class="form-control" value="{{ old('name', $user->name) }}" required>
                    </div>

                    <div class="row g-2 mb-3">
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Email Address (Read-only)</label>
                            <input type="email" class="form-control bg-light" value="{{ $user->email }}" disabled>
                            <small class="text-muted" style="font-size: 0.72rem;">Email changes require Administrator authorization.</small>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Direct Phone / Extension</label>
                            <input type="text" name="phone" class="form-control" value="{{ old('phone', $user->phone) }}" placeholder="+1 (555) 019-4820">
                        </div>
                    </div>

                    <div class="text-end">
                        <button type="submit" class="btn btn-sm btn-primary px-4 fw-bold">Update Details</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Change Password Card -->
        <div class="card border-0 shadow-sm" id="password-section">
            <div class="card-header bg-white py-3">
                <h6 class="mb-0 fw-bold"><i class="bi bi-key text-warning me-2"></i>Security & Password Update</h6>
            </div>
            <div class="card-body p-4">
                <form action="{{ route('profile.password') }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Current Password *</label>
                        <input type="password" name="current_password" class="form-control" placeholder="••••••••" required>
                    </div>

                    <div class="row g-2 mb-3">
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">New Secure Password *</label>
                            <input type="password" name="password" class="form-control" placeholder="Minimum 8 characters" required minlength="8">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Confirm New Password *</label>
                            <input type="password" name="password_confirmation" class="form-control" placeholder="Repeat new password" required minlength="8">
                        </div>
                    </div>

                    <div class="text-end">
                        <button type="submit" class="btn btn-sm btn-warning px-4 fw-bold">Update Password</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
