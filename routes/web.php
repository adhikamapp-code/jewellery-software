<?php

use App\Http\Controllers\AdminBranchController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CustomerCrmController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\FinanceController;
use App\Http\Controllers\GoldSchemeController;
use App\Http\Controllers\MetalRateController;
use App\Http\Controllers\OldGoldController;
use App\Http\Controllers\PosController;
use App\Http\Controllers\ProductInventoryController;
use App\Http\Controllers\PurchaseSupplierController;
use App\Http\Controllers\WorkshopController;
use App\Http\Middleware\EnsureUserIsActive;
use Illuminate\Support\Facades\Route;

// Auth
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.post');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

Route::middleware(['auth', EnsureUserIsActive::class])->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    // Module 4: Metal Rates
    Route::get('/rates', [MetalRateController::class, 'index'])->name('rates.index');
    Route::post('/rates', [MetalRateController::class, 'store'])->name('rates.store');
    Route::post('/rates/{metalRate}/approve', [MetalRateController::class, 'approve'])->name('rates.approve');

    // Modules 2, 3, 13, 14, 15: Products, Inventory, Labels, Stones, Hallmarking
    Route::get('/products', [ProductInventoryController::class, 'products'])->name('products.index');
    Route::post('/products', [ProductInventoryController::class, 'storeProduct'])->name('products.store');
    Route::get('/inventory', [ProductInventoryController::class, 'inventory'])->name('inventory.index');
    Route::post('/inventory', [ProductInventoryController::class, 'storeItem'])->name('inventory.store');
    Route::post('/inventory/transfer', [ProductInventoryController::class, 'transferStock'])->name('inventory.transfer');
    Route::post('/inventory/adjust', [ProductInventoryController::class, 'adjustStock'])->name('inventory.adjust');
    Route::get('/inventory/labels', [ProductInventoryController::class, 'labels'])->name('inventory.labels');
    Route::get('/stones-hallmark', [ProductInventoryController::class, 'stonesAndHallmarking'])->name('stones.hallmark');
    Route::post('/stones', [ProductInventoryController::class, 'storeStone'])->name('stones.store');
    Route::post('/hallmark', [ProductInventoryController::class, 'storeHallmark'])->name('hallmark.store');

    // Modules 5, 6, 17: POS, Billing, Price Engine, Returns
    Route::get('/pos', [PosController::class, 'index'])->name('pos.index');
    Route::post('/pos/calculate', [PosController::class, 'calculatePreview'])->name('pos.calculate');
    Route::post('/pos/checkout', [PosController::class, 'store'])->name('pos.store');
    Route::get('/invoices', [PosController::class, 'invoices'])->name('pos.invoices');
    Route::get('/invoices/{invoice}', [PosController::class, 'showInvoice'])->name('pos.invoice');
    Route::post('/invoices/{invoice}/cancel', [PosController::class, 'cancelInvoice'])->name('pos.cancel');
    Route::post('/sales-returns', [PosController::class, 'storeReturn'])->name('pos.returns.store');

    // Modules 7, 18, 24: CRM, Loyalty, Marketing
    Route::get('/customers', [CustomerCrmController::class, 'index'])->name('crm.index');
    Route::post('/customers', [CustomerCrmController::class, 'store'])->name('crm.store');
    Route::get('/customers/{customer}', [CustomerCrmController::class, 'show'])->name('crm.show');
    Route::post('/customers/{customer}/loyalty', [CustomerCrmController::class, 'adjustLoyalty'])->name('crm.loyalty');
    Route::post('/campaigns', [CustomerCrmController::class, 'storeCampaign'])->name('crm.campaigns.store');

    // Module 8: Old Gold Exchange & Purchase
    Route::get('/old-gold', [OldGoldController::class, 'index'])->name('old_gold.index');
    Route::post('/old-gold', [OldGoldController::class, 'store'])->name('old_gold.store');
    Route::post('/old-gold/{oldGoldPurchase}/refinery', [OldGoldController::class, 'transferRefinery'])->name('old_gold.refinery');

    // Module 9: Purchases & Suppliers
    Route::get('/purchases', [PurchaseSupplierController::class, 'index'])->name('purchases.index');
    Route::post('/suppliers', [PurchaseSupplierController::class, 'storeSupplier'])->name('suppliers.store');
    Route::post('/purchase-invoices', [PurchaseSupplierController::class, 'storePurchaseInvoice'])->name('purchases.store');
    Route::post('/supplier-payments', [PurchaseSupplierController::class, 'paySupplier'])->name('suppliers.pay');

    // Modules 10, 11, 16: Manufacturing, Custom Orders, Repairs
    Route::get('/workshop', [WorkshopController::class, 'index'])->name('workshop.index');
    Route::post('/manufacturing', [WorkshopController::class, 'storeManufacturing'])->name('workshop.mfg.store');
    Route::post('/manufacturing/{order}/stage', [WorkshopController::class, 'updateManufacturingStage'])->name('workshop.mfg.stage');
    Route::post('/custom-orders', [WorkshopController::class, 'storeCustomOrder'])->name('workshop.custom.store');
    Route::post('/custom-orders/{order}/status', [WorkshopController::class, 'updateCustomOrderStatus'])->name('workshop.custom.status');
    Route::post('/repairs', [WorkshopController::class, 'storeRepair'])->name('workshop.repair.store');
    Route::post('/repairs/{repairJob}/status', [WorkshopController::class, 'updateRepairStatus'])->name('workshop.repair.status');

    // Module 12: Gold Savings Schemes
    Route::get('/schemes', [GoldSchemeController::class, 'index'])->name('schemes.index');
    Route::post('/schemes', [GoldSchemeController::class, 'store'])->name('schemes.store');
    Route::post('/schemes/installments/{installment}/pay', [GoldSchemeController::class, 'payInstallment'])->name('schemes.pay');

    // Module 19: Accounts & Finance
    Route::get('/finance', [FinanceController::class, 'index'])->name('finance.index');
    Route::post('/finance/vouchers', [FinanceController::class, 'storeVoucher'])->name('finance.vouchers.store');
    Route::post('/finance/expenses', [FinanceController::class, 'storeExpense'])->name('finance.expenses.store');

    // Modules 20, 21, 22, 23: Staff, Branches, Security/Approvals, BI Reports
    Route::get('/staff', [AdminBranchController::class, 'staff'])->name('admin.staff');
    Route::post('/staff', [AdminBranchController::class, 'storeStaff'])->name('admin.staff.store');
    Route::post('/staff/attendance', [AdminBranchController::class, 'markAttendance'])->name('admin.attendance');
    Route::get('/branches', [AdminBranchController::class, 'branches'])->name('admin.branches');
    Route::post('/branches', [AdminBranchController::class, 'storeBranch'])->name('admin.branches.store');
    Route::get('/security', [AdminBranchController::class, 'securityAndApprovals'])->name('admin.security');
    Route::post('/approvals/{approval}/decide', [AdminBranchController::class, 'decideApproval'])->name('admin.approvals.decide');
    Route::get('/reports', [AdminBranchController::class, 'reports'])->name('admin.reports');
});
