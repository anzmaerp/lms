<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up() {
        Schema::create((config('ipmanager.db_prefix') ?? 'ipmanager_') . 'ip_restrictions', function (Blueprint $table) {
            $table->id();
            $table->string('type');
            $table->string('ip_start')->nullable();
            $table->string('ip_end')->nullable();
            $table->text('reason')->nullable();
            $table->string('country')->nullable();
            $table->timestamps();
        });
    }

    public function down() {
        Schema::dropIfExists((config('ipmanager.db_prefix') ?? 'ipmanager_') . 'ip_restrictions');
    }
};
