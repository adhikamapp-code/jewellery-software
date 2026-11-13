<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Category;
use App\Models\Consumable;
use App\Models\Counter;
use App\Models\DiamondStone;
use App\Models\HallmarkRecord;
use App\Models\InventoryItem;
use App\Models\LabelPrintLog;
use App\Models\Product;
use App\Models\StockAdjustment;
use App\Models\StockMovement;
use App\Models\StockTransfer;
use App\Models\StockTransferItem;
use App\Models\Supplier;
use App\Services\AuditService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class ProductInventoryController extends Controller
{
    public function products(Request $request)
    {
        $products = Product::with(['category', 'supplier'])
            ->withCount(['inventoryItems as available_count' => fn ($q) => $q->where('status', 'available')])
            ->when($request->search, fn ($q, $s) => $q->where('name', 'like', "%{$s}%")->orWhere('sku', 'like', "%{$s}%"))
            ->latest()
            ->paginate(20);

        $categories = Category::all();
        $suppliers = Supplier::where('is_active', true)->get();

        return view('inventory.products', compact('products', 'categories', 'suppliers'));
    }

    public function storeProduct(Request $request)
    {
        $data = $request->validate([
            'sku' => 'required|string|unique:products,sku',
            'name' => 'required|string',
            'category_id' => 'required|exists:categories,id',
            'product_type' => 'required|string',
            'metal_type' => 'required|string',
            'purity' => 'required|string',
            'making_charge_type' => 'required|in:per_gram,percentage,fixed',
            'making_charge_value' => 'required|numeric|min:0',
            'wastage_pct' => 'nullable|numeric|min:0',
            'gst_rate' => 'nullable|numeric|min:0',
            'supplier_id' => 'nullable|exists:suppliers,id',
            'description' => 'nullable|string',
        ]);

        $product = Product::create($data + ['gst_rate' => $data['gst_rate'] ?? 3.0, 'wastage_pct' => $data['wastage_pct'] ?? 0]);
        AuditService::log('Products', 'create', "Created product {$product->sku} - {$product->name}", $product);

        return back()->with('success', 'Product master created.');
    }

    public function inventory(Request $request)
    {
        $items = InventoryItem::with(['product', 'branch', 'counter', 'supplier'])
            ->when($request->branch_id, fn ($q, $b) => $q->where('branch_id', $b))
            ->when($request->status, fn ($q, $s) => $q->where('status', $s))
            ->when($request->metal_type, fn ($q, $m) => $q->whereHas('product', fn ($p) => $p->where('metal_type', $m)))
            ->when($request->search, fn ($q, $s) => $q->where(fn ($w) => $w->where('item_code', 'like', "%{$s}%")->orWhere('barcode', 'like', "%{$s}%")->orWhere('huid', 'like', "%{$s}%")))
            ->latest()
            ->paginate(25);

        $products = Product::where('status', 'active')->get();
        $branches = Branch::where('is_active', true)->get();
        $counters = Counter::where('is_active', true)->get();
        $suppliers = Supplier::where('is_active', true)->get();
        $transfers = StockTransfer::with(['fromBranch', 'toBranch', 'items.inventoryItem.product'])->latest()->take(10)->get();
        $consumables = Consumable::with('branch')->get();

        return view('inventory.items', compact('items', 'products', 'branches', 'counters', 'suppliers', 'transfers', 'consumables'));
    }

    public function storeItem(Request $request)
    {
        $data = $request->validate([
            'product_id' => 'required|exists:products,id',
            'branch_id' => 'required|exists:branches,id',
            'counter_id' => 'nullable|exists:counters,id',
            'supplier_id' => 'nullable|exists:suppliers,id',
            'gross_weight' => 'required|numeric|min:0.001',
            'stone_weight' => 'nullable|numeric|min:0',
            'diamond_weight' => 'nullable|numeric|min:0',
            'purity' => 'required|string',
            'huid' => 'nullable|string|unique:inventory_items,huid',
            'hallmark_center' => 'nullable|string',
            'hallmark_charge' => 'nullable|numeric|min:0',
            'purchase_cost' => 'nullable|numeric|min:0',
            'stone_charge' => 'nullable|numeric|min:0',
            'diamond_charge' => 'nullable|numeric|min:0',
        ]);

        $stoneWt = (float) ($data['stone_weight'] ?? 0);
        $netWt = max(0, (float) $data['gross_weight'] - $stoneWt);
        $nextNum = (InventoryItem::max('id') ?? 0) + 1;
        $itemCode = 'JW-' . str_pad($nextNum, 5, '0', STR_PAD_LEFT);
        $barcode = '8901000' . str_pad($nextNum, 5, '0', STR_PAD_LEFT);

        $item = InventoryItem::create(array_merge($data, [
            'item_code' => $itemCode,
            'barcode' => $barcode,
            'net_weight' => round($netWt, 3),
            'stone_weight' => $stoneWt,
            'diamond_weight' => $data['diamond_weight'] ?? 0,
            'hallmark_charge' => $data['hallmark_charge'] ?? 45,
            'status' => 'available',
        ]));

        StockMovement::create([
            'inventory_item_id' => $item->id,
            'branch_id' => $item->branch_id,
            'type' => 'stock_in',
            'reference' => $itemCode,
            'notes' => 'Unique jewellery piece registered',
            'user_id' => Auth::id(),
        ]);

        AuditService::log('Inventory', 'create', "Registered piece {$itemCode} ({$item->net_weight}g)", $item);

        return back()->with('success', "Piece {$itemCode} registered with barcode {$barcode}.");
    }

    public function transferStock(Request $request)
    {
        $data = $request->validate([
            'inventory_item_id' => 'required|exists:inventory_items,id',
            'to_branch_id' => 'required|exists:branches,id',
            'to_counter_id' => 'nullable|exists:counters,id',
            'notes' => 'nullable|string',
        ]);

        $item = InventoryItem::findOrFail($data['inventory_item_id']);
        $transfer = StockTransfer::create([
            'transfer_no' => 'TRF-' . strtoupper(Str::random(6)),
            'from_branch_id' => $item->branch_id,
            'to_branch_id' => $data['to_branch_id'],
            'from_counter_id' => $item->counter_id,
            'to_counter_id' => $data['to_counter_id'] ?? null,
            'initiated_by' => Auth::id(),
            'approved_by' => Auth::id(),
            'status' => 'completed',
            'notes' => $data['notes'] ?? null,
            'transferred_at' => now(),
        ]);

        StockTransferItem::create(['stock_transfer_id' => $transfer->id, 'inventory_item_id' => $item->id]);
        $item->update(['branch_id' => $data['to_branch_id'], 'counter_id' => $data['to_counter_id'] ?? null]);

        StockMovement::create([
            'inventory_item_id' => $item->id,
            'branch_id' => $data['to_branch_id'],
            'type' => 'transfer_in',
            'reference' => $transfer->transfer_no,
            'notes' => 'Branch/counter transfer',
            'user_id' => Auth::id(),
        ]);

        return back()->with('success', "Item {$item->item_code} transferred ({$transfer->transfer_no}).");
    }

    public function adjustStock(Request $request)
    {
        $data = $request->validate([
            'inventory_item_id' => 'required|exists:inventory_items,id',
            'type' => 'required|in:damaged,lost,found,weight_correction,verification',
            'new_weight' => 'nullable|numeric|min:0',
            'reason' => 'required|string',
        ]);

        $item = InventoryItem::findOrFail($data['inventory_item_id']);
        $adj = StockAdjustment::create([
            'adjustment_no' => 'ADJ-' . strtoupper(Str::random(6)),
            'branch_id' => $item->branch_id,
            'inventory_item_id' => $item->id,
            'type' => $data['type'],
            'previous_weight' => $item->net_weight,
            'new_weight' => $data['new_weight'] ?? $item->net_weight,
            'reason' => $data['reason'],
            'status' => 'approved',
            'requested_by' => Auth::id(),
            'approved_by' => Auth::id(),
        ]);

        if (in_array($data['type'], ['damaged', 'lost'], true)) {
            $item->update(['status' => $data['type']]);
        } elseif ($data['type'] === 'verification') {
            $item->update(['verified_at' => now()]);
        } elseif ($data['type'] === 'weight_correction' && isset($data['new_weight'])) {
            $item->update(['net_weight' => $data['new_weight']]);
        }

        StockMovement::create([
            'inventory_item_id' => $item->id,
            'branch_id' => $item->branch_id,
            'type' => 'adjustment',
            'reference' => $adj->adjustment_no,
            'notes' => "{$data['type']}: {$data['reason']}",
            'user_id' => Auth::id(),
        ]);

        return back()->with('success', "Stock adjustment {$adj->adjustment_no} recorded.");
    }

    public function labels(Request $request)
    {
        $ids = $request->input('ids', []);
        $items = InventoryItem::with('product')
            ->when(!empty($ids), fn ($q) => $q->whereIn('id', $ids))
            ->where('status', 'available')
            ->take(24)
            ->get();

        foreach ($items as $item) {
            LabelPrintLog::create([
                'inventory_item_id' => $item->id,
                'user_id' => Auth::id(),
                'label_type' => 'tag',
                'copies' => 1,
            ]);
        }

        return view('inventory.labels', compact('items'));
    }

    public function stonesAndHallmarking()
    {
        $stones = DiamondStone::with(['supplier', 'inventoryItem.product'])->latest()->get();
        $hallmarks = HallmarkRecord::with('inventoryItem.product')->latest()->get();
        $items = InventoryItem::with('product')->where('status', 'available')->get();
        $suppliers = Supplier::where('is_active', true)->get();

        return view('inventory.stones_hallmark', compact('stones', 'hallmarks', 'items', 'suppliers'));
    }

    public function storeStone(Request $request)
    {
        $data = $request->validate([
            'stone_type' => 'required|string',
            'shape' => 'nullable|string',
            'cut' => 'nullable|string',
            'color' => 'nullable|string',
            'clarity' => 'nullable|string',
            'carat_weight' => 'required|numeric|min:0.001',
            'certificate_no' => 'nullable|string',
            'certification_lab' => 'nullable|string',
            'supplier_id' => 'nullable|exists:suppliers,id',
            'cost_price' => 'required|numeric|min:0',
            'selling_price' => 'required|numeric|min:0',
        ]);

        $data['stone_code'] = 'STN-' . strtoupper(Str::random(6));
        DiamondStone::create($data);

        return back()->with('success', "Stone {$data['stone_code']} added to inventory.");
    }

    public function storeHallmark(Request $request)
    {
        $data = $request->validate([
            'inventory_item_id' => 'required|exists:inventory_items,id',
            'center_name' => 'required|string',
            'dispatch_date' => 'required|date',
            'huid' => 'nullable|string',
            'purity_verified' => 'nullable|string',
            'charges' => 'nullable|numeric|min:0',
            'status' => 'required|in:dispatched,received,verified',
        ]);

        $record = HallmarkRecord::create($data + ['receipt_date' => $data['status'] !== 'dispatched' ? now() : null]);

        if (!empty($data['huid'])) {
            InventoryItem::where('id', $data['inventory_item_id'])->update([
                'huid' => $data['huid'],
                'hallmark_center' => $data['center_name'],
                'hallmark_date' => now(),
                'hallmark_charge' => $data['charges'] ?? 45,
            ]);
        }

        return back()->with('success', 'Hallmark / HUID record saved.');
    }
}

