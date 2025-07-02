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
        Schema::create('subscriptions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('role_id');
            $table->string('name', 255);
            $table->text('description')->nullable();
            $table->double('price')->default(0);
            $table->string('image')->nullable();
            $table->json('credit_limits');
            $table->json('revenue_share')->nullable();
            $table->boolean('auto_renew')->default(0)->comment('0: no, 1: yes');
            $table->tinyInteger('period')->default(0)->comment('1: monthly, 2: yearly, 3: 6 months');
            $table->unsignedBigInteger('created_by');
            $table->tinyInteger('status')->default(1)->comment('1: active, 0: deactive');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('subscriptions');
    }
};
