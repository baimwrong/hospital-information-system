<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Lab Report {{ $request->request_number }} - {{ $hospitalName }}</title>
    <link rel="stylesheet" href="{{ asset('vendor/bootstrap/css/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('vendor/bootstrap-icons/bootstrap-icons.min.css') }}">
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            color: #212529;
            background: #f8f9fa;
        }
        .report-container {
            max-width: 840px;
            margin: 20px auto;
            background: #fff;
            padding: 35px 40px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.08);
        }
        .lab-header {
            border-bottom: 2px solid #0dcaf0;
            padding-bottom: 15px;
            margin-bottom: 20px;
        }
        .meta-box {
            background-color: #f8f9fa;
            border: 1px solid #e9ecef;
            border-radius: 6px;
            padding: 12px 15px;
        }
        .signature-box {
            margin-top: 40px;
            text-align: right;
        }
        .signature-line {
            display: inline-block;
            width: 250px;
            border-top: 1px solid #000;
            padding-top: 5px;
            text-align: center;
        }
        @media print {
            body {
                background: #fff;
                padding: 0;
            }
            .report-container {
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
        <button onclick="window.print()" class="btn btn-info text-white px-4 fw-bold me-2">
            <i class="bi bi-printer me-1"></i> Print Laboratory Report (Ctrl+P)
        </button>
        <button onclick="window.close()" class="btn btn-secondary px-3">
            Close Window
        </button>
    </div>

    <div class="report-container">
        <!-- Laboratory Header -->
        <div class="lab-header d-flex justify-content-between align-items-center">
            <div class="d-flex align-items-center gap-3">
                <i class="bi bi-clipboard2-pulse fs-1 text-info"></i>
                <div>
                    <h3 class="fw-bold mb-0 text-dark">{{ $hospitalName }}</h3>
                    <div class="text-uppercase fw-semibold small text-info" style="letter-spacing: 0.05rem;">Department of Pathology & Clinical Diagnostics</div>
                    <div class="text-muted small">{{ $hospitalAddress }} &bull; Tel: {{ $hospitalPhone }}</div>
                </div>
            </div>
            <div class="text-end">
                <span class="badge bg-info text-white fs-6">{{ $request->request_number }}</span>
                <div class="text-muted small mt-1">Priority: <strong class="text-uppercase">{{ $request->priority }}</strong></div>
                <div class="text-muted small">Status: <strong class="text-uppercase">{{ $request->status }}</strong></div>
            </div>
        </div>

        <!-- Specimen & Patient Demographics -->
        <div class="row g-3 mb-4">
            <div class="col-6">
                <div class="meta-box h-100">
                    <h6 class="fw-bold text-info mb-2">Patient Demographics</h6>
                    <div class="row g-1 small">
                        <div class="col-12"><strong>Patient Name:</strong> {{ $request->patient->full_name }}</div>
                        <div class="col-6"><strong>MRN:</strong> {{ $request->patient->mrn }}</div>
                        <div class="col-6"><strong>Age / Gender:</strong> {{ $request->patient->age }} yrs / {{ ucfirst($request->patient->gender) }}</div>
                        <div class="col-6"><strong>Blood Group:</strong> {{ $request->patient->blood_group }}</div>
                        <div class="col-6"><strong>Phone:</strong> {{ $request->patient->phone }}</div>
                    </div>
                </div>
            </div>
            <div class="col-6">
                <div class="meta-box h-100">
                    <h6 class="fw-bold text-info mb-2">Order & Specimen Tracking</h6>
                    <div class="row g-1 small">
                        <div class="col-12"><strong>Ordering Doctor:</strong> Dr. {{ $request->doctor->user->name ?? 'Physician' }} ({{ $request->doctor->specialization }})</div>
                        <div class="col-6"><strong>Requested:</strong> {{ $request->created_at->format('M d, Y - H:i') }}</div>
                        <div class="col-6"><strong>Collected:</strong> {{ $request->collected_at ? $request->collected_at->format('M d, Y - H:i') : 'Pending' }}</div>
                        <div class="col-6"><strong>Completed:</strong> {{ $request->completed_at ? $request->completed_at->format('M d, Y - H:i') : 'In Progress' }}</div>
                        <div class="col-6"><strong>Technologist:</strong> {{ $request->technician->name ?? 'Diagnostic Lab Staff' }}</div>
                    </div>
                </div>
            </div>
        </div>

        @if($request->clinical_notes)
            <div class="p-2 px-3 bg-light rounded border mb-4 small">
                <strong>Clinical Indication / Diagnosis:</strong> {{ $request->clinical_notes }}
            </div>
        @endif

        <!-- Diagnostic Findings Table -->
        <h5 class="fw-bold text-dark mb-3"><i class="bi bi-eyedropper text-info me-1"></i>Pathology Findings & Biomarkers</h5>

        <div class="table-responsive mb-4">
            <table class="table table-bordered align-middle">
                <thead class="table-light">
                    <tr>
                        <th style="width: 28%;">Investigation / Test</th>
                        <th style="width: 22%;">Parameter Tested</th>
                        <th style="width: 18%;">Patient Result</th>
                        <th style="width: 10%;">Unit</th>
                        <th style="width: 22%;">Reference Biological Interval</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($request->results as $res)
                        <tr>
                            <td>
                                <strong>{{ $res->labTestType->name }}</strong>
                                <small class="text-muted d-block">{{ $res->labTestType->category }} &bull; Code: {{ $res->labTestType->test_code }}</small>
                            </td>
                            <td>{{ $res->parameter_name }}</td>
                            <td>
                                @php
                                    $flagClass = match($res->flag) {
                                        'critical' => 'badge bg-danger fs-6',
                                        'high' => 'badge bg-warning text-dark',
                                        'low' => 'badge bg-warning text-dark',
                                        default => 'fw-bold text-success',
                                    };
                                @endphp
                                <span class="{{ $flagClass }}">{{ $res->result_value }}</span>
                                @if($res->flag !== 'normal')
                                    <span class="badge bg-danger ms-1 text-uppercase" style="font-size: 0.65rem;">{{ $res->flag }}</span>
                                @endif
                                @if($res->comments)
                                    <div class="small text-muted mt-1" style="font-size: 0.72rem;">{{ $res->comments }}</div>
                                @endif
                            </td>
                            <td class="text-muted small">{{ $res->unit ?: '—' }}</td>
                            <td class="small text-muted">{{ $res->reference_range ?: 'Standard Interval' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Interpretive Comments & Pathologist Sign-off -->
        <div class="row align-items-end mt-4 pt-3">
            <div class="col-7">
                <div class="p-2 border rounded bg-light small text-muted" style="font-size: 0.72rem;">
                    <strong>Disclaimer:</strong> Laboratory findings must be interpreted in conjunction with patient history and other clinical examinations. In case of marked abnormal or critical values, re-evaluation or confirmation is recommended. This report is verified electronically by certified clinical laboratory technologists.
                </div>
            </div>
            <div class="col-5 text-end">
                <div class="signature-box mt-0">
                    <div class="signature-line">
                        <strong>{{ $request->technician->name ?? 'Chief Clinical Pathologist' }}</strong><br>
                        <small class="text-muted">Clinical Laboratory Technologist<br>Verified Date: {{ $request->completed_at ? $request->completed_at->format('M d, Y - H:i') : now()->format('M d, Y') }}</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
