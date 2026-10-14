@extends('layouts.app')

@section('title', 'Admin Bulk Data Import')
@section('page_title', 'Master Data Bulk Import Hub')

@section('content')
<div class="row g-4 mb-4">
    <div class="col-lg-8">
        <!-- Upload Form Card -->
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white py-3">
                <h5 class="mb-0 fw-bold"><i class="bi bi-cloud-arrow-up-fill text-primary me-2"></i>Upload Master Dataset (.CSV)</h5>
                <small class="text-muted">Import clinical and operational records in bulk into the hospital database.</small>
            </div>
            <div class="card-body p-4">
                <form action="{{ route('admin.bulk-import.import') }}" method="POST" enctype="multipart/form-data">
                    @csrf

                    <div class="mb-4">
                        <label class="form-label fw-bold text-dark">1. Select Target Hospital Dataset</label>
                        <div class="row g-2">
                            @foreach($entities as $key => $info)
                                <div class="col-md-6 col-xl-4">
                                    <label class="card h-100 p-3 border cursor-pointer entity-option {{ $loop->first ? 'border-primary bg-primary bg-opacity-10' : '' }}" style="cursor: pointer;">
                                        <div class="d-flex align-items-center gap-2 mb-2">
                                            <input type="radio" name="entity_type" value="{{ $key }}" class="form-check-input" {{ $loop->first ? 'checked' : '' }} onchange="selectEntity(this)">
                                            <i class="bi {{ $info['icon'] }} fs-5 text-{{ $info['color'] }}"></i>
                                            <span class="fw-bold text-dark small">{{ $info['title'] }}</span>
                                        </div>
                                        <p class="text-muted small mb-0 lh-sm">{{ $info['description'] }}</p>
                                    </label>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <div class="mb-4">
                        <div class="d-flex align-items-center justify-content-between mb-2">
                            <label for="csv_file" class="form-label fw-bold text-dark mb-0">2. Select CSV File from Local Network</label>
                            <a href="{{ route('admin.bulk-import.template', 'patients') }}" id="templateDownloadBtn" class="btn btn-sm btn-outline-success d-flex align-items-center gap-1">
                                <i class="bi bi-file-earmark-arrow-down"></i> Download Sample CSV Template
                            </a>
                        </div>
                        <input type="file" name="file" id="csv_file" class="form-control form-control-lg" accept=".csv,text/csv,text/plain" required>
                        <div class="form-text">
                            Supports standard UTF-8 CSV files with comma (,) or semicolon (;) delimiter. Max file size: 10MB.
                        </div>
                    </div>

                    <div class="p-3 bg-light rounded border mb-4">
                        <h6 class="fw-bold text-dark mb-2"><i class="bi bi-info-circle me-1"></i> Import Safety & Integrity Rules</h6>
                        <ul class="small text-muted mb-0 ps-3">
                            <li>Transactions: Successful records are written to PostgreSQL 18 with relational integrity.</li>
                            <li>Duplicate prevention: Records with duplicate unique keys (such as MRN or Doctor license) will be flagged without stopping valid rows.</li>
                            <li>Templates: Use the template button above to get the exact expected headers for the chosen entity.</li>
                        </ul>
                    </div>

                    <button type="submit" class="btn btn-primary btn-lg w-100 d-flex align-items-center justify-content-center gap-2 shadow-sm">
                        <i class="bi bi-check2-circle fs-5"></i> Start Data Ingestion & Import
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- Quick Reference & Template Center -->
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white py-3">
                <h6 class="mb-0 fw-bold"><i class="bi bi-folder-check text-success me-2"></i>Pre-formatted CSV Templates</h6>
            </div>
            <div class="list-group list-group-flush">
                @foreach($entities as $key => $info)
                    <div class="list-group-item d-flex align-items-center justify-content-between py-3">
                        <div class="d-flex align-items-center gap-2">
                            <i class="bi {{ $info['icon'] }} fs-4 text-{{ $info['color'] }}"></i>
                            <div>
                                <div class="fw-semibold">{{ $info['title'] }}</div>
                                <small class="text-muted">{{ $key }}.csv</small>
                            </div>
                        </div>
                        <a href="{{ route('admin.bulk-import.template', $key) }}" class="btn btn-sm btn-outline-primary" title="Download Template">
                            <i class="bi bi-download"></i>
                        </a>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</div>

<!-- Historical Bulk Import Jobs Table -->
<div class="card border-0 shadow-sm">
    <div class="card-header bg-white py-3 d-flex align-items-center justify-content-between">
        <h5 class="mb-0 fw-bold"><i class="bi bi-clock-history text-secondary me-2"></i>Recent Bulk Import Audits</h5>
        <span class="badge bg-secondary">{{ $imports->total() }} Total Import Tasks</span>
    </div>
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>Date & Time</th>
                    <th>Initiated By</th>
                    <th>Target Dataset</th>
                    <th>Filename</th>
                    <th>Total Rows</th>
                    <th>Imported</th>
                    <th>Failed</th>
                    <th>Status</th>
                    <th>Report</th>
                </tr>
            </thead>
            <tbody>
                @forelse($imports as $imp)
                    <tr>
                        <td class="text-nowrap small text-muted">
                            {{ $imp->created_at->format('M d, Y - H:i') }}
                        </td>
                        <td class="fw-semibold">
                            {{ $imp->user->name ?? 'Admin' }}
                        </td>
                        <td>
                            <span class="badge bg-light text-dark border text-capitalize">{{ str_replace('_', ' ', $imp->entity_type) }}</span>
                        </td>
                        <td class="small text-truncate" style="max-width: 180px;" title="{{ $imp->original_filename }}">
                            {{ $imp->original_filename }}
                        </td>
                        <td class="fw-bold">{{ $imp->total_rows }}</td>
                        <td>
                            <span class="badge bg-success">{{ $imp->successful_rows }}</span>
                        </td>
                        <td>
                            @if($imp->failed_rows > 0)
                                <span class="badge bg-danger">{{ $imp->failed_rows }}</span>
                            @else
                                <span class="text-muted">0</span>
                            @endif
                        </td>
                        <td>
                            @if($imp->status === 'completed')
                                <span class="badge bg-success">Success</span>
                            @elseif($imp->status === 'completed_with_errors')
                                <span class="badge bg-warning text-dark">Partial Errors</span>
                            @else
                                <span class="badge bg-danger">Failed</span>
                            @endif
                        </td>
                        <td>
                            <a href="{{ route('admin.bulk-import.show', $imp) }}" class="btn btn-sm btn-outline-secondary">
                                Details
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" class="text-center py-4 text-muted">No bulk imports recorded yet. Upload a dataset above to begin.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($imports->hasPages())
        <div class="card-footer bg-white">
            {{ $imports->links() }}
        </div>
    @endif
</div>

@push('scripts')
<script>
    function selectEntity(radio) {
        document.querySelectorAll('.entity-option').forEach(el => {
            el.classList.remove('border-primary', 'bg-primary', 'bg-opacity-10');
        });
        radio.closest('.entity-option').classList.add('border-primary', 'bg-primary', 'bg-opacity-10');
        
        const templateBtn = document.getElementById('templateDownloadBtn');
        templateBtn.href = "{{ url('admin/bulk-import/template') }}/" + radio.value;
    }
</script>
@endpush
@endsection
