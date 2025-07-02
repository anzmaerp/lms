<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('coupons', function (Blueprint $table) {
            $table->id();
            $table->morphs('couponable');
            $table->string('code', 30)->unique();
            $table->string('color', 50)->nullable();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->tinyInteger('discount_type')->default(0)->comment('1: percentage, 0: fixed');
            $table->tinyInteger('auto_apply')->default(0)->comment('1: auto apply, 0: manual apply');
            $table->double('discount_value')->default(0);
            $table->timestamp('expiry_date')->nullable();
            $table->tinyInteger('status')->default(0)->comment('1: active, 0: inactive');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('coupons');
    }
};
