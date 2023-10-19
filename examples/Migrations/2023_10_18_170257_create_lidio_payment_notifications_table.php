<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

use App\Models\LidioPaymentLink;
use App\Models\PaymentRecords;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('lidio_payment_notifications', function (Blueprint $table) {
            $table->id();
            $table->string('action');
            $table->string('payment_result');
            $table->json('process_info')->nullable();
            $table->json('customer_info')->nullable();
            $table->json('basket_items')->nullable();
            $table->json('payment_list')->nullable();
            $table->boolean('success')->nullable();
            $table->string('failed')->nullable();
            $table->string('order_id')->nullable();
            $table->string('merchant_process_id')->nullable();
            $table->string('merchant_custom_field')->nullable();
            $table->boolean('signatures_verified')->nullable();
            // Following is unused so far, so disabled on save.
            // $table->boolean('requested_amount_verified')->nullable();
            // $table->boolean('processed_amount_verified')->nullable();
            $table->boolean('merchant_custom_field_verified')->nullable();

            // Optionally match to payment link
            // You can use merchant_process_id or order_id to match
            $table->foreignIdFor(LidioPaymentLink::class)
                ->nullable()
                ->constrained()
                // Keep the past payment notifications even if the payment link is deleted
                ->onDelete('set null');

            // Optionally save the reason of failed checks within your application
            // Such as "Payment link not found", "Invalid merchant_custom_field", etc.
            $table->string('notification_rejection_reason')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lidio_payment_notifications');
    }
};
