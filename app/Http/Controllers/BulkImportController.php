<?php

namespace App\Http\Controllers;

use App\Models\BulkImport;
use App\Services\BulkImportService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;

class BulkImportController extends Controller
{
    public function __construct(
        protected BulkImportService $importService
    ) {}

    public function index(): View
    {
        $imports = BulkImport::with('user')->latest()->paginate(15);

        $entities = [
            'patients' => [
                'title' => 'Patients Master Data',
                'description' => 'Import patient demographics, contact numbers, national IDs, blood types, emergency contacts, and known allergies.',
                'icon' => 'bi-people',
                'color' => 'primary',
            ],
            'doctors' => [
                'title' => 'Physicians & Specialists',
                'description' => 'Bulk register licensed medical practitioners, credentials, department assignments, and consultation fees.',
                'icon' => 'bi-person-badge',
                'color' => 'success',
            ],
            'medicines' => [
                'title' => 'Pharmacy & Inventory',
                'description' => 'Import drug formulary, generic names, batch numbers, unit pricing, dosage forms, and stock quantities.',
                'icon' => 'bi-capsule',
                'color' => 'info',
            ],
            'lab_test_types' => [
                'title' => 'Laboratory Test Catalog',
                'description' => 'Import pathology/radiology test catalog, standard reference intervals, specimen types, and tariff pricing.',
                'icon' => 'bi-clipboard2-pulse',
                'color' => 'warning',
            ],
            'departments' => [
                'title' => 'Clinical Departments',
                'description' => 'Import clinical and administrative departments with department codes.',
                'icon' => 'bi-diagram-3',
                'color' => 'secondary',
            ],
        ];

        return view('admin.bulk-import.index', compact('imports', 'entities'));
    }

    public function import(Request $request): RedirectResponse
    {
        $request->validate([
            'entity_type' => 'required|in:patients,doctors,medicines,lab_test_types,departments',
            'file' => 'required|file|mimes:csv,txt|max:10240', // Max 10MB
        ]);

        $result = $this->importService->import(
            $request->file('file'),
            $request->input('entity_type'),
            Auth::id()
        );

        $import = $result['import'];

        if ($import->status === 'completed') {
            return redirect()->route('admin.bulk-import.index')
                ->with('success', "Bulk import successful! {$import->successful_rows} rows imported.");
        } elseif ($import->status === 'completed_with_errors') {
            return redirect()->route('admin.bulk-import.show', $import)
                ->with('warning', "Bulk import completed with errors: {$import->successful_rows} imported, {$import->failed_rows} failed.");
        } else {
            return redirect()->route('admin.bulk-import.show', $import)
                ->with('error', "Bulk import failed: {$result['message']}");
        }
    }

    public function downloadTemplate(string $entityType): Response
    {
        $validTypes = ['patients', 'doctors', 'medicines', 'lab_test_types', 'departments'];
        if (! in_array($entityType, $validTypes, true)) {
            abort(404, 'Invalid template type.');
        }

        $csvContent = $this->importService->generateTemplate($entityType);
        $filename = "hospital_{$entityType}_template.csv";

        return response($csvContent, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }

    public function show(BulkImport $import): View
    {
        $import->load('user');

        return view('admin.bulk-import.show', compact('import'));
    }
}
