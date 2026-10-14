<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'Dashboard') - {{ \App\Models\Setting::get('hospital_name', 'Hospital Information System') }}</title>

    <!-- Local Offline Assets (100% Internal Network Capable) -->
    <link rel="stylesheet" href="{{ asset('vendor/bootstrap/css/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('vendor/bootstrap-icons/bootstrap-icons.min.css') }}">
    <link rel="stylesheet" href="{{ asset('css/hospital.css') }}">

    @stack('styles')
</head>
<body>
    <div class="app-wrapper">
        <!-- Sidebar Navigation -->
        <aside class="sidebar">
            <a href="{{ route('dashboard') }}" class="sidebar-brand">
                <i class="bi bi-hospital fs-3 text-info"></i>
                <div class="lh-1">
                    <span class="d-block text-white fw-bold">Hospital</span>
                    <small class="text-secondary" style="font-size: 0.65rem; letter-spacing: 0.05rem;">TERTIARY HIS</small>
                </div>
            </a>

            <div class="sidebar-nav">
                <div class="nav-section-title">Core Operations</div>
                <a href="{{ route('dashboard') }}" class="sidebar-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                    <i class="bi bi-speedometer2"></i>
                    <span>Dashboard</span>
                </a>

                <a href="{{ route('patients.index') }}" class="sidebar-link {{ request()->routeIs('patients.*') ? 'active' : '' }}">
                    <i class="bi bi-people"></i>
                    <span>Patients (EMR)</span>
                </a>

                <a href="{{ route('appointments.index') }}" class="sidebar-link {{ request()->routeIs('appointments.*') ? 'active' : '' }}">
                    <i class="bi bi-calendar2-check"></i>
                    <span>Appointments (OPD)</span>
                </a>

                <a href="{{ route('vitals.index') }}" class="sidebar-link {{ request()->routeIs('vitals.*') ? 'active' : '' }}">
                    <i class="bi bi-activity"></i>
                    <span>Triage & Vitals</span>
                </a>

                <a href="{{ route('encounters.index') }}" class="sidebar-link {{ request()->routeIs('encounters.*') ? 'active' : '' }}">
                    <i class="bi bi-journal-medical"></i>
                    <span>Clinical Encounters</span>
                </a>

                <div class="nav-section-title">Departments & Inpatient</div>
                <a href="{{ route('inpatient.index') }}" class="sidebar-link {{ request()->routeIs('inpatient.*') ? 'active' : '' }}">
                    <i class="bi bi-door-open"></i>
                    <span>Inpatient & Wards</span>
                </a>

                <a href="{{ route('doctors.index') }}" class="sidebar-link {{ request()->routeIs('doctors.*') ? 'active' : '' }}">
                    <i class="bi bi-person-badge"></i>
                    <span>Physicians Roster</span>
                </a>

                <a href="{{ route('pharmacy.index') }}" class="sidebar-link {{ request()->routeIs('pharmacy.*') ? 'active' : '' }}">
                    <i class="bi bi-capsule"></i>
                    <span>Pharmacy Formulary</span>
                </a>

                <a href="{{ route('laboratory.index') }}" class="sidebar-link {{ request()->routeIs('laboratory.*') ? 'active' : '' }}">
                    <i class="bi bi-clipboard2-pulse"></i>
                    <span>Laboratory (LIS)</span>
                </a>

                <div class="nav-section-title">Finance & Billing</div>
                <a href="{{ route('billing.index') }}" class="sidebar-link {{ request()->routeIs('billing.*') ? 'active' : '' }}">
                    <i class="bi bi-receipt"></i>
                    <span>Billing & Invoices</span>
                </a>

                @if(Auth::check() && Auth::user()->isAdmin())
                <div class="nav-section-title">Administration</div>
                <a href="{{ route('admin.bulk-import.index') }}" class="sidebar-link {{ request()->routeIs('admin.bulk-import.*') ? 'active' : '' }}">
                    <i class="bi bi-cloud-arrow-up"></i>
                    <span>Bulk Data Import</span>
                </a>

                <a href="{{ route('admin.audit-logs.index') }}" class="sidebar-link {{ request()->routeIs('admin.audit-logs.*') ? 'active' : '' }}">
                    <i class="bi bi-shield-check"></i>
                    <span>Audit Trail</span>
                </a>

                <a href="{{ route('admin.settings.index') }}" class="sidebar-link {{ request()->routeIs('admin.settings.*') ? 'active' : '' }}">
                    <i class="bi bi-gear"></i>
                    <span>Hospital Settings</span>
                </a>
                @endif
            </div>

            <div class="sidebar-footer">
                <div class="d-flex align-items-center gap-2 mb-2">
                    <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center fw-bold" style="width: 34px; height: 34px; font-size: 0.85rem;">
                        {{ strtoupper(substr(Auth::user()->name ?? 'U', 0, 1)) }}
                    </div>
                    <div class="overflow-hidden">
                        <div class="text-white text-truncate fw-semibold" style="font-size: 0.85rem;">{{ Auth::user()->name ?? 'Staff User' }}</div>
                        <span class="badge bg-secondary text-capitalize" style="font-size: 0.65rem;">{{ Auth::user()->role ?? 'staff' }}</span>
                    </div>
                </div>
                <form action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button type="submit" class="btn btn-outline-danger btn-sm w-100 d-flex align-items-center justify-content-center gap-1">
                        <i class="bi bi-box-arrow-right"></i> Sign Out
                    </button>
                </form>
            </div>
        </aside>

        <!-- Main Content Viewport -->
        <div class="main-content">
            <!-- Top Navbar -->
            <header class="top-navbar">
                <div class="d-flex align-items-center gap-3">
                    <button class="btn btn-sm btn-light border d-md-none" id="sidebarToggle" type="button">
                        <i class="bi bi-list"></i>
                    </button>
                    <div>
                        <h5 class="mb-0 fw-bold text-dark">@yield('page_title', 'Hospital Operations')</h5>
                        <small class="text-muted d-none d-sm-inline">
                            <i class="bi bi-building me-1"></i> {{ \App\Models\Setting::get('hospital_name', 'Hospital Information System') }} &bull; {{ now()->format('l, F j, Y') }}
                        </small>
                    </div>
                </div>

                <div class="d-flex align-items-center gap-2">
                    <a href="{{ route('patients.create') }}" class="btn btn-sm btn-primary d-flex align-items-center gap-1">
                        <i class="bi bi-person-plus"></i> <span class="d-none d-md-inline">New Patient</span>
                    </a>
                    <a href="{{ route('appointments.create') }}" class="btn btn-sm btn-outline-primary d-flex align-items-center gap-1">
                        <i class="bi bi-calendar-plus"></i> <span class="d-none d-md-inline">New Appointment</span>
                    </a>
                </div>
            </header>

            <!-- Content Body -->
            <main class="content-body">
                <!-- System Alerts -->
                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show d-flex align-items-center gap-2 mb-3 shadow-sm" role="alert">
                        <i class="bi bi-check-circle-fill fs-5"></i>
                        <div>{{ session('success') }}</div>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                @if(session('warning'))
                    <div class="alert alert-warning alert-dismissible fade show d-flex align-items-center gap-2 mb-3 shadow-sm" role="alert">
                        <i class="bi bi-exclamation-triangle-fill fs-5"></i>
                        <div>{{ session('warning') }}</div>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                @if(session('error'))
                    <div class="alert alert-danger alert-dismissible fade show d-flex align-items-center gap-2 mb-3 shadow-sm" role="alert">
                        <i class="bi bi-x-circle-fill fs-5"></i>
                        <div>{{ session('error') }}</div>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                @if(session('info'))
                    <div class="alert alert-info alert-dismissible fade show d-flex align-items-center gap-2 mb-3 shadow-sm" role="alert">
                        <i class="bi bi-info-circle-fill fs-5"></i>
                        <div>{{ session('info') }}</div>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                @if($errors->any())
                    <div class="alert alert-danger alert-dismissible fade show mb-3 shadow-sm" role="alert">
                        <div class="fw-bold mb-1"><i class="bi bi-exclamation-octagon me-1"></i> Please check the following issues:</div>
                        <ul class="mb-0 ps-3">
                            @foreach($errors->all() as $err)
                                <li>{{ $err }}</li>
                            @endforeach
                        </ul>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                @yield('content')
            </main>
        </div>
    </div>

    <!-- Local Offline JS -->
    <script src="{{ asset('vendor/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
    <script>
        document.getElementById('sidebarToggle')?.addEventListener('click', function() {
            document.querySelector('.sidebar').classList.toggle('d-none');
        });
    </script>
    @stack('scripts')
</body>
</html>
