<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Medicine;
use App\Models\Prescription;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class PharmacyController extends Controller
{
    public function index(Request $request): View
    {
        $query = Medicine::query();

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('brand_name', 'like', "%{$search}%")
                    ->orWhere('generic_name', 'like', "%{$search}%")
                    ->orWhere('code', 'like', "%{$search}%")
                    ->orWhere('category', 'like', "%{$search}%");
            });
        }

        if ($cat = $request->input('category')) {
            $query->where('category', $cat);
        }

        if ($filter = $request->input('stock_status')) {
            if ($filter === 'low') {
                $query->whereColumn('stock_quantity', '<=', 'min_stock_alert')->where('stock_quantity', '>', 0);
            } elseif ($filter === 'out') {
                $query->where('stock_quantity', '<=', 0);
            } elseif ($filter === 'expired') {
                $query->where('expiry_date', '<', now());
            }
        }

        $medicines = $query->orderBy('brand_name')->paginate(15)->withQueryString();
        $categories = Medicine::distinct()->pluck('category')->filter();

        $lowStockCount = Medicine::whereColumn('stock_quantity', '<=', 'min_stock_alert')->count();
        $outOfStockCount = Medicine::where('stock_quantity', '<=', 0)->count();

        return view('pharmacy.index', compact('medicines', 'categories', 'lowStockCount', 'outOfStockCount'));
    }

    public function create(): View
    {
        $nextCode = Medicine::generateCode();

        return view('pharmacy.create', compact('nextCode'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'code' => 'required|string|unique:medicines,code',
            'brand_name' => 'required|string|max:150',
            'generic_name' => 'required|string|max:150',
            'category' => 'required|string|max:60',
            'dosage_form' => 'required|string|max:40',
            'strength' => 'required|string|max:50',
            'unit_price' => 'required|numeric|min:0',
            'purchase_cost' => 'nullable|numeric|min:0',
            'stock_quantity' => 'required|integer|min:0',
            'min_stock_alert' => 'required|integer|min:0',
            'batch_number' => 'nullable|string|max:50',
            'expiry_date' => 'nullable|date',
            'manufacturer' => 'nullable|string|max:150',
            'storage_condition' => 'nullable|string|max:100',
        ]);

        $validated['purchase_cost'] = $validated['purchase_cost'] ?? ($validated['unit_price'] * 0.7);
        $validated['status'] = $validated['stock_quantity'] > 0 ? 'in_stock' : 'out_of_stock';

        $med = Medicine::create($validated);

        AuditLog::log('CREATE', 'Pharmacy', "Added medicine {$med->brand_name} ({$med->code}) to inventory", (string) $med->id);

        return redirect()->route('pharmacy.index')
            ->with('success', "Medicine {$med->brand_name} added to pharmacy inventory.");
    }

    public function edit(Medicine $medicine): View
    {
        return view('pharmacy.edit', compact('medicine'));
    }

    public function update(Request $request, Medicine $medicine): RedirectResponse
    {
        $validated = $request->validate([
            'brand_name' => 'required|string|max:150',
            'generic_name' => 'required|string|max:150',
            'category' => 'required|string|max:60',
            'dosage_form' => 'required|string|max:40',
            'strength' => 'required|string|max:50',
            'unit_price' => 'required|numeric|min:0',
            'purchase_cost' => 'nullable|numeric|min:0',
            'stock_quantity' => 'required|integer|min:0',
            'min_stock_alert' => 'required|integer|min:0',
            'batch_number' => 'nullable|string|max:50',
            'expiry_date' => 'nullable|date',
            'manufacturer' => 'nullable|string|max:150',
            'storage_condition' => 'nullable|string|max:100',
        ]);

        $validated['status'] = $validated['stock_quantity'] > 0 ? 'in_stock' : 'out_of_stock';
        $medicine->update($validated);

        AuditLog::log('UPDATE', 'Pharmacy', "Updated medicine item {$medicine->brand_name} stock: {$medicine->stock_quantity}", (string) $medicine->id);

        return redirect()->route('pharmacy.index')->with('success', 'Medicine inventory record updated.');
    }

    public function prescriptions(Request $request): View
    {
        $query = Prescription::with(['patient', 'doctor.user', 'items.medicine']);

        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        $prescriptions = $query->latest()->paginate(15)->withQueryString();

        return view('pharmacy.prescriptions', compact('prescriptions'));
    }

    public function dispense(Request $request, Prescription $prescription): RedirectResponse
    {
        if ($prescription->status === 'dispensed') {
            return back()->with('info', 'This prescription has already been dispensed.');
        }

        DB::beginTransaction();

        try {
            $totalMedicineCost = 0.00;
            $dispensedItems = [];

            foreach ($prescription->items as $item) {
                $med = $item->medicine;
                if ($med->stock_quantity < $item->quantity_prescribed) {
                    throw new \Exception("Insufficient stock for {$med->brand_name}. Current available stock: {$med->stock_quantity}. Required: {$item->quantity_prescribed}.");
                }

                $med->decrement('stock_quantity', $item->quantity_prescribed);
                if ($med->stock_quantity <= 0) {
                    $med->update(['status' => 'out_of_stock']);
                }

                $item->update([
                    'quantity_dispensed' => $item->quantity_prescribed,
                    'status' => 'dispensed',
                ]);

                $subtotal = (float) $item->subtotal;
                $totalMedicineCost += $subtotal;
                $dispensedItems[] = [
                    'description' => "Medication: {$med->brand_name} ({$item->dosage}) x{$item->quantity_prescribed}",
                    'quantity' => $item->quantity_prescribed,
                    'unit_price' => $item->unit_price,
                    'subtotal' => $subtotal,
                ];
            }

            $prescription->update([
                'status' => 'dispensed',
                'dispensed_by_id' => Auth::id(),
                'dispensed_at' => now(),
            ]);

            // Generate invoice for dispensed pharmacy items
            if ($totalMedicineCost > 0) {
                $invoice = Invoice::create([
                    'invoice_number' => Invoice::generateNumber(),
                    'patient_id' => $prescription->patient_id,
                    'total_amount' => $totalMedicineCost,
                    'paid_amount' => 0.00,
                    'balance_due' => $totalMedicineCost,
                    'payment_status' => 'unpaid',
                    'invoice_date' => now()->toDateString(),
                    'due_date' => now()->addDays(5)->toDateString(),
                    'generated_by_id' => Auth::id(),
                    'notes' => 'Prescription fulfillment '.$prescription->prescription_number,
                ]);

                foreach ($dispensedItems as $dItem) {
                    InvoiceItem::create([
                        'invoice_id' => $invoice->id,
                        'item_type' => 'medication',
                        'description' => $dItem['description'],
                        'quantity' => $dItem['quantity'],
                        'unit_price' => $dItem['unit_price'],
                        'subtotal' => $dItem['subtotal'],
                    ]);
                }
            }

            DB::commit();

            AuditLog::log('DISPENSE', 'Pharmacy', "Dispensed prescription {$prescription->prescription_number} for patient #{$prescription->patient_id}", (string) $prescription->id);

            return back()->with('success', "Prescription {$prescription->prescription_number} dispensed successfully. Invoice generated.");
        } catch (\Throwable $e) {
            DB::rollBack();

            return back()->withErrors(['error' => 'Dispense failed: '.$e->getMessage()]);
        }
    }
}
