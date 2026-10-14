<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Doctor;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\LabRequest;
use App\Models\LabTestResult;
use App\Models\LabTestType;
use App\Models\Patient;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class LaboratoryController extends Controller
{
    public function index(Request $request): View
    {
        $query = LabRequest::with(['patient', 'doctor.user', 'technician', 'results.labTestType']);

        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        if ($priority = $request->input('priority')) {
            $query->where('priority', $priority);
        }

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('request_number', 'like', "%{$search}%")
                    ->orWhereHas('patient', function ($pq) use ($search) {
                        $pq->where('first_name', 'like', "%{$search}%")
                            ->orWhere('last_name', 'like', "%{$search}%")
                            ->orWhere('mrn', 'like', "%{$search}%");
                    });
            });
        }

        $requests = $query->latest()->paginate(15)->withQueryString();
        $pendingCount = LabRequest::whereIn('status', ['pending', 'sample_collected'])->count();

        return view('laboratory.index', compact('requests', 'pendingCount'));
    }

    public function createRequest(Request $request): View
    {
        $patients = Patient::where('status', 'active')->orderBy('first_name')->get();
        $doctors = Doctor::with('user')->where('status', 'active')->get();
        $testTypes = LabTestType::where('is_active', true)->orderBy('name')->get();
        $nextNumber = LabRequest::generateNumber();

        $selectedPatient = null;
        if ($pId = $request->input('patient_id')) {
            $selectedPatient = Patient::find($pId);
        }

        return view('laboratory.create-request', compact('patients', 'doctors', 'testTypes', 'nextNumber', 'selectedPatient'));
    }

    public function storeRequest(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'patient_id' => 'required|exists:patients,id',
            'doctor_id' => 'required|exists:doctors,id',
            'priority' => 'required|in:routine,urgent,stat',
            'clinical_notes' => 'nullable|string',
            'test_type_ids' => 'required|array|min:1',
            'test_type_ids.*' => 'exists:lab_test_types,id',
        ]);

        DB::beginTransaction();

        try {
            $labRequest = LabRequest::create([
                'request_number' => LabRequest::generateNumber(),
                'patient_id' => $validated['patient_id'],
                'doctor_id' => $validated['doctor_id'],
                'priority' => $validated['priority'],
                'clinical_notes' => $validated['clinical_notes'] ?? null,
                'status' => 'pending',
            ]);

            $totalLabCost = 0.00;
            $itemsForInvoice = [];

            foreach ($validated['test_type_ids'] as $testTypeId) {
                $testType = LabTestType::findOrFail($testTypeId);
                LabTestResult::create([
                    'lab_request_id' => $labRequest->id,
                    'lab_test_type_id' => $testType->id,
                    'parameter_name' => $testType->name,
                    'result_value' => 'Pending Sample',
                    'unit' => '-',
                    'reference_range' => $testType->reference_range ?? 'Standard interval',
                    'flag' => 'normal',
                ]);

                $totalLabCost += (float) $testType->price;
                $itemsForInvoice[] = [
                    'description' => "Lab Investigation: {$testType->name}",
                    'quantity' => 1,
                    'unit_price' => $testType->price,
                    'subtotal' => $testType->price,
                ];
            }

            // Create invoice for laboratory tests
            if ($totalLabCost > 0) {
                $invoice = Invoice::create([
                    'invoice_number' => Invoice::generateNumber(),
                    'patient_id' => $labRequest->patient_id,
                    'total_amount' => $totalLabCost,
                    'paid_amount' => 0.00,
                    'balance_due' => $totalLabCost,
                    'payment_status' => 'unpaid',
                    'invoice_date' => now()->toDateString(),
                    'due_date' => now()->addDays(5)->toDateString(),
                    'generated_by_id' => Auth::id(),
                    'notes' => 'Laboratory investigation fee for '.$labRequest->request_number,
                ]);

                foreach ($itemsForInvoice as $item) {
                    InvoiceItem::create([
                        'invoice_id' => $invoice->id,
                        'item_type' => 'lab_test',
                        'description' => $item['description'],
                        'quantity' => $item['quantity'],
                        'unit_price' => $item['unit_price'],
                        'subtotal' => $item['subtotal'],
                    ]);
                }
            }

            DB::commit();

            AuditLog::log('CREATE', 'LIS', "Created lab order {$labRequest->request_number} with ".count($validated['test_type_ids']).' test(s)', (string) $labRequest->id);

            return redirect()->route('laboratory.show', $labRequest)
                ->with('success', "Laboratory request {$labRequest->request_number} registered successfully.");
        } catch (\Throwable $e) {
            DB::rollBack();

            return back()->withInput()->withErrors(['error' => 'Failed to create lab request: '.$e->getMessage()]);
        }
    }

    public function show(LabRequest $request): View
    {
        $request->load(['patient.vitals', 'doctor.user', 'technician', 'results.labTestType']);

        return view('laboratory.show', compact('request'));
    }

    public function updateStatus(Request $request, LabRequest $labRequest): RedirectResponse
    {
        $validated = $request->validate([
            'status' => 'required|in:pending,sample_collected,processing,completed,cancelled',
        ]);

        $updates = ['status' => $validated['status']];

        if ($validated['status'] === 'sample_collected' && ! $labRequest->collected_at) {
            $updates['collected_at'] = now();
            $updates['technician_id'] = Auth::id();
        }

        $labRequest->update($updates);

        AuditLog::log('UPDATE', 'LIS', "Updated lab request {$labRequest->request_number} status to {$validated['status']}", (string) $labRequest->id);

        return back()->with('success', "Lab order status updated to {$validated['status']}.");
    }

    public function saveResults(Request $request, LabRequest $labRequest): RedirectResponse
    {
        $validated = $request->validate([
            'results' => 'required|array',
            'results.*.id' => 'required|exists:lab_test_results,id',
            'results.*.result_value' => 'required|string',
            'results.*.unit' => 'nullable|string',
            'results.*.flag' => 'required|in:normal,high,low,critical',
            'results.*.comments' => 'nullable|string',
        ]);

        foreach ($validated['results'] as $resData) {
            $result = LabTestResult::find($resData['id']);
            if ($result && $result->lab_request_id === $labRequest->id) {
                $result->update([
                    'result_value' => $resData['result_value'],
                    'unit' => $resData['unit'] ?? $result->unit,
                    'flag' => $resData['flag'],
                    'comments' => $resData['comments'] ?? null,
                ]);
            }
        }

        $labRequest->update([
            'status' => 'completed',
            'completed_at' => now(),
            'technician_id' => Auth::id(),
        ]);

        AuditLog::log('UPDATE', 'LIS', "Completed and recorded lab analysis results for {$labRequest->request_number}", (string) $labRequest->id);

        return back()->with('success', 'Laboratory findings and analysis recorded. Order marked as completed.');
    }

    public function testTypes(): View
    {
        $testTypes = LabTestType::orderBy('category')->orderBy('name')->paginate(20);

        return view('laboratory.test-types', compact('testTypes'));
    }

    public function storeTestType(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'test_code' => 'required|string|max:50|unique:lab_test_types,test_code',
            'name' => 'required|string|max:150',
            'category' => 'required|string|max:100',
            'price' => 'required|numeric|min:0',
            'sample_type' => 'nullable|string|max:100',
            'reference_range' => 'nullable|string',
            'turnaround_time_hours' => 'required|integer|min:1',
        ]);

        $tt = LabTestType::create($validated);

        AuditLog::log('CREATE', 'LIS', "Added new test to catalog: {$tt->name} ({$tt->test_code})", (string) $tt->id);

        return back()->with('success', "Test {$tt->name} added to catalog.");
    }
}
