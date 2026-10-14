@extends('layouts.app')

@section('title', 'Pharmacy & Formulary Inventory')
@section('page_title', 'Pharmacy Department - Drug Formulary & Inventory')

@section('content')
<!-- Inventory KPI Summary -->
<div class="row g-3 mb-4">
    <div class="col-sm-6 col-md-3">
        <div class="metric-card">
            <div class="metric-val text-primary">{{ $medicines->total() }}</div>
            <div class="metric-label">Formulary Items</div>
        </div>
    </div>
    <div class="col-sm-6 col-md-3">
        <div class="metric-card">
            <div class="metric-val text-warning">{{ $lowStockCount }}</div>
            <div class="metric-label">Low Stock Warnings</div>
        </div>
    </div>
    <div class="col-sm-6 col-md-3">
        <div class="metric-card">
            <div class="metric-val text-danger">{{ $outOfStockCount }}</div>
            <div class="metric-label">Stock-Outs (Zero Units)</div>
        </div>
    </div>
    <div class="col-sm-6 col-md-3">
        <div class="metric-card d-flex align-items-center justify-content-between">
            <div>
                <a href="{{ route('pharmacy.prescriptions') }}" class="btn btn-outline-primary btn-sm">
                    <i class="bi bi-capsule me-1"></i> Dispense Prescriptions
                </a>
            </div>
            <div>
                <a href="{{ route('pharmacy.create') }}" class="btn btn-primary btn-sm">
                    <i class="bi bi-plus-lg me-1"></i> Add Drug
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Filters -->
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body p-3">
        <form action="{{ route('pharmacy.index') }}" method="GET" class="row g-2 align-items-center">
            <div class="col-md-5">
                <input type="text" name="search" class="form-control" placeholder="Search by Brand Name, Generic Name, Drug Code..." value="{{ request('search') }}">
            </div>
            <div class="col-md-3">
                <select name="category" class="form-select">
                    <option value="">All Categories</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat }}" {{ request('category') === $cat ? 'selected' : '' }}>{{ $cat }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <select name="stock_status" class="form-select">
                    <option value="">All Stock</option>
                    <option value="low" {{ request('stock_status') === 'low' ? 'selected' : '' }}>Low Stock Alerts</option>
                    <option value="out" {{ request('stock_status') === 'out' ? 'selected' : '' }}>Out of Stock</option>
                </select>
            </div>
            <div class="col-md-2 d-flex gap-2">
                <button type="submit" class="btn btn-primary w-100">Filter</button>
                <a href="{{ route('pharmacy.index') }}" class="btn btn-light border"><i class="bi bi-arrow-counterclockwise"></i></a>
            </div>
        </form>
    </div>
</div>

<!-- Medicines Table -->
<div class="card border-0 shadow-sm">
    <div class="card-header bg-white py-3">
        <h5 class="mb-0 fw-bold">Pharmaceutical Inventory</h5>
    </div>
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>Drug Code</th>
                    <th>Brand & Generic Name</th>
                    <th>Category</th>
                    <th>Form & Strength</th>
                    <th>Stock Level</th>
                    <th>Threshold</th>
                    <th>Unit Price</th>
                    <th>Expiry Date</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($medicines as $med)
                    <tr>
                        <td class="fw-bold">{{ $med->code }}</td>
                        <td>
                            <div class="fw-semibold text-dark">{{ $med->brand_name }}</div>
                            <small class="text-muted">{{ $med->generic_name }}</small>
                        </td>
                        <td>
                            <span class="badge bg-light text-dark border">{{ $med->category }}</span>
                        </td>
                        <td>{{ $med->dosage_form }} ({{ $med->strength }})</td>
                        <td>
                            @if($med->stock_quantity <= 0)
                                <span class="badge bg-danger">OUT OF STOCK (0)</span>
                            @elseif($med->stock_quantity <= $med->min_stock_alert)
                                <span class="badge bg-warning text-dark">{{ $med->stock_quantity }} (LOW)</span>
                            @else
                                <span class="badge bg-success">{{ $med->stock_quantity }} In Stock</span>
                            @endif
                        </td>
                        <td class="text-muted">{{ $med->min_stock_alert }}</td>
                        <td class="fw-bold">{{ \App\Models\Setting::get('currency_symbol', '$') }}{{ number_format($med->unit_price, 2) }}</td>
                        <td class="small {{ ($med->expiry_date && $med->expiry_date->isPast()) ? 'text-danger fw-bold' : '' }}">
                            {{ $med->expiry_date ? $med->expiry_date->format('M Y') : 'N/A' }}
                        </td>
                        <td>
                            <a href="{{ route('pharmacy.edit', $med) }}" class="btn btn-sm btn-outline-secondary" title="Edit Inventory / Price">
                                <i class="bi bi-pencil"></i>
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" class="text-center py-5 text-muted">No medications found in formulary.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($medicines->hasPages())
        <div class="card-footer bg-white">
            {{ $medicines->links() }}
        </div>
    @endif
</div>
@endsection
