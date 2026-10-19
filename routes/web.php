<?php

use App\Http\Controllers\AppointmentController;
use App\Http\Controllers\AuditLogController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\BillingController;
use App\Http\Controllers\BulkImportController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DoctorController;
use App\Http\Controllers\InpatientController;
use App\Http\Controllers\LaboratoryController;
use App\Http\Controllers\MedicalRecordController;
use App\Http\Controllers\PatientController;
use App\Http\Controllers\PharmacyController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\VitalSignController;
use Illuminate\Support\Facades\Route;

// Redirect root to dashboard or login
Route::get('/', function () {
    return redirect()->route('dashboard');
});

// Authentication Routes
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.post');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Authenticated Medical Staff Routes
Route::middleware('auth')->group(function () {
    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Patients EMR
    Route::get('/patients/search/quick', [PatientController::class, 'quickSearch'])
        ->middleware('role:admin,receptionist,nurse,doctor,accountant,laboratorist')
        ->name('patients.quick-search');
    Route::delete('/patients/{patient}', [PatientController::class, 'destroy'])
        ->middleware('role:admin')
        ->name('patients.destroy');
    Route::resource('patients', PatientController::class)
        ->middleware('role:admin,receptionist,nurse,doctor')
        ->except(['destroy']);

    // Physicians Directory
    Route::get('/doctors', [DoctorController::class, 'index'])->name('doctors.index');
    Route::get('/doctors/{doctor}', [DoctorController::class, 'show'])->name('doctors.show');
    Route::resource('doctors', DoctorController::class)
        ->middleware('role:admin')
        ->only(['create', 'store', 'edit', 'update']);

    // Outpatient Appointments
    Route::resource('appointments', AppointmentController::class)
        ->middleware('role:admin,receptionist,nurse,doctor')
        ->except(['edit', 'update', 'destroy']);
    Route::post('/appointments/{appointment}/status', [AppointmentController::class, 'updateStatus'])
        ->middleware('role:admin,receptionist,nurse,doctor')
        ->name('appointments.update-status');

    // Nursing & Triage Vital Signs
    Route::middleware('role:admin,nurse,doctor')->group(function () {
        Route::get('/vitals', [VitalSignController::class, 'index'])->name('vitals.index');
        Route::get('/vitals/create', [VitalSignController::class, 'create'])->name('vitals.create');
        Route::post('/vitals', [VitalSignController::class, 'store'])->name('vitals.store');
    });

    // Clinical Encounters & EMR Consultations
    Route::get('/encounters', [MedicalRecordController::class, 'index'])
        ->middleware('role:admin,doctor,nurse')
        ->name('encounters.index');
    Route::get('/encounters/create', [MedicalRecordController::class, 'create'])
        ->middleware('role:admin,doctor')
        ->name('encounters.create');
    Route::post('/encounters', [MedicalRecordController::class, 'store'])
        ->middleware('role:admin,doctor')
        ->name('encounters.store');
    Route::get('/encounters/{encounter}', [MedicalRecordController::class, 'show'])
        ->middleware('role:admin,doctor,nurse')
        ->name('encounters.show');

    // Inpatient (IPD) Admissions & Bed Management
    Route::prefix('inpatient')->name('inpatient.')->group(function () {
        Route::get('/', [InpatientController::class, 'index'])
            ->middleware('role:admin,doctor,nurse,receptionist')
            ->name('index');
        Route::get('/wards', [InpatientController::class, 'wards'])
            ->middleware('role:admin,doctor,nurse,receptionist')
            ->name('wards');
        Route::post('/wards', [InpatientController::class, 'storeWard'])
            ->middleware('role:admin')
            ->name('wards.store');
        Route::get('/admissions/create', [InpatientController::class, 'createAdmission'])
            ->middleware('role:admin,doctor,nurse,receptionist')
            ->name('admissions.create');
        Route::post('/admissions', [InpatientController::class, 'storeAdmission'])
            ->middleware('role:admin,doctor,nurse,receptionist')
            ->name('admissions.store');
        Route::get('/admissions/{admission}', [InpatientController::class, 'show'])
            ->middleware('role:admin,doctor,nurse,receptionist')
            ->name('show');
        Route::post('/admissions/{admission}/discharge', [InpatientController::class, 'discharge'])
            ->middleware('role:admin,doctor,nurse')
            ->name('discharge');
    });

    // Pharmacy & Formulary Inventory
    Route::prefix('pharmacy')->name('pharmacy.')->group(function () {
        Route::get('/', [PharmacyController::class, 'index'])
            ->middleware('role:admin,pharmacist,doctor,nurse')
            ->name('index');
        Route::get('/prescriptions/list', [PharmacyController::class, 'prescriptions'])
            ->middleware('role:admin,pharmacist,doctor,nurse')
            ->name('prescriptions');
        Route::post('/prescriptions/{prescription}/dispense', [PharmacyController::class, 'dispense'])
            ->middleware('role:admin,pharmacist')
            ->name('prescriptions.dispense');

        Route::middleware('role:admin,pharmacist')->group(function () {
            Route::get('/create', [PharmacyController::class, 'create'])->name('create');
            Route::post('/', [PharmacyController::class, 'store'])->name('store');
            Route::get('/{medicine}/edit', [PharmacyController::class, 'edit'])->name('edit');
            Route::put('/{medicine}', [PharmacyController::class, 'update'])->name('update');
        });
    });

    // Laboratory Information System (LIS)
    Route::prefix('laboratory')->name('laboratory.')->group(function () {
        Route::get('/', [LaboratoryController::class, 'index'])
            ->middleware('role:admin,laboratorist,doctor,nurse')
            ->name('index');
        Route::get('/requests/create', [LaboratoryController::class, 'createRequest'])
            ->middleware('role:admin,doctor,nurse')
            ->name('requests.create');
        Route::post('/requests', [LaboratoryController::class, 'storeRequest'])
            ->middleware('role:admin,doctor,nurse')
            ->name('requests.store');
        Route::get('/requests/{request}', [LaboratoryController::class, 'show'])
            ->middleware('role:admin,laboratorist,doctor,nurse')
            ->name('show');
        Route::post('/requests/{labRequest}/status', [LaboratoryController::class, 'updateStatus'])
            ->middleware('role:admin,laboratorist')
            ->name('requests.update-status');
        Route::post('/requests/{labRequest}/results', [LaboratoryController::class, 'saveResults'])
            ->middleware('role:admin,laboratorist')
            ->name('requests.save-results');
        Route::get('/test-types', [LaboratoryController::class, 'testTypes'])
            ->middleware('role:admin,laboratorist,doctor,nurse')
            ->name('test-types');
        Route::post('/test-types', [LaboratoryController::class, 'storeTestType'])
            ->middleware('role:admin,laboratorist')
            ->name('test-types.store');
    });

    // Billing & Revenue Cycle Management
    Route::prefix('billing')->name('billing.')->group(function () {
        Route::get('/', [BillingController::class, 'index'])
            ->middleware('role:admin,accountant,receptionist')
            ->name('index');
        Route::get('/create', [BillingController::class, 'create'])
            ->middleware('role:admin,accountant')
            ->name('create');
        Route::post('/', [BillingController::class, 'store'])
            ->middleware('role:admin,accountant')
            ->name('store');
        Route::get('/{invoice}', [BillingController::class, 'show'])
            ->middleware('role:admin,accountant,receptionist')
            ->name('show');
        Route::post('/{invoice}/payment', [BillingController::class, 'recordPayment'])
            ->middleware('role:admin,accountant')
            ->name('payment');
        Route::get('/{invoice}/print', [BillingController::class, 'print'])
            ->middleware('role:admin,accountant,receptionist')
            ->name('print');
    });

    // Admin-Only Modules (Role Guarded)
    Route::middleware('role:admin')->prefix('admin')->name('admin.')->group(function () {
        // Bulk Data Import
        Route::get('/bulk-import', [BulkImportController::class, 'index'])->name('bulk-import.index');
        Route::post('/bulk-import', [BulkImportController::class, 'import'])->name('bulk-import.import');
        Route::get('/bulk-import/template/{entityType}', [BulkImportController::class, 'downloadTemplate'])->name('bulk-import.template');
        Route::get('/bulk-import/{import}', [BulkImportController::class, 'show'])->name('bulk-import.show');

        // Audit Trails
        Route::get('/audit-logs', [AuditLogController::class, 'index'])->name('audit-logs.index');

        // System Settings
        Route::get('/settings', [SettingController::class, 'index'])->name('settings.index');
        Route::post('/settings', [SettingController::class, 'update'])->name('settings.update');
    });
});
