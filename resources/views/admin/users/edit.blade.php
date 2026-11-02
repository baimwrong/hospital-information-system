@extends('layouts.app')

@section('title', 'Edit Staff Account - ' . $user->name)
@section('page_title', 'Hospital Staff Administration - Edit Account')

@section('content')
<div class="card border-0 shadow-sm" style="max-width: 760px; margin: 0 auto;">
    <div class="card-header bg-white py-3 d-flex align-items-center justify-content-between">
        <h5 class="mb-0 fw-bold"><i class="bi bi-pencil-square text-primary me-2"></i>Update Staff: {{ $user->name }}</h5>
        <span class="badge bg-light text-dark border text-capitalize">{{ $user->role }}</span>
    </div>
    <div class="card-body p-4">
        <form action="{{ route('admin.users.update', $user) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="row g-3 mb-3">
                <div class="col-md-7">
                    <label class="form-label small fw-semibold">Full Name *</label>
                    <input type="text" name="name" class="form-control" value="{{ old('name', $user->name) }}" required>
                </div>
                <div class="col-md-5">
                    <label class="form-label small fw-semibold">Hospital Role *</label>
                    <select name="role" class="form-select" required>
                        @foreach($roles as $key => $label)
                            <option value="{{ $key }}" {{ old('role', $user->role) === $key ? 'selected' : '' }}>
                                {{ $label }} ({{ ucfirst($key) }})
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="row g-3 mb-4">
                <div class="col-md-7">
                    <label class="form-label small fw-semibold">Work Email Address *</label>
                    <input type="email" name="email" class="form-control" value="{{ old('email', $user->email) }}" required>
                </div>
                <div class="col-md-5">
                    <label class="form-label small fw-semibold">Contact Phone Number</label>
                    <input type="text" name="phone" class="form-control" value="{{ old('phone', $user->phone) }}">
                </div>
            </div>

            <div class="mb-4 p-3 bg-light rounded border">
                <div class="form-check form-switch mb-0">
                    <input class="form-check-input" type="checkbox" name="is_active" id="isActiveSwitch" value="1" {{ old('is_active', $user->is_active) ? 'checked' : '' }}>
                    <label class="form-check-label fw-semibold small" for="isActiveSwitch">
                        Account Active Status
                    </label>
                    <small class="text-muted d-block" style="font-size: 0.75rem;">
                        Deactivating revokes immediate login access to all clinical modules and terminal stations.
                    </small>
                </div>
            </div>

            <div class="d-flex justify-content-end gap-2 pt-3 border-top">
                <a href="{{ route('admin.users.index') }}" class="btn btn-light border px-4">Cancel</a>
                <button type="submit" class="btn btn-primary px-5 fw-bold">Save Changes</button>
            </div>
        </form>
    </div>
</div>
@endsection
