@extends('layouts.app')

@section('title', 'Import Job Inspection')
@section('page_title', 'Bulk Import Execution Report')

@section('content')
<div class="mb-3">
    <a href="{{ route('admin.bulk-import.index') }}" class="btn btn-sm btn-outline-secondary">
        <i class="bi bi-arrow-left me-1"></i> Back to Bulk Import Hub
    </a>
</div>

<!-- Summary Card -->
<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-white py-3 d-flex align-items-center justify-content-between">
        <h5 class="mb-0 fw-bold">Import Task #{{ $import->id }} Details</h5>
        @if($import->status === 'completed')
            <span class="badge bg-success fs-6">Completed Successfully</span>
        @elseif($import->status === 'completed_with_errors')
            <span class="badge bg-warning text-dark fs-6">Completed with Row Errors</span>
        @else
            <span class="badge bg-danger fs-6">Failed</span>
        @endif
    </div>
    <div class="card-body p-4">
        <div class="row g-3">
            <div class="col-sm-6 col-md-3">
                <small class="text-muted d-block">Target Entity</small>
                <span class="fw-bold fs-5 text-capitalize text-dark">{{ str_replace('_', ' ', $import->entity_type) }}</span>
            </div>
            <div class="col-sm-6 col-md-3">
                <small class="text-muted d-block">Uploaded File</small>
                <span class="fw-bold fs-5 text-dark">{{ $import->original_filename }}</span>
            </div>
            <div class="col-sm-6 col-md-3">
                <small class="text-muted d-block">Total Rows Read</small>
                <span class="fw-bold fs-5 text-dark">{{ $import->total_rows }}</span>
            </div>
            <div class="col-sm-6 col-md-3">
                <small class="text-muted d-block">Processed By</small>
                <span class="fw-bold fs-5 text-dark">{{ $import->user->name ?? 'Administrator' }}</span>
            </div>
        </div>

        <hr class="my-4">

        <div class="row g-3 text-center">
            <div class="col-4">
                <div class="p-3 bg-light rounded">
                    <div class="fs-3 fw-bold text-dark">{{ $import->total_rows }}</div>
                    <small class="text-muted fw-semibold">Total Records</small>
                </div>
            </div>
            <div class="col-4">
                <div class="p-3 bg-success bg-opacity-10 rounded">
                    <div class="fs-3 fw-bold text-success">{{ $import->successful_rows }}</div>
                    <small class="text-success fw-semibold">Imported Successfully</small>
                </div>
            </div>
            <div class="col-4">
                <div class="p-3 bg-danger bg-opacity-10 rounded">
                    <div class="fs-3 fw-bold text-danger">{{ $import->failed_rows }}</div>
                    <small class="text-danger fw-semibold">Failed Validation</small>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Errors Breakdown -->
<div class="card border-0 shadow-sm">
    <div class="card-header bg-white py-3">
        <h5 class="mb-0 fw-bold"><i class="bi bi-exclamation-triangle-fill text-warning me-2"></i>Row-by-Row Error Log</h5>
        <small class="text-muted">Review any rows rejected during validation so you can amend your spreadsheet and re-import.</small>
    </div>
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th style="width: 100px;">Row Number</th>
                    <th>Record Preview</th>
                    <th>Validation Reason / Error Description</th>
                </tr>
            </thead>
            <tbody>
                @if(!empty($import->error_summary))
                    @foreach($import->error_summary as $err)
                        <tr>
                            <td>
                                <span class="badge bg-secondary">Row {{ $err['row'] ?? 'N/A' }}</span>
                            </td>
                            <td>
                                @if(!empty($err['data']))
                                    <code class="small text-muted">{{ json_encode($err['data']) }}</code>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td>
                                <span class="text-danger fw-semibold">{{ $err['error'] ?? 'Unknown error' }}</span>
                            </td>
                        </tr>
                    @endforeach
                @else
                    <tr>
                        <td colspan="3" class="text-center py-4 text-success">
                            <i class="bi bi-check-circle-fill me-1"></i> No errors recorded! All rows imported smoothly.
                        </td>
                    </tr>
                @endif
            </tbody>
        </table>
    </div>
</div>
@endsection
