@extends('layouts.app')

@section('title', 'Security & Audit Logs')
@section('page_title', 'System Security - Clinical Audit Trail')

@section('content')
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body p-3">
        <form action="{{ route('admin.audit-logs.index') }}" method="GET" class="row g-2 align-items-center">
            <div class="col-md-5">
                <input type="text" name="search" class="form-control" placeholder="Search audit trail by description, IP address, Record ID..." value="{{ request('search') }}">
            </div>
            <div class="col-md-3">
                <select name="module" class="form-select">
                    <option value="">All Hospital Modules</option>
                    @foreach($modules as $m)
                        <option value="{{ $m }}" {{ request('module') === $m ? 'selected' : '' }}>{{ $m }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <select name="action" class="form-select">
                    <option value="">All Actions</option>
                    <option value="CREATE" {{ request('action') === 'CREATE' ? 'selected' : '' }}>CREATE</option>
                    <option value="UPDATE" {{ request('action') === 'UPDATE' ? 'selected' : '' }}>UPDATE</option>
                    <option value="DELETE" {{ request('action') === 'DELETE' ? 'selected' : '' }}>DELETE</option>
                    <option value="BULK_IMPORT" {{ request('action') === 'BULK_IMPORT' ? 'selected' : '' }}>BULK_IMPORT</option>
                    <option value="DISPENSE" {{ request('action') === 'DISPENSE' ? 'selected' : '' }}>DISPENSE</option>
                    <option value="ADMISSION" {{ request('action') === 'ADMISSION' ? 'selected' : '' }}>ADMISSION</option>
                    <option value="LOGIN" {{ request('action') === 'LOGIN' ? 'selected' : '' }}>LOGIN</option>
                </select>
            </div>
            <div class="col-md-2 d-flex gap-2">
                <button type="submit" class="btn btn-primary w-100">Filter</button>
                <a href="{{ route('admin.audit-logs.index') }}" class="btn btn-light border"><i class="bi bi-arrow-counterclockwise"></i></a>
            </div>
        </form>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-header bg-white py-3">
        <h5 class="mb-0 fw-bold">Immutable System Audit Trail</h5>
    </div>
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>Timestamp</th>
                    <th>Staff User</th>
                    <th>Module</th>
                    <th>Action</th>
                    <th>Description</th>
                    <th>IP Address</th>
                </tr>
            </thead>
            <tbody>
                @forelse($logs as $log)
                    <tr>
                        <td class="text-nowrap small text-muted">
                            {{ $log->created_at->format('M d, Y - H:i:s') }}
                        </td>
                        <td class="fw-semibold">
                            {{ $log->user->name ?? 'System' }}
                            @if($log->user)
                                <small class="text-muted d-block text-capitalize">{{ $log->user->role }}</small>
                            @endif
                        </td>
                        <td>
                            <span class="badge bg-light text-dark border">{{ $log->module }}</span>
                        </td>
                        <td>
                            @php
                                $badgeClass = match($log->action) {
                                    'CREATE' => 'success',
                                    'UPDATE' => 'info',
                                    'DELETE' => 'danger',
                                    'BULK_IMPORT' => 'primary',
                                    'DISPENSE' => 'warning text-dark',
                                    'ADMISSION' => 'primary',
                                    default => 'secondary'
                                };
                            @endphp
                            <span class="badge bg-{{ $badgeClass }}">{{ $log->action }}</span>
                        </td>
                        <td class="small">{{ $log->description }}</td>
                        <td class="small text-muted font-monospace">{{ $log->ip_address ?? '127.0.0.1' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center py-5 text-muted">No audit trail events found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($logs->hasPages())
        <div class="card-footer bg-white">
            {{ $logs->links() }}
        </div>
    @endif
</div>
@endsection
