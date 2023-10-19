<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LidioPaymentLink extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'request' => 'array',
    ];

    // Has morphed relationship with general payment link model
    public function paymentLink()
    {
        return $this->morphOne(PaymentLink::class, 'provider_payment_link');
    }

    // Has many payment notifications
    public function lidioPaymentNotifications()
    {
        return $this->hasMany(LidioPaymentNotification::class);
    }
}
