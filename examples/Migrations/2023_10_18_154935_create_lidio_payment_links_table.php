<?php

use App\Models\PaymentLink;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('lidio_payment_links', function (Blueprint $table) {
            $table->id();

            $table->string('result');
            $table->string('result_message')
                ->nullable();
            $table->string('order_id');
            $table->string('system_trans_id')
                ->nullable();
            $table->string('link_url');
            $table->string('email');
            $table->string('phone');

            // In this implementation
            // The merchant custom field is used to store the UUID of the payment link
            $table->string('merchant_custom_field')
                ->nullable();

            // Recommended to store expiration timestamp
            $table->timestamp('expires_at');

            // Optionally store the request JSON
            $table->json('request')
                ->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lidio_payment_links');
    }
};
