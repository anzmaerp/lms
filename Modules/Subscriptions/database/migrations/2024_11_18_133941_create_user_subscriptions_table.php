<?php

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
        Schema::create('user_subscriptions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('order_item_id')->index();
            $table->unsignedBigInteger('user_id')->index();
            $table->unsignedBigInteger('subscription_id')->index();
            $table->double('price');
            $table->timestamp('expires_at')->nullable();
            $table->json('revenue_share')->nullable();
            $table->json('credit_limits')->nullable();
            $table->json('remaining_credits')->nullable();
            $table->tinyInteger('auto_renew')->default(0)->comment('0: no, 1: yes');
            $table->tinyInteger('status')->default(0)->comment('0: inactive, 1: active');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_subscriptions');
    }
};
