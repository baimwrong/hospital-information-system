@extends('layouts.app')

@section('title', 'Physicians Directory')
@section('page_title', 'Medical Staff & Specialists Roster')

@section('content')
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body p-3">
        <form action="{{ route('doctors.index') }}" method="GET" class="row g-2 align-items-center">
            <div class="col-md-6">
                <input type="text" name="search" class="form-control" placeholder="Search doctor by Name, Specialty, License Number..." value="{{ request('search') }}">
            </div>
            <div class="col-md-4">
                <select name="department_id" class="form-select">
                    <option value="">All Departments</option>
                    @foreach($departments as $dept)
                        <option value="{{ $dept->id }}" {{ request('department_id') == $dept->id ? 'selected' : '' }}>
                            {{ $dept->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary w-100">Filter</button>
            </div>
        </form>
    </div>
</div>

<div class="d-flex align-items-center justify-content-between mb-3">
    <h5 class="fw-bold mb-0">Active Medical Specialists</h5>
    @if(Auth::user()->isAdmin())
        <a href="{{ route('doctors.create') }}" class="btn btn-primary btn-sm">
            <i class="bi bi-person-plus me-1"></i> Register Physician
        </a>
    @endif
</div>

<div class="row g-4">
    @forelse($doctors as $doc)
        <div class="col-md-6 col-xl-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center gap-3 mb-3">
                        <div class="rounded-circle bg-primary bg-opacity-10 text-primary d-flex align-items-center justify-content-center fw-bold fs-4" style="width: 52px; height: 52px;">
                            <i class="bi bi-person-badge"></i>
                        </div>
                        <div>
                            <h5 class="fw-bold text-dark mb-0">{{ $doc->user->name ?? 'Physician' }}</h5>
                            <span class="text-primary fw-semibold small">{{ $doc->specialization }}</span>
                        </div>
                    </div>

                    <div class="mb-3 small">
                        <div class="mb-1"><strong class="text-muted">Department:</strong> {{ $doc->department->name ?? 'Clinical' }}</div>
                        <div class="mb-1"><strong class="text-muted">License:</strong> {{ $doc->license_number }}</div>
                        <div class="mb-1"><strong class="text-muted">Clinic:</strong> {{ $doc->room_number ?? 'Consulting Room' }}</div>
                        <div class="mb-1"><strong class="text-muted">Fee:</strong> {{ \App\Models\Setting::get('currency_symbol', '$') }}{{ number_format($doc->consultation_fee, 2) }}</div>
                    </div>

                    @if(!empty($doc->available_days) && is_array($doc->available_days))
                        <div class="d-flex flex-wrap gap-1 mb-3">
                            @foreach($doc->available_days as $day)
                                <span class="badge bg-light text-secondary border" style="font-size: 0.65rem;">{{ substr($day, 0, 3) }}</span>
                            @endforeach
                        </div>
                    @endif

                    <div class="d-flex justify-content-between align-items-center pt-2 border-top">
                        <span class="badge bg-{{ $doc->status === 'active' ? 'success' : 'secondary' }} text-capitalize">
                            {{ $doc->status }}
                        </span>
                        <div class="btn-group btn-group-sm">
                            <a href="{{ route('doctors.show', $doc) }}" class="btn btn-outline-primary">Profile</a>
                            @if(Auth::user()->isAdmin())
                                <a href="{{ route('doctors.edit', $doc) }}" class="btn btn-outline-secondary">Edit</a>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @empty
        <div class="col-12">
            <div class="card border-0 shadow-sm p-5 text-center text-muted">
                No physicians found matching search criteria.
            </div>
        </div>
    @endforelse
</div>

@if($doctors->hasPages())
    <div class="mt-4">
        {{ $doctors->links() }}
    </div>
@endif
@endsection
