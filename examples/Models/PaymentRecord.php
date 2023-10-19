<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaymentRecord extends Model
{
    use HasFactory;

    protected $guarded = [];

    // Belongs to an invoice
    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }

    // Can be manually created by a user
    public function createdByUser()
    {
        return $this->belongsTo(Users::class, 'created_by_user_id');
    }

    // Has morphed relationship with different payment record providers
    public function providerPaymentRecord()
    {
        return $this->morphTo(
            'provider_payment_record',
            'provider_payment_record_type',
            'provider_payment_record_id',
            'id'
        );
    }

    protected static function booted()
    {
        static::saved(function ($paymentRecord) {
            // Update invoice status to paid if payments are enough
            if ($paymentRecord->invoice) {
                $paymentRecord->invoice->updatePaidStatus();
            }
        });
    }
}
