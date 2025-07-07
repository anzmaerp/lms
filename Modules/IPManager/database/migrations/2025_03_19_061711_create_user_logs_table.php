<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up() {
        Schema::create((config('ipmanager.db_prefix') ?? 'ipmanager_') . 'user_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('os')->nullable();
            $table->string('browser')->nullable();
            $table->string('device')->nullable();
            $table->string('ip_address')->nullable();
            $table->string('country')->nullable();
            $table->string('city')->nullable();
            $table->string('latitude')->nullable();
            $table->string('longitude')->nullable();
            $table->text('session_id')->nullable();
            $table->timestamp('session_start')->useCurrent();
            $table->timestamp('session_end')->nullable();
            $table->integer('duration')->storedAs('TIMESTAMPDIFF(SECOND, session_start, session_end)');
            $table->timestamps();
        });
    }

    public function down() {
        Schema::dropIfExists((config('ipmanager.db_prefix') ?? 'ipmanager_') . 'user_logs');
    }
};

