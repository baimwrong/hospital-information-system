@extends('layouts.app')

@section('title', 'Hospital Settings')
@section('page_title', 'Enterprise Administration - Hospital Configuration')

@section('content')
<div class="card border-0 shadow-sm" style="max-width: 860px; margin: 0 auto;">
    <div class="card-header bg-white py-3">
        <h5 class="mb-0 fw-bold"><i class="bi bi-gear-fill text-primary me-2"></i>Hospital Facility Configuration</h5>
    </div>
    <div class="card-body p-4">
        <form action="{{ route('admin.settings.update') }}" method="POST">
            @csrf

            <h6 class="fw-bold text-primary mb-3">Hospital Identity & Branding</h6>
            <div class="row g-3 mb-4">
                <div class="col-md-6">
                    <label class="form-label small fw-semibold">Hospital Legal Entity Name *</label>
                    <input type="text" name="hospital_name" class="form-control" value="{{ old('hospital_name', $settings['hospital_name']) }}" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label small fw-semibold">Hospital Slogan / Tagline</label>
                    <input type="text" name="hospital_tagline" class="form-control" value="{{ old('hospital_tagline', $settings['hospital_tagline']) }}">
                </div>
            </div>

            <h6 class="fw-bold text-primary mb-3">Facility Contact & Physical Address</h6>
            <div class="row g-3 mb-4">
                <div class="col-md-12">
                    <label class="form-label small fw-semibold">Hospital Physical Address *</label>
                    <input type="text" name="hospital_address" class="form-control" value="{{ old('hospital_address', $settings['hospital_address']) }}" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label small fw-semibold">Hospital Helpline / Telephone *</label>
                    <input type="text" name="hospital_phone" class="form-control" value="{{ old('hospital_phone', $settings['hospital_phone']) }}" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label small fw-semibold">Hospital Official Email *</label>
                    <input type="email" name="hospital_email" class="form-control" value="{{ old('hospital_email', $settings['hospital_email']) }}" required>
                </div>
            </div>

            <h6 class="fw-bold text-primary mb-3">Financial Currency & Fiscal Policy</h6>
            <div class="row g-3 mb-4">
                <div class="col-md-4">
                    <label class="form-label small fw-semibold">Currency Symbol *</label>
                    <input type="text" name="currency_symbol" class="form-control" value="{{ old('currency_symbol', $settings['currency_symbol']) }}" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label small fw-semibold">Hospital Service Tax Rate (%) *</label>
                    <input type="number" step="0.1" name="tax_rate_percent" class="form-control" value="{{ old('tax_rate_percent', $settings['tax_rate_percent']) }}" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label small fw-semibold">Timezone *</label>
                    <input type="text" name="timezone" class="form-control" value="{{ old('timezone', $settings['timezone']) }}" required>
                </div>
            </div>

            <div class="d-flex justify-content-end">
                <button type="submit" class="btn btn-primary px-5 fw-semibold">
                    <i class="bi bi-check2-circle me-1"></i> Save Configuration
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
