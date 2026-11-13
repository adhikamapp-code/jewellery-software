@extends('layouts.app')

@section('header', 'Customer CRM, Loyalty & Marketing Campaigns')

@section('content')
<div class="space-y-6">
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Register Customer -->
        <div class="bg-white rounded-xl border border-slate-200 p-5">
            <h2 class="text-base font-semibold text-slate-800 mb-3">Register Customer</h2>
            <form action="{{ route('crm.store') }}" method="POST" class="space-y-2.5 text-xs">
                @csrf
                <input type="text" name="name" required placeholder="Full Name" class="w-full border border-slate-300 rounded-lg px-3 py-2">
                <div class="grid grid-cols-2 gap-2">
                    <input type="text" name="phone" required placeholder="Mobile Number" class="border border-slate-300 rounded-lg px-3 py-2">
                    <input type="email" name="email" placeholder="Email" class="border border-slate-300 rounded-lg px-3 py-2">
                </div>
                <div class="grid grid-cols-2 gap-2">
                    <div><label class="text-slate-500">DOB</label><input type="date" name="dob" class="w-full border border-slate-300 rounded-lg px-2 py-1.5"></div>
                    <div><label class="text-slate-500">Anniversary</label><input type="date" name="anniversary" class="w-full border border-slate-300 rounded-lg px-2 py-1.5"></div>
                </div>
                <div class="grid grid-cols-3 gap-2">
                    <input type="text" name="pan_number" placeholder="PAN Number" class="border border-slate-300 rounded-lg px-2.5 py-2">
                    <select name="group_tier" class="border border-slate-300 rounded-lg px-2 py-2">
                        <option value="standard">Standard</option>
                        <option value="silver">Silver</option>
                        <option value="gold">Gold</option>
                        <option value="vip">VIP</option>
                    </select>
                    <input type="number" name="credit_limit" value="0" placeholder="Credit Limit" class="border border-slate-300 rounded-lg px-2 py-2">
                </div>
                <input type="text" name="address" placeholder="Address" class="w-full border border-slate-300 rounded-lg px-3 py-2">
                <button class="w-full bg-amber-600 hover:bg-amber-700 text-white font-semibold py-2 rounded-lg">Add Customer</button>
            </form>
        </div>

        <!-- Marketing & Festival Campaigns -->
        <div class="bg-white rounded-xl border border-slate-200 p-5">
            <h2 class="text-base font-semibold text-slate-800 mb-3">Launch Marketing Campaign</h2>
            <form action="{{ route('crm.campaigns.store') }}" method="POST" class="space-y-2.5 text-xs">
                @csrf
                <input type="text" name="title" required placeholder="Campaign Title (e.g. Akshaya Tritiya Gold Offer)" class="w-full border border-slate-300 rounded-lg px-3 py-2">
                <div class="grid grid-cols-3 gap-2">
                    <select name="channel" class="border border-slate-300 rounded-lg px-2 py-2">
                        <option value="whatsapp">WhatsApp</option>
                        <option value="sms">SMS</option>
                        <option value="email">Email</option>
                    </select>
                    <select name="occasion" class="border border-slate-300 rounded-lg px-2 py-2">
                        <option value="akshaya_tritiya">Akshaya Tritiya</option>
                        <option value="dhanteras">Dhanteras</option>
                        <option value="diwali">Diwali</option>
                        <option value="birthday">Birthday</option>
                        <option value="anniversary">Anniversary</option>
                        <option value="collection">New Collection</option>
                    </select>
                    <select name="target_group" class="border border-slate-300 rounded-lg px-2 py-2">
                        <option value="all">All Customers</option>
                        <option value="vip">VIP Tier</option>
                        <option value="gold">Gold Tier</option>
                        <option value="silver">Silver Tier</option>
                    </select>
                </div>
                <textarea name="message" rows="3" required placeholder="Promotional message..." class="w-full border border-slate-300 rounded-lg px-3 py-2"></textarea>
                <button class="w-full bg-slate-900 text-white font-semibold py-2 rounded-lg">Send Campaign</button>
            </form>
        </div>

        <!-- Birthday Reminders & Recent Campaigns -->
        <div class="bg-white rounded-xl border border-slate-200 p-5 space-y-4">
            <div>
                <h3 class="text-sm font-semibold text-slate-800 mb-2">🎂 This Month's Birthdays</h3>
                @forelse($upcomingBirthdays as $ub)
                    <div class="text-xs flex justify-between py-1 border-b border-slate-100">
                        <span>{{ $ub->name }} ({{ $ub->phone }})</span>
                        <span class="text-amber-700 font-medium">{{ $ub->dob->format('d M') }}</span>
                    </div>
                @empty
                    <div class="text-xs text-slate-400">No birthdays this month.</div>
                @endforelse
            </div>
            <div>
                <h3 class="text-sm font-semibold text-slate-800 mb-2">📣 Recent Campaigns</h3>
                @foreach($campaigns as $camp)
                    <div class="text-xs py-1.5 border-b border-slate-100">
                        <div class="font-semibold">{{ $camp->title }}</div>
                        <div class="text-slate-500 uppercase">{{ $camp->channel }} · Sent to {{ $camp->sent_count }}</div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    <!-- Customers Directory -->
    <div class="bg-white rounded-xl border border-slate-200 p-5">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-base font-semibold text-slate-800">Customer Directory & Lifetime Value</h2>
            <form method="GET" class="flex gap-2 text-xs">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Name or phone..." class="border border-slate-300 rounded-lg px-3 py-1.5">
                <button class="bg-slate-800 text-white px-3 py-1.5 rounded-lg">Search</button>
            </form>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-slate-50 text-xs text-slate-500 uppercase">
                    <tr>
                        <th class="px-3 py-2 text-left">Customer</th>
                        <th class="px-3 py-2 text-left">Tier</th>
                        <th class="px-3 py-2 text-right">Purchases</th>
                        <th class="px-3 py-2 text-right">Lifetime Value</th>
                        <th class="px-3 py-2 text-right">Loyalty Pts</th>
                        <th class="px-3 py-2 text-right">Outstanding</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach($customers as $c)
                        <tr>
                            <td class="px-3 py-2">
                                <a href="{{ route('crm.show', $c) }}" class="font-semibold text-amber-700 hover:underline">{{ $c->name }}</a>
                                <div class="text-xs text-slate-500">{{ $c->customer_code }} · {{ $c->phone }}</div>
                            </td>
                            <td class="px-3 py-2"><span class="px-2 py-0.5 text-xs rounded-full bg-amber-100 text-amber-800 uppercase font-semibold">{{ $c->group_tier }}</span></td>
                            <td class="px-3 py-2 text-right">{{ $c->invoices_count }}</td>
                            <td class="px-3 py-2 text-right font-semibold">₹{{ number_format($c->invoices_sum_grand_total ?? 0, 0) }}</td>
                            <td class="px-3 py-2 text-right font-semibold text-emerald-700">{{ $c->loyalty_points }} pts</td>
                            <td class="px-3 py-2 text-right text-rose-600">₹{{ number_format($c->outstanding_balance, 0) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="mt-4">{{ $customers->links() }}</div>
    </div>
</div>
@endsection

