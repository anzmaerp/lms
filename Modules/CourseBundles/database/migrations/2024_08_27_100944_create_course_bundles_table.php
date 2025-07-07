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

        Schema::create((config('coursebundles.db_prefix') ?? 'courses_') . 'course_bundles', function (Blueprint $table) {
            $table->unsignedBigInteger('bundle_id')->index();
            $table->unsignedBigInteger('course_id')->index();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists((config('coursebundles.db_prefix') ?? 'courses_') . 'course_bundles');
    }
};
