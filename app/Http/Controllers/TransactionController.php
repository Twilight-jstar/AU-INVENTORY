<?php

namespace App\Http\Controllers;

use App\Models\{Item, StockIn, StockOut, Department, Category};
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\{DB, Auth};
use Barryvdh\DomPDF\Facade\Pdf;

class TransactionController extends Controller
{
    /**
     * Optimized Index with Pagination and Limited Columns
     */
    public function index()
    {
        // Query Optimization: Kumuha lang ng kailangang columns at limitahan ang records
        $stockIn = StockIn::with(['item:id,name,category_id', 'item.category:id,name'])
            ->select('id', 'ref_no', 'item_id', 'quantity', 'received_by', 'supplier_name', 'created_at')
            ->latest()
            ->limit(100)
            ->get()
            ->map(fn($record) => [
                'id' => $record->ref_no,
                'raw_id' => 'in-' . $record->id,
                'created_at' => $record->created_at,
                'item' => $record->item,
                'type' => 'In',
                'quantity' => $record->quantity,
                'received_by' => $record->received_by,
                'recorded_by' => $record->received_by,
                'released_to' => null,
                'department' => null,
                'note' => "Supplier: " . ($record->supplier_name ?? 'N/A'),
            ]);

        $stockOut = StockOut::with(['item:id,name,category_id', 'item.category:id,name'])
            ->select('id', 'ref_no', 'item_id', 'quantity', 'department', 'released_to', 'released_by', 'purpose', 'created_at')
            ->latest()
            ->limit(100)
            ->get()
            ->map(fn($record) => [
                'id' => $record->ref_no,
                'raw_id' => 'out-' . $record->id,
                'created_at' => $record->created_at,
                'item' => $record->item,
                'type' => 'Out',
                'quantity' => $record->quantity,
                'department' => $record->department,
                'released_to' => $record->released_to,
                'released_by' => $record->released_by,
                'recorded_by' => $record->released_by,
                'note' => $record->purpose ?? 'Release',
            ]);

        $mergedTransactions = $stockIn->concat($stockOut)
            ->sortByDesc('created_at')
            ->values();

        return Inertia::render('Transactions/Index', [
            'transactions' => $mergedTransactions,
            'departments' => Department::where('is_active', true)->orderBy('name')->get(['id', 'name']),
            'categories' => Category::orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function stockIn()
    {
        return Inertia::render('Transactions/StockIn', [
            'items' => Item::orderBy('name')->get(['id', 'name', 'product_code']),
        ]);
    }

    public function stockOut()
    {
        return Inertia::render('Transactions/StockOut', [
            'items' => Item::where('quantity', '>', 0)->orderBy('name')->get(['id', 'name', 'quantity', 'product_code']),
            'departments' => Department::where('is_active', true)->orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function store_bulk_in(Request $request)
    {
        $request->validate([
            'date_received' => 'required|date',
            'line_items' => 'required|array|min:1',
            'line_items.*.item_id' => 'required|exists:items,id',
            'line_items.*.quantity' => 'required|numeric|min:0.1',
        ]);

        $lastId = null;

        DB::transaction(function () use ($request, &$lastId) {
            foreach ($request->line_items as $itemData) {
                $record = StockIn::create([
                    'item_id' => $itemData['item_id'],
                    'quantity' => $itemData['quantity'],
                    'supplier_name' => $request->supplier_name,
                    'date_received' => $request->date_received,
                    'received_by' => Auth::user()->name,
                    'ref_no' => 'IN-' . now()->format('ymd') . '-' . str_pad($itemData['item_id'], 4, '0', STR_PAD_LEFT),
                ]);
                $lastId = $record->id;
                Item::findOrFail($itemData['item_id'])->increment('quantity', $itemData['quantity']);
            }
        });

        return back()->with(['success' => 'Stock In recorded.', 'export_id' => 'in-' . $lastId]);
    }

    /**
     * Stock Out with Critical Validation
     */
    public function store_bulk_out(Request $request)
    {
        $request->validate([
            'department' => 'required',
            'released_to' => 'required|string',
            'date_released' => 'required|date',
            'line_items' => 'required|array|min:1',
            'line_items.*.item_id' => 'required|exists:items,id',
            'line_items.*.quantity' => 'required|numeric|min:0.1',
        ]);

        $lowStockItems = [];
        $lastId = null;

        try {
            DB::transaction(function () use ($request, &$lowStockItems, &$lastId) {
                foreach ($request->line_items as $itemData) {
                    // Critical Validation: Lock row for update and check stock
                    $item = Item::lockForUpdate()->findOrFail($itemData['item_id']);

                    if ($item->quantity < $itemData['quantity']) {
                        throw new \Exception("Insufficient stock for {$item->name}. Current stock: {$item->quantity}");
                    }

                    $record = StockOut::create([
                        'item_id' => $itemData['item_id'],
                        'quantity' => $itemData['quantity'],
                        'department' => $request->department,
                        'date_released' => $request->date_released,
                        'released_by' => Auth::user()->name,
                        'released_to' => $request->released_to,
                        'purpose' => $request->purpose ?? 'Standard Issuance',
                        'ref_no' => 'OUT-' . now()->format('ymd') . '-' . str_pad($item->id, 4, '0', STR_PAD_LEFT),
                    ]);
                    
                    $lastId = $record->id;
                    $item->decrement('quantity', $itemData['quantity']);

                    // Threshold Check
                    $threshold = ($item->min_stock > 0) ? $item->min_stock : 10;
                    if ($item->refresh()->quantity <= $threshold) {
                        $lowStockItems[] = "{$item->name} ({$item->quantity} left)";
                    }
                }
            });
        } catch (\Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }

        $payload = ['success' => 'Stock Out recorded.', 'export_id' => 'out-' . $lastId];
        if (!empty($lowStockItems)) {
            $payload['warning'] = "Low stock detected: " . implode(', ', $lowStockItems);
        }

        return back()->with($payload);
    }

    /**
     * DRY PDF Generator Helper
     */
    private function generatePdfReport($transactions, $title, $receiver = null, $date = null)
    {
        if ($transactions->isEmpty()) return null;

        return Pdf::loadView('pdf.bulk_transactions', [
            'transactions' => $transactions,
            'title' => $title,
            'received_by_name' => $receiver,
            'received_by_date' => $date
        ])->setPaper('letter', 'portrait');
    }

    public function exportDailyIn(Request $request)
    {
        $request->validate(['date' => 'required|date']);
        
        $transactions = StockIn::whereDate('date_received', $request->date)
            ->with(['item.unit'])->get()->map(fn($trx) => (object)[
                'ref_no' => $trx->ref_no,
                'date' => $trx->date_received,
                'total_quantity' => $trx->quantity,
                'item' => $trx->item,
                'combined_remarks' => "Supplier: " . ($trx->supplier_name ?? 'N/A'),
                'received_by' => $trx->received_by
            ]);

        $pdf = $this->generatePdfReport($transactions, "DAILY STOCK IN REPORT");
        return $pdf ? $pdf->stream("Daily-In-{$request->date}.pdf") : back()->with('error', 'No records found.');
    }

    public function exportByDepartment(Request $request)
    {
        $request->validate(['department' => 'required|string']);
        
        $transactions = StockOut::where('department', $request->department)
            ->with(['item.unit'])->get()->map(fn($trx) => (object)[
                'ref_no' => $trx->ref_no,
                'date' => $trx->date_released,
                'total_quantity' => $trx->quantity, 
                'item' => $trx->item,
                'combined_remarks' => $trx->purpose ?? "",
                'released_by' => $trx->released_by,
                'released_to' => $trx->released_to
            ]);

        if ($transactions->isEmpty()) return back()->with('error', 'No records found.');

        $last = $transactions->last();
        $pdf = $this->generatePdfReport($transactions, "STOCK ISSUANCE FORM", $last->released_to, $last->date);
        return $pdf->stream("Dept-{$request->department}.pdf");
    }

    public function exportPdf($id)
    {
        $type = str_starts_with($id, 'in-') ? 'in' : 'out';
        $realId = str_replace(['in-', 'out-'], '', $id);

        if ($type === 'in') {
            $trx = StockIn::with(['item.unit'])->findOrFail($realId);
            $data = collect([(object)[
                'ref_no' => $trx->ref_no,
                'date' => $trx->date_received,
                'total_quantity' => $trx->quantity,
                'item' => $trx->item,
                'received_by' => $trx->received_by,
                'combined_remarks' => "Supplier: " . ($trx->supplier_name ?? 'N/A')
            ]]);
            return $this->generatePdfReport($data, "STOCK-IN SLIP")->stream("In-{$realId}.pdf");
        } else {
            $trx = StockOut::with(['item.unit'])->findOrFail($realId);
            $data = collect([(object)[
                'ref_no' => $trx->ref_no,
                'date' => $trx->date_released,
                'total_quantity' => $trx->quantity,
                'item' => $trx->item,
                'released_by' => $trx->released_by,
                'released_to' => $trx->released_to,
                'combined_remarks' => "Purpose: " . ($trx->purpose ?? "Standard Issuance")
            ]]);
            return $this->generatePdfReport($data, "STOCK ISSUANCE FORM", $trx->released_to, $trx->date_released)->stream("Out-{$realId}.pdf");
        }
    }
}