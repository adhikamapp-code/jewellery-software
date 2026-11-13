<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    protected $guarded = ['id'];

    protected function casts(): array
    {
        return ['dob' => 'date', 'anniversary' => 'date'];
    }

    public function invoices()
    {
        return $this->hasMany(Invoice::class);
    }

    public function oldGoldPurchases()
    {
        return $this->hasMany(OldGoldPurchase::class);
    }

    public function goldSchemes()
    {
        return $this->hasMany(GoldScheme::class);
    }

    public function loyaltyTransactions()
    {
        return $this->hasMany(LoyaltyTransaction::class);
    }

    public function wishlists()
    {
        return $this->hasMany(CustomerWishlist::class);
    }
}

