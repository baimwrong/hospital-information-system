<?php

namespace App\Http\Controllers;

use App\Models\Admission;
use App\Models\Appointment;
use App\Models\Bed;
use App\Models\Doctor;
use App\Models\Invoice;
use App\Models\LabRequest;
use App\Models\Medicine;
use App\Models\Patient;
use App\Models\PatientVital;
use App\Models\Payment;
use Carbon\Carbon;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $today = Carbon::today();

        $todayPayments = (float) Payment::whereDate('created_at', $today)->sum('amount');

        $metrics = [
            'total_patients' => Patient::count(),
            'today_appointments' => Appointment::whereDate('appointment_date', $today)->count(),
            'active_admissions' => Admission::where('status', 'admitted')->count(),
            'total_doctors' => Doctor::where('status', 'active')->count(),
            'occupied_beds' => Bed::where('status', 'occupied')->count(),
            'total_beds' => Bed::count(),
            'pending_lab_tests' => LabRequest::whereIn('status', ['pending', 'sample_collected', 'processing'])->count(),
            'low_stock_medicines' => Medicine::whereColumn('stock_quantity', '<=', 'min_stock_alert')->count(),
            'today_revenue' => $todayPayments > 0 ? $todayPayments : (float) Invoice::whereDate('invoice_date', $today)->sum('paid_amount'),
        ];

        // Bed occupancy rate percentage
        $metrics['bed_occupancy_rate'] = $metrics['total_beds'] > 0
            ? round(($metrics['occupied_beds'] / $metrics['total_beds']) * 100, 1)
            : 0;

        $recentAppointments = Appointment::with(['patient', 'doctor.user', 'department'])
            ->whereDate('appointment_date', '>=', $today)
            ->orderBy('appointment_date')
            ->orderBy('appointment_time')
            ->limit(6)
            ->get();

        $recentAdmissions = Admission::with(['patient', 'doctor.user', 'bed.ward'])
            ->where('status', 'admitted')
            ->latest('admission_date')
            ->limit(5)
            ->get();

        $triageAlerts = PatientVital::with('patient')
            ->whereIn('triage_level', ['resuscitation', 'emergency', 'urgent'])
            ->latest('recorded_at')
            ->limit(5)
            ->get();

        $lowStockMeds = Medicine::whereColumn('stock_quantity', '<=', 'min_stock_alert')
            ->orderBy('stock_quantity')
            ->limit(5)
            ->get();

        return view('dashboard.index', compact(
            'metrics',
            'recentAppointments',
            'recentAdmissions',
            'triageAlerts',
            'lowStockMeds'
        ));
    }
}
