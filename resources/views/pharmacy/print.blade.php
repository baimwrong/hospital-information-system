<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Prescription {{ $prescription->prescription_number }} - {{ $hospitalName }}</title>
    <link rel="stylesheet" href="{{ asset('vendor/bootstrap/css/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('vendor/bootstrap-icons/bootstrap-icons.min.css') }}">
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            color: #212529;
            background: #f8f9fa;
        }
        .prescription-container {
            max-width: 800px;
            margin: 20px auto;
            background: #fff;
            padding: 35px 40px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.08);
        }
        .hospital-header {
            border-bottom: 2px solid #0d6efd;
            padding-bottom: 15px;
            margin-bottom: 20px;
        }
        .rx-symbol {
            font-family: 'Times New Roman', serif;
            font-size: 2.2rem;
            font-weight: bold;
            color: #0d6efd;
            line-height: 1;
        }
        .meta-box {
            background-color: #f8f9fa;
            border: 1px solid #e9ecef;
            border-radius: 6px;
            padding: 12px 15px;
        }
        .signature-box {
            margin-top: 50px;
            text-align: right;
        }
        .signature-line {
            display: inline-block;
            width: 240px;
            border-top: 1px solid #000;
            padding-top: 5px;
            text-align: center;
        }
        @media print {
            body {
                background: #fff;
                padding: 0;
            }
            .prescription-container {
                box-shadow: none;
                margin: 0;
                max-width: 100%;
                padding: 15px 25px;
            }
            .no-print {
                display: none !important;
            }
        }
    </style>
</head>
<body>
    <div class="no-print text-center my-3">
        <button onclick="window.print()" class="btn btn-primary px-4 fw-bold me-2">
            <i class="bi bi-printer me-1"></i> Print Prescription (Ctrl+P)
        </button>
        <button onclick="window.close()" class="btn btn-secondary px-3">
            Close Window
        </button>
    </div>

    <div class="prescription-container">
        <!-- Hospital Header -->
        <div class="hospital-header d-flex justify-content-between align-items-center">
            <div class="d-flex align-items-center gap-3">
                <i class="bi bi-hospital fs-1 text-primary"></i>
                <div>
                    <h3 class="fw-bold mb-0 text-dark">{{ $hospitalName }}</h3>
                    <div class="text-muted small">{{ $hospitalAddress }}</div>
                    <div class="text-muted small">Tel: {{ $hospitalPhone }} &bull; Email: {{ $hospitalEmail }}</div>
                </div>
            </div>
            <div class="text-end">
                <div class="small fw-bold text-primary text-uppercase" style="letter-spacing: 0.05rem;">Official Medical Prescription (Rx)</div>
                <span class="badge bg-primary fs-6 mt-1">{{ $prescription->prescription_number }}</span>
                <div class="text-muted small mt-1">Date: {{ $prescription->created_at->format('M d, Y') }}</div>
                <div class="text-muted small">Status: <strong class="text-uppercase">{{ $prescription->status }}</strong></div>
            </div>
        </div>

        <!-- Patient Demographics & Safety Alerts -->
        <div class="row g-3 mb-4">
            <div class="col-8">
                <div class="meta-box h-100">
                    <h6 class="fw-bold text-primary mb-2">Patient Information</h6>
                    <div class="row g-2 small">
                        <div class="col-6"><strong>Name:</strong> {{ $prescription->patient->full_name }}</div>
                        <div class="col-6"><strong>MRN:</strong> {{ $prescription->patient->mrn }}</div>
                        <div class="col-6"><strong>Age / Gender:</strong> {{ $prescription->patient->age }} yrs, {{ ucfirst($prescription->patient->gender) }}</div>
                        <div class="col-6"><strong>Phone:</strong> {{ $prescription->patient->phone }}</div>
                    </div>
                </div>
            </div>
            <div class="col-4">
                <div class="meta-box h-100 border-danger bg-danger bg-opacity-10">
                    <h6 class="fw-bold text-danger mb-1"><i class="bi bi-exclamation-triangle-fill me-1"></i>Documented Allergies</h6>
                    @if(!empty($prescription->patient->allergies) && is_array($prescription->patient->allergies))
                        <div class="small fw-semibold text-danger">
                            {{ implode(', ', $prescription->patient->allergies) }}
                        </div>
                    @else
                        <small class="text-muted">No known drug allergies (NKDA)</small>
                    @endif
                </div>
            </div>
        </div>

        <!-- Prescriber Details -->
        <div class="meta-box mb-4">
            <div class="row g-2 small">
                <div class="col-md-4">
                    <strong>Prescribing Physician:</strong> Dr. {{ $prescription->doctor->user->name ?? 'Physician' }}
                </div>
                <div class="col-md-4">
                    <strong>Department:</strong> {{ $prescription->doctor->department->name ?? 'General Practice' }}
                </div>
                <div class="col-md-4">
                    <strong>Medical License #:</strong> {{ $prescription->doctor->license_number }}
                </div>
            </div>
        </div>

        <!-- Rx Prescription Items Table -->
        <div class="d-flex align-items-center mb-2">
            <span class="rx-symbol me-2">&#8478;</span>
            <h5 class="fw-bold mb-0">Prescribed Medications</h5>
        </div>

        <div class="table-responsive mb-4">
            <table class="table table-bordered align-middle">
                <thead class="table-light">
                    <tr>
                        <th style="width: 5%;">#</th>
                        <th style="width: 35%;">Medication & Molecule</th>
                        <th style="width: 25%;">Dosage & Frequency</th>
                        <th style="width: 15%;">Duration</th>
                        <th style="width: 20%;">Qty Prescribed</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($prescription->items as $idx => $item)
                        <tr>
                            <td class="text-center fw-bold">{{ $idx + 1 }}</td>
                            <td>
                                <strong class="d-block">{{ $item->medicine->brand_name }}</strong>
                                <small class="text-muted">{{ $item->medicine->generic_name }} &bull; {{ $item->medicine->dosage_form }} ({{ $item->medicine->strength }})</small>
                                @if($item->instructions)
                                    <div class="small text-primary mt-1"><i class="bi bi-info-circle me-1"></i>{{ $item->instructions }}</div>
                                @endif
                            </td>
                            <td>{{ $item->dosage }}</td>
                            <td>{{ $item->duration_days }} day(s)</td>
                            <td class="fw-bold">{{ $item->quantity_prescribed }} units</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        @if($prescription->notes)
            <div class="p-3 bg-light rounded border mb-4 small">
                <strong>Clinical Notes / Directives:</strong> {{ $prescription->notes }}
            </div>
        @endif

        <!-- Doctor Signature Block -->
        <div class="row align-items-end mt-4 pt-3">
            <div class="col-7">
                <div class="p-2 border rounded bg-light small text-muted" style="font-size: 0.72rem;">
                    <strong>Instructions for Patient:</strong> Take strictly as directed by the prescribing physician. Do not share or reuse medications. Keep stored safely away from children. In case of unexpected adverse reactions, report immediately to the Hospital Emergency department.
                </div>
            </div>
            <div class="col-5 text-end">
                <div class="signature-box mt-0">
                    <div class="signature-line">
                        <strong>Dr. {{ $prescription->doctor->user->name ?? 'Physician' }}</strong><br>
                        <small class="text-muted">MD, {{ $prescription->doctor->specialization }}<br>License: {{ $prescription->doctor->license_number }}</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
