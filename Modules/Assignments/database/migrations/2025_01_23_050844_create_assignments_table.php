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

        Schema::create((config('assignments.db_prefix') ?? 'assignments_') . 'assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('instructor_id')->constrained('users')->onDelete('cascade');
            $table->morphs('related');
            $table->string('title');
            $table->text('description');
            $table->unsignedInteger('total_marks');
            $table->unsignedInteger('passing_percentage');
            $table->unsignedInteger('due_days')->nullable();
            $table->time('due_time')->nullable();
            $table->json('subject_slots')->nullable();
            $table->unsignedTinyInteger('type')->comment('1 => Text, 2 => Document, 3 => Both');
            $table->unsignedInteger('max_file_size')->nullable()->comment('File size in MBs');
            $table->unsignedInteger('max_file_count')->nullable()->comment('Allowed file count');
            $table->unsignedInteger('characters_count')->nullable()->comment('Allowed characters for assignment submission');
            $table->unsignedTinyInteger('status')->default(0)->comment('0 => Draft, 1 => Published, 2 => Archived');
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
        Schema::dropIfExists((config('assignments.db_prefix') ?? 'assignments_') . 'assignments');
    }
};
