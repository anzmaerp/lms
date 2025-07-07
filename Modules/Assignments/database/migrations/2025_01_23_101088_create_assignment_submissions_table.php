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

        Schema::create((config('assignments.db_prefix') ?? 'assignments_') . 'assignment_submissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('assignment_id')->constrained((config('assignments.db_prefix') ?? 'assignments_') . 'assignments')->onDelete('cascade');
            $table->foreignId('student_id')->constrained('users')->onDelete('cascade');
            $table->text('submission_text')->nullable();
            $table->unsignedInteger('marks_awarded')->nullable();
            $table->dateTime('submitted_at')->nullable();
            $table->dateTime('graded_at')->nullable();
            $table->dateTime('ended_at')->nullable();
            $table->unsignedTinyInteger('result')->comment('0 => fail, 1 => pass, 2 => in_review, 3 => assigned')->nullable();
            $table->text('feedback')->nullable();
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
        Schema::dropIfExists((config('assignments.db_prefix') ?? 'assignments_') . 'assignment_submissions');
    }
};
