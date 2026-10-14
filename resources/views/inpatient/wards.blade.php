@extends('layouts.app')

@section('title', 'Wards & Bed Visual Layout')
@section('page_title', 'Inpatient Department (IPD) - Ward & Bed Grid')

@section('content')
<div class="mb-4 d-flex align-items-center justify-content-between">
    <div>
        <a href="{{ route('inpatient.index') }}" class="btn btn-sm btn-outline-secondary me-2">
            <i class="bi bi-arrow-left me-1"></i> Admissions List
        </a>
        <span class="small text-muted">
            <span class="badge bg-success me-1">&bull;</span> Available
            <span class="badge bg-danger ms-2 me-1">&bull;</span> Occupied
            <span class="badge bg-warning text-dark ms-2 me-1">&bull;</span> Cleaning / Turnaround
        </span>
    </div>
    <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#createWardModal">
        <i class="bi bi-plus-lg me-1"></i> Add New Ward
    </button>
</div>

<!-- Wards Cards -->
<div class="row g-4">
    @foreach($wards as $ward)
        <div class="col-xl-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white py-3 d-flex align-items-center justify-content-between">
                    <div>
                        <h5 class="fw-bold mb-0 text-dark">{{ $ward->name }}</h5>
                        <small class="text-muted">{{ ucfirst($ward->type) }} Ward &bull; {{ $ward->floor ?? 'Main Hospital' }} &bull; {{ \App\Models\Setting::get('currency_symbol', '$') }}{{ number_format($ward->daily_rate, 2) }}/day</small>
                    </div>
                    <div>
                        <span class="badge bg-light text-dark border">
                            {{ $ward->availableBedsCount() }} of {{ $ward->beds->count() }} Available
                        </span>
                    </div>
                </div>
                <div class="card-body p-4">
                    <div class="row g-3">
                        @foreach($ward->beds as $bed)
                            <div class="col-sm-6 col-md-4 col-lg-3">
                                <div class="card text-center p-2 border-2 {{ $bed->status === 'available' ? 'border-success bg-success bg-opacity-10' : ($bed->status === 'occupied' ? 'border-danger bg-danger bg-opacity-10' : 'border-warning bg-warning bg-opacity-10') }}">
                                    <div class="d-flex align-items-center justify-content-between mb-1">
                                        <i class="bi bi-hospital fs-5 {{ $bed->status === 'available' ? 'text-success' : ($bed->status === 'occupied' ? 'text-danger' : 'text-warning') }}"></i>
                                        <span class="badge bg-white text-dark border small">{{ $bed->bed_number }}</span>
                                    </div>
                                    <div class="small fw-bold text-truncate" title="{{ $bed->currentAdmission->patient->full_name ?? 'Vacant' }}">
                                        {{ $bed->currentAdmission->patient->full_name ?? 'Vacant Bed' }}
                                    </div>
                                    <div class="text-capitalize small text-muted" style="font-size: 0.7rem;">
                                        {{ $bed->status }}
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    @endforeach
</div>

<!-- Modal: Create Ward -->
<div class="modal fade" id="createWardModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form action="{{ route('inpatient.wards.store') }}" method="POST">
            @csrf
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold">Add Hospital Ward</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Ward Name *</label>
                        <input type="text" name="name" class="form-control" placeholder="e.g. Surgical Ward C" required>
                    </div>
                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label class="form-label small fw-semibold">Ward Classification *</label>
                            <select name="type" class="form-select" required>
                                <option value="general">General</option>
                                <option value="icu">ICU</option>
                                <option value="maternity">Maternity</option>
                                <option value="pediatric">Pediatric</option>
                                <option value="surgical">Surgical</option>
                                <option value="vip">VIP</option>
                                <option value="isolation">Isolation</option>
                            </select>
                        </div>
                        <div class="col-6">
                            <label class="form-label small fw-semibold">Location / Floor</label>
                            <input type="text" name="floor" class="form-control" placeholder="3rd Floor - West">
                        </div>
                    </div>
                    <div class="row g-2 mb-3">
                        <div class="col-4">
                            <label class="form-label small fw-semibold">Bed Capacity *</label>
                            <input type="number" name="capacity" class="form-control" value="6" min="1" max="50" required>
                        </div>
                        <div class="col-4">
                            <label class="form-label small fw-semibold">Gender *</label>
                            <select name="gender_preference" class="form-select" required>
                                <option value="mixed">Mixed</option>
                                <option value="male">Male Only</option>
                                <option value="female">Female Only</option>
                            </select>
                        </div>
                        <div class="col-4">
                            <label class="form-label small fw-semibold">Daily Rate *</label>
                            <input type="number" step="0.01" name="daily_rate" class="form-control" value="100.00" required>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Create Ward & Generate Beds</button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection
