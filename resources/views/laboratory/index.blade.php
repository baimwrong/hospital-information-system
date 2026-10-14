@extends('layouts.app')

@section('title', 'Laboratory (LIS)')
@section('page_title', 'Laboratory Information System (LIS) - Diagnostic Orders')

@section('content')
<div class="row g-3 mb-4">
    <div class="col-sm-6 col-md-4">
        <div class="metric-card">
            <div class="metric-val text-primary">{{ $requests->total() }}</div>
            <div class="metric-label">Total Test Requisitions</div>
        </div>
    </div>
    <div class="col-sm-6 col-md-4">
        <div class="metric-card">
            <div class="metric-val text-warning">{{ $pendingCount }}</div>
            <div class="metric-label">Pending Specimen / Processing</div>
        </div>
    </div>
    <div class="col-sm-6 col-md-4 d-flex align-items-center justify-content-end gap-2">
        <a href="{{ route('laboratory.test-types') }}" class="btn btn-outline-primary">
            <i class="bi bi-list-columns-reverse me-1"></i> Test Catalog
        </a>
        <a href="{{ route('laboratory.requests.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-lg me-1"></i> Order Investigation
        </a>
    </div>
</div>

<div class="card border-0 shadow-sm mb-4">
    <div class="card-body p-3">
        <form action="{{ route('laboratory.index') }}" method="GET" class="row g-2 align-items-center">
            <div class="col-md-5">
                <input type="text" name="search" class="form-control" placeholder="Search by Order #, Patient Name, MRN..." value="{{ request('search') }}">
            </div>
            <div class="col-md-3">
                <select name="status" class="form-select">
                    <option value="">All Statuses</option>
                    @foreach(['pending', 'sample_collected', 'processing', 'completed', 'cancelled'] as $st)
                        <option value="{{ $st }}" {{ request('status') === $st ? 'selected' : '' }}>{{ ucfirst(str_replace('_', ' ', $st)) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <select name="priority" class="form-select">
                    <option value="">All Priorities</option>
                    <option value="routine" {{ request('priority') === 'routine' ? 'selected' : '' }}>Routine</option>
                    <option value="urgent" {{ request('priority') === 'urgent' ? 'selected' : '' }}>Urgent</option>
                    <option value="stat" {{ request('priority') === 'stat' ? 'selected' : '' }}>STAT (Emergency)</option>
                </select>
            </div>
            <div class="col-md-2 d-flex gap-2">
                <button type="submit" class="btn btn-primary w-100">Filter</button>
                <a href="{{ route('laboratory.index') }}" class="btn btn-light border"><i class="bi bi-arrow-counterclockwise"></i></a>
            </div>
        </form>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>Order #</th>
                    <th>Date</th>
                    <th>Patient</th>
                    <th>Ordering Physician</th>
                    <th>Tests Ordered</th>
                    <th>Priority</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($requests as $lab)
                    <tr>
                        <td class="fw-bold">{{ $lab->request_number }}</td>
                        <td class="small">{{ $lab->created_at->format('M d, Y - H:i') }}</td>
                        <td>
                            <a href="{{ route('patients.show', $lab->patient) }}" class="fw-semibold text-decoration-none">
                                {{ $lab->patient->full_name }}
                            </a>
                            <div class="small text-muted">{{ $lab->patient->mrn }}</div>
                        </td>
                        <td>{{ $lab->doctor->user->name ?? 'Physician' }}</td>
                        <td>
                            <span class="badge bg-light text-dark border">{{ $lab->results->count() }} Investigation(s)</span>
                            <div class="small text-muted text-truncate" style="max-width: 200px;">
                                {{ $lab->results->pluck('parameter_name')->implode(', ') }}
                            </div>
                        </td>
                        <td>
                            <span class="badge bg-{{ $lab->priority === 'stat' ? 'danger' : ($lab->priority === 'urgent' ? 'warning text-dark' : 'info') }} text-uppercase">
                                {{ $lab->priority }}
                            </span>
                        </td>
                        <td>
                            <span class="badge bg-{{ $lab->status === 'completed' ? 'success' : ($lab->status === 'sample_collected' ? 'primary' : 'secondary') }} text-capitalize">
                                {{ str_replace('_', ' ', $lab->status) }}
                            </span>
                        </td>
                        <td>
                            <a href="{{ route('laboratory.show', $lab) }}" class="btn btn-sm btn-outline-primary">
                                Lab Sheet & Results
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="text-center py-5 text-muted">No laboratory orders recorded.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($requests->hasPages())
        <div class="card-footer bg-white">
            {{ $requests->links() }}
        </div>
    @endif
</div>
@endsection
