<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\CustomerFeedback;
use App\Models\LoyaltyTransaction;
use App\Models\MarketingCampaign;
use App\Services\AuditService;
use Illuminate\Http\Request;

class CustomerCrmController extends Controller
{
    public function index(Request $request)
    {
        $customers = Customer::withCount('invoices')
            ->withSum('invoices', 'grand_total')
            ->when($request->tier, fn ($q, $t) => $q->where('group_tier', $t))
            ->when($request->search, fn ($q, $s) => $q->where('name', 'like', "%{$s}%")->orWhere('phone', 'like', "%{$s}%"))
            ->latest()
            ->paginate(20);

        $upcomingBirthdays = Customer::whereNotNull('dob')
            ->whereMonth('dob', now()->month)
            ->get();

        $campaigns = MarketingCampaign::latest()->take(10)->get();
        $feedbacks = CustomerFeedback::with(['customer', 'invoice'])->latest()->take(10)->get();

        return view('crm.index', compact('customers', 'upcomingBirthdays', 'campaigns', 'feedbacks'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string',
            'phone' => 'required|string|unique:customers,phone',
            'email' => 'nullable|email',
            'address' => 'nullable|string',
            'dob' => 'nullable|date',
            'anniversary' => 'nullable|date',
            'pan_number' => 'nullable|string',
            'group_tier' => 'required|in:standard,silver,gold,vip',
            'credit_limit' => 'nullable|numeric|min:0',
            'preferences' => 'nullable|string',
        ]);

        $next = (Customer::max('id') ?? 0) + 1;
        $data['customer_code'] = 'CUST-' . str_pad($next, 4, '0', STR_PAD_LEFT);
        $customer = Customer::create($data);

        AuditService::log('CRM', 'create', "Registered customer {$customer->name}", $customer);
        return back()->with('success', "Customer {$customer->name} ({$customer->customer_code}) added.");
    }

    public function show(Customer $customer)
    {
        $customer->load(['invoices.items', 'oldGoldPurchases', 'goldSchemes', 'loyaltyTransactions', 'wishlists.product']);
        return view('crm.show', compact('customer'));
    }

    public function adjustLoyalty(Customer $customer, Request $request)
    {
        $data = $request->validate([
            'type' => 'required|in:earn,redeem,adjust',
            'points' => 'required|integer|min:1',
            'description' => 'required|string',
        ]);

        if ($data['type'] === 'redeem') {
            if ($customer->loyalty_points < $data['points']) {
                return back()->withErrors(['points' => 'Insufficient loyalty points.']);
            }
            $customer->decrement('loyalty_points', $data['points']);
        } else {
            $customer->increment('loyalty_points', $data['points']);
        }

        LoyaltyTransaction::create([
            'customer_id' => $customer->id,
            'points' => $data['points'],
            'type' => $data['type'],
            'description' => $data['description'],
        ]);

        return back()->with('success', 'Loyalty points updated.');
    }

    public function storeCampaign(Request $request)
    {
        $data = $request->validate([
            'title' => 'required|string',
            'channel' => 'required|in:whatsapp,sms,email',
            'occasion' => 'required|string',
            'target_group' => 'required|string',
            'message' => 'required|string',
        ]);

        $audienceCount = Customer::when($data['target_group'] !== 'all', fn ($q) => $q->where('group_tier', $data['target_group']))->count();
        MarketingCampaign::create($data + ['status' => 'sent', 'sent_count' => $audienceCount]);

        return back()->with('success', "Campaign '{$data['title']}' dispatched to {$audienceCount} customers.");
    }
}

