<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Setting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SettingController extends Controller
{
    public function index(): View
    {
        $settings = [
            'hospital_name' => Setting::get('hospital_name', 'Hospital Information System'),
            'hospital_tagline' => Setting::get('hospital_tagline', 'Advanced Clinical & Hospital Management'),
            'hospital_address' => Setting::get('hospital_address', '124 Healthcare Boulevard, Medical City'),
            'hospital_phone' => Setting::get('hospital_phone', '+1 (555) 019-4820'),
            'hospital_email' => Setting::get('hospital_email', 'contact@hospital.local'),
            'currency_symbol' => Setting::get('currency_symbol', '$'),
            'tax_rate_percent' => Setting::get('tax_rate_percent', '5.0'),
            'timezone' => Setting::get('timezone', 'UTC'),
        ];

        return view('admin.settings.index', compact('settings'));
    }

    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'hospital_name' => 'required|string|max:150',
            'hospital_tagline' => 'nullable|string|max:200',
            'hospital_address' => 'required|string|max:250',
            'hospital_phone' => 'required|string|max:50',
            'hospital_email' => 'required|email|max:100',
            'currency_symbol' => 'required|string|max:5',
            'tax_rate_percent' => 'required|numeric|between:0,100',
            'timezone' => 'required|string|max:50',
        ]);

        foreach ($validated as $key => $value) {
            Setting::set($key, (string) $value);
        }

        AuditLog::log('UPDATE', 'Settings', 'Updated hospital enterprise configuration');

        return back()->with('success', 'Hospital configuration settings updated successfully.');
    }
}
