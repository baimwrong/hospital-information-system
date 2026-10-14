@extends('layouts.app')

@section('title', 'Lab Order - ' . $request->request_number)
@section('page_title', 'Laboratory Analysis & Findings Report')

@section('content')
<div class="mb-3 d-flex align-items-center justify-content-between">
    <a href="{{ route('laboratory.index') }}" class="btn btn-sm btn-outline-secondary">
        <i class="bi bi-arrow-left me-1"></i> Back to Lab Queue
    </a>
    <div class="d-flex align-items-center gap-2">
        <span class="badge bg-{{ $request->priority === 'stat' ? 'danger' : 'info' }} text-uppercase fs-6">
            {{ $request->priority }}
        </span>
        <span class="badge bg-{{ $request->status === 'completed' ? 'success' : 'secondary' }} text-capitalize fs-6">
            {{ str_replace('_', ' ', $request->status) }}
        </span>
    </div>
</div>

<div class="row g-4">
    <div class="col-lg-8">
        <!-- Order Header -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white py-3 d-flex align-items-center justify-content-between">
                <div>
                    <h5 class="mb-0 fw-bold">{{ $request->request_number }}</h5>
                    <small class="text-muted">Requisition Date: {{ $request->created_at->format('M d, Y - H:i') }}</small>
                </div>
                <div>
                    <form action="{{ route('laboratory.requests.update-status', $request) }}" method="POST" class="d-flex align-items-center gap-2">
                        @csrf
                        <select name="status" class="form-select form-select-sm" onchange="this.form.submit()">
                            <option value="pending" {{ $request->status === 'pending' ? 'selected' : '' }}>Pending</option>
                            <option value="sample_collected" {{ $request->status === 'sample_collected' ? 'selected' : '' }}>Sample Collected</option>
                            <option value="processing" {{ $request->status === 'processing' ? 'selected' : '' }}>Processing in Analyzer</option>
                            <option value="completed" {{ $request->status === 'completed' ? 'selected' : '' }}>Completed / Verified</option>
                            <option value="cancelled" {{ $request->status === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                        </select>
                    </form>
                </div>
            </div>
            <div class="card-body p-4">
                <div class="row g-3 mb-3">
                    <div class="col-sm-6">
                        <small class="text-muted d-block">Ordering Doctor</small>
                        <strong class="text-dark">{{ $request->doctor->user->name ?? 'Physician' }}</strong>
                        <div class="text-muted small">{{ $request->doctor->specialization }}</div>
                    </div>
                    <div class="col-sm-6">
                        <small class="text-muted d-block">Medical Technologist</small>
                        <strong class="text-dark">{{ $request->technician->name ?? 'Unassigned' }}</strong>
                        <div class="text-muted small">{{ $request->completed_at ? 'Completed: ' . $request->completed_at->format('M d, H:i') : 'In progress' }}</div>
                    </div>
                </div>

                @if($request->clinical_notes)
                    <div class="p-3 bg-light rounded border mb-0 small">
                        <strong>Clinical Indications:</strong> {{ $request->clinical_notes }}
                    </div>
                @endif
            </div>
        </div>

        <!-- Laboratory Results Form -->
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white py-3">
                <h5 class="mb-0 fw-bold"><i class="bi bi-file-earmark-medical text-primary me-2"></i>Analysis Findings & Reference Values</h5>
            </div>
            <div class="card-body p-4">
                <form action="{{ route('laboratory.requests.save-results', $request) }}" method="POST">
                    @csrf
                    <div class="table-responsive mb-4">
                        <table class="table table-bordered align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>Investigation Parameter</th>
                                    <th>Observed Value *</th>
                                    <th>Unit</th>
                                    <th>Standard Reference Range</th>
                                    <th>Flag *</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($request->results as $idx => $res)
                                    <tr>
                                        <td>
                                            <input type="hidden" name="results[{{ $idx }}][id]" value="{{ $res->id }}">
                                            <strong class="text-dark">{{ $res->parameter_name }}</strong>
                                            <div class="text-muted small">{{ $res->labTestType->category ?? 'Pathology' }}</div>
                                        </td>
                                        <td>
                                            <input type="text" name="results[{{ $idx }}][result_value]" class="form-control" value="{{ old('results.'.$idx.'.result_value', $res->result_value) }}" required>
                                        </td>
                                        <td>
                                            <input type="text" name="results[{{ $idx }}][unit]" class="form-control form-control-sm" value="{{ old('results.'.$idx.'.unit', $res->unit) }}" style="max-width: 90px;">
                                        </td>
                                        <td class="small text-muted">
                                            {{ $res->reference_range }}
                                        </td>
                                        <td>
                                            <select name="results[{{ $idx }}][flag]" class="form-select form-select-sm">
                                                <option value="normal" {{ $res->flag === 'normal' ? 'selected' : '' }}>Normal</option>
                                                <option value="high" {{ $res->flag === 'high' ? 'selected' : '' }}>High</option>
                                                <option value="low" {{ $res->flag === 'low' ? 'selected' : '' }}>Low</option>
                                                <option value="critical" {{ $res->flag === 'critical' ? 'selected' : '' }}>CRITICAL</option>
                                            </select>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="d-flex justify-content-end gap-2">
                        <button type="submit" class="btn btn-primary px-4 fw-semibold">
                            Save Lab Results & Mark Completed
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Patient Context -->
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white py-3">
                <h6 class="mb-0 fw-bold">Specimen Subject</h6>
            </div>
            <div class="card-body">
                <h5 class="fw-bold mb-1">{{ $request->patient->full_name }}</h5>
                <span class="badge bg-light text-dark border mb-3">{{ $request->patient->mrn }}</span>
                <ul class="list-unstyled small mb-3">
                    <li class="mb-1"><strong>Age / Gender:</strong> {{ $request->patient->age }} yrs, {{ ucfirst($request->patient->gender) }}</li>
                    <li class="mb-1"><strong>Blood Group:</strong> {{ $request->patient->blood_group }} ({{ $request->patient->genotype ?? 'N/A' }})</li>
                    <li class="mb-1"><strong>Phone:</strong> {{ $request->patient->phone }}</li>
                </ul>
                <a href="{{ route('patients.show', $request->patient) }}" class="btn btn-outline-primary btn-sm w-100">
                    View Complete EHR Profile
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
