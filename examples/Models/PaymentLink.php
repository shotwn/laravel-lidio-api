<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use App\Traits\UserStamps;

class PaymentLink extends Model
{
    use HasFactory, UserStamps;

    protected $guarded = [];

    // Belongs to an invoice
    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }

    // Has morphed relationship with different payment link providers
    public function providerPaymentLink()
    {
        return $this->morphTo(
            'provider_payment_link',
            'provider_payment_link_type',
            'provider_payment_link_id',
            'id'
        );
    }
}
