<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        Schema::create((config('coursebundles.db_prefix') ?? 'courses_') . 'bundles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('instructor_id')->constrained('users')->onDelete('cascade')->index();
            $table->string('title');
            $table->string('slug')->nullable();
            $table->string('short_description', 200)->nullable();
            $table->text('description')->nullable();
            $table->decimal('price', 10, 2)->default(0.00);
            $table->integer('discount_percentage')->default(0);
            $table->decimal('final_price', 10, 2)->storedAs('ROUND(price - (price * discount_percentage / 100), 2)');
            $table->tinyInteger('status')->default(0)->comment('0->draft, 1->published, 2->archieved')->index();
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
        Schema::dropIfExists((config('coursebundles.db_prefix') ?? 'courses_') . 'bundles');
    }
};
