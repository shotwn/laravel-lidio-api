<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LidioPaymentNotification extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'process_info' => 'array',
        'customer_info' => 'array',
        'basket_items' => 'array',
        'payment_list' => 'array',
    ];

    // Belongs to a payment record
    public function paymentRecord()
    {
        return $this->morphOne(PaymentRecord::class, 'provider_payment_notification');
    }

    // Belongs to a payment link
    public function lidioPaymentLink()
    {
        return $this->belongsTo(LidioPaymentLink::class);
    }
}
