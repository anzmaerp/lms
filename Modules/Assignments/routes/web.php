<?php


use Illuminate\Support\Facades\Route;
use Modules\Assignments\Livewire\Pages\Tutor\AssignmentsList\SubmissionsAssignmentsList;
use Modules\Assignments\Livewire\Pages\Tutor\AssignmentsList\AssignmentsList;
use Modules\Assignments\Livewire\Pages\Tutor\CreateAssignment\CreateAssignment;
use Modules\Assignments\Livewire\Pages\Tutor\AssignmentMark\AssignmentMark;

use Modules\Assignments\Livewire\Pages\Student\AssignmentAttempt\AssignmentAttempt;
use Modules\Assignments\Livewire\Pages\Student\AsignmentList\AssignmentList;
use Modules\Assignments\Livewire\Pages\Student\AssignmentResult\AssignmentResult;
use Modules\Assignments\Livewire\Pages\Student\SubmitAssignment\SubmitAssignment;

Route::middleware(['locale', 'maintenance', 'enabled:assignments'])->as('assignments.')->prefix(config('assignments.url_prefix'))->group(function () {

    Route::middleware(['auth', 'verified', 'onlineUser', 'role:tutor'])->name('tutor.')->group(function () {
        Route::get('assignments', AssignmentsList::class)->name('assignments-list');
        Route::get('assignments/create', CreateAssignment::class)->name('create-assignment');
        Route::get('assignments/update/{id?}', CreateAssignment::class)->name('update-assignment');
        Route::get('assignments/{assignmentId}/submissions', SubmissionsAssignmentsList::class)->name('submissions-assignments-list');
        Route::get('mark-assignment/{id}', AssignmentMark::class)->name('mark-assignment');
    });
    Route::middleware(['auth', 'verified', 'role:student'])->name('student.')->group(function () {
        Route::get('student/assignments', AssignmentList::class)->name('student-assignments');
        Route::get('attempt-assignment/{id}', AssignmentAttempt::class)->name('attempt-assignment');
        Route::get('submit-assignment/{id}', SubmitAssignment::class)->name('submit-assignment');
        Route::get('assignment/{submissionId}/result', AssignmentResult::class)->name('assignment-result');
    });
});
