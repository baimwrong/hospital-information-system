<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Patient;
use App\Models\Payment;
use App\Models\Setting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class BillingController extends Controller
{
    public function index(Request $request): View
    {
        $query = Invoice::with(['patient', 'generatedBy']);

        if ($status = $request->input('payment_status')) {
            $query->where('payment_status', $status);
        }

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('invoice_number', 'like', "%{$search}%")
                    ->orWhereHas('patient', function ($pq) use ($search) {
                        $pq->where('first_name', 'like', "%{$search}%")
                            ->orWhere('last_name', 'like', "%{$search}%")
                            ->orWhere('mrn', 'like', "%{$search}%");
                    });
            });
        }

        $invoices = $query->latest('invoice_date')->paginate(15)->withQueryString();

        $totalBilled = Invoice::sum('total_amount');
        $totalCollected = Invoice::sum('paid_amount');
        $totalOutstanding = Invoice::sum('balance_due');

        return view('billing.index', compact('invoices', 'totalBilled', 'totalCollected', 'totalOutstanding'));
    }

    public function create(Request $request): View
    {
        $patients = Patient::where('status', 'active')->orderBy('first_name')->get();
        $nextNumber = Invoice::generateNumber();

        $selectedPatient = null;
        if ($pId = $request->input('patient_id')) {
            $selectedPatient = Patient::find($pId);
        }

        return view('billing.create', compact('patients', 'nextNumber', 'selectedPatient'));
    }

    public function store(Request $request): RedirectResponse
    {
        if ($request->has('items') && is_array($request->input('items'))) {
            $filtered = array_values(array_filter($request->input('items'), function ($item) {
                return ! empty($item['description']) && isset($item['unit_price']) && $item['unit_price'] !== '';
            }));
            $request->merge(['items' => $filtered]);
        }

        $validated = $request->validate([
            'patient_id' => 'required|exists:patients,id',
            'invoice_date' => 'required|date',
            'due_date' => 'nullable|date',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.description' => 'required|string',
            'items.*.item_type' => 'required|string',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.unit_price' => 'required|numeric|min:0',
            'discount_amount' => 'nullable|numeric|min:0',
            'tax_amount' => 'nullable|numeric|min:0',
        ]);

        DB::beginTransaction();

        try {
            $total = 0.00;
            $itemsData = [];

            foreach ($validated['items'] as $item) {
                $qty = (int) $item['quantity'];
                $price = (float) $item['unit_price'];
                $subtotal = $qty * $price;
                $total += $subtotal;

                $itemsData[] = [
                    'item_type' => $item['item_type'],
                    'description' => $item['description'],
                    'quantity' => $qty,
                    'unit_price' => $price,
                    'subtotal' => $subtotal,
                ];
            }

            $discount = (float) ($validated['discount_amount'] ?? 0);
            $tax = (float) ($validated['tax_amount'] ?? 0);
            $finalTotal = max(0, $total - $discount + $tax);

            $invoice = Invoice::create([
                'invoice_number' => Invoice::generateNumber(),
                'patient_id' => $validated['patient_id'],
                'total_amount' => $finalTotal,
                'discount_amount' => $discount,
                'tax_amount' => $tax,
                'paid_amount' => 0.00,
                'balance_due' => $finalTotal,
                'payment_status' => 'unpaid',
                'invoice_date' => $validated['invoice_date'],
                'due_date' => $validated['due_date'] ?? now()->addDays(7)->toDateString(),
                'notes' => $validated['notes'] ?? null,
                'generated_by_id' => Auth::id(),
            ]);

            foreach ($itemsData as $item) {
                $item['invoice_id'] = $invoice->id;
                InvoiceItem::create($item);
            }

            DB::commit();

            AuditLog::log('CREATE', 'Billing', "Generated custom hospital invoice {$invoice->invoice_number} for {$finalTotal}", (string) $invoice->id);

            return redirect()->route('billing.show', $invoice)
                ->with('success', "Invoice {$invoice->invoice_number} created successfully.");
        } catch (\Throwable $e) {
            DB::rollBack();

            return back()->withInput()->withErrors(['error' => 'Failed to generate invoice: '.$e->getMessage()]);
        }
    }

    public function show(Invoice $invoice): View
    {
        $invoice->load(['patient', 'items', 'generatedBy', 'appointment.doctor.user', 'admission.bed.ward', 'payments.recordedBy']);
        $hospitalName = Setting::get('hospital_name', 'General Hospital');
        $hospitalAddress = Setting::get('hospital_address', '124 Healthcare Boulevard, Medical District');
        $hospitalPhone = Setting::get('hospital_phone', '+1 (555) 019-4820');
        $currencySymbol = Setting::get('currency_symbol', '$');

        return view('billing.show', compact('invoice', 'hospitalName', 'hospitalAddress', 'hospitalPhone', 'currencySymbol'));
    }

    public function recordPayment(Request $request, Invoice $invoice): RedirectResponse
    {
        $validated = $request->validate([
            'amount' => 'required|numeric|min:0.01|max:'.$invoice->balance_due,
            'payment_method' => 'required|in:cash,credit_card,debit_card,bank_transfer,insurance',
            'transaction_reference' => 'nullable|string|max:100',
            'notes' => 'nullable|string',
        ]);

        DB::beginTransaction();

        try {
            $lockedInvoice = Invoice::where('id', $invoice->id)->lockForUpdate()->firstOrFail();

            $amount = (float) $validated['amount'];
            if ($amount > (float) $lockedInvoice->balance_due) {
                throw new \Exception('Payment amount cannot exceed remaining balance due.');
            }

            $payment = Payment::create([
                'payment_number' => Payment::generateNumber(),
                'invoice_id' => $lockedInvoice->id,
                'patient_id' => $lockedInvoice->patient_id,
                'amount' => $amount,
                'payment_method' => $validated['payment_method'],
                'transaction_reference' => $validated['transaction_reference'] ?? null,
                'notes' => $validated['notes'] ?? null,
                'recorded_by_id' => Auth::id(),
            ]);

            $lockedInvoice->paid_amount += $amount;
            $lockedInvoice->balance_due = max(0, $lockedInvoice->total_amount - $lockedInvoice->paid_amount);
            $lockedInvoice->payment_method = $validated['payment_method'];

            if ($lockedInvoice->balance_due <= 0) {
                $lockedInvoice->payment_status = 'paid';
            } else {
                $lockedInvoice->payment_status = 'partial';
            }

            if (! empty($validated['notes'])) {
                $lockedInvoice->notes = ($lockedInvoice->notes ? $lockedInvoice->notes."\n" : '').'Payment '.$payment->payment_number.': '.$amount.' via '.$validated['payment_method'].' ('.now()->format('Y-m-d H:i').')';
            }

            $lockedInvoice->save();

            DB::commit();

            AuditLog::log('PAYMENT', 'Billing', "Recorded payment {$payment->payment_number} of {$amount} on invoice {$lockedInvoice->invoice_number} via {$validated['payment_method']}", (string) $payment->id);

            return back()->with('success', "Payment {$payment->payment_number} of {$amount} recorded. Remaining balance: {$lockedInvoice->balance_due}.");
        } catch (\Throwable $e) {
            DB::rollBack();

            return back()->withErrors(['error' => 'Payment failed: '.$e->getMessage()]);
        }
    }

    public function print(Invoice $invoice): View
    {
        $invoice->load(['patient', 'items', 'generatedBy']);
        $hospitalName = Setting::get('hospital_name', 'General Hospital');
        $hospitalAddress = Setting::get('hospital_address', '124 Healthcare Boulevard, Medical District');
        $hospitalPhone = Setting::get('hospital_phone', '+1 (555) 019-4820');
        $hospitalEmail = Setting::get('hospital_email', 'billing@hospital.local');
        $currencySymbol = Setting::get('currency_symbol', '$');

        return view('billing.print', compact('invoice', 'hospitalName', 'hospitalAddress', 'hospitalPhone', 'hospitalEmail', 'currencySymbol'));
    }
}
