<?php

use Illuminate\Support\Facades\Route;
use Modules\Assignments\Http\Controllers\Api\AssignmentsController;

Route::middleware('auth:sanctum')->group(function () {
 
    Route::middleware('role:tutor')->group(function () {
        Route::post('assignment',                       [AssignmentsController::class, 'createOrUpdateAssignment']);
        Route::get('session-list',                      [AssignmentsController::class, 'getSessionList']);
        Route::get('courses-list',                      [AssignmentsController::class, 'getCourseList']);
        Route::prefix('assignments')->group(function () {
            Route::get('subjects-list', [AssignmentsController::class, 'getSubjects']);
        });
        Route::delete('assignment',                     [AssignmentsController::class, 'deleteAssignment']);
        Route::post('publish-assignment',               [AssignmentsController::class, 'publishAssignment']);
        Route::post('archive-assignment',               [AssignmentsController::class, 'archiveAssignment']);
        Route::get('submission-assignment-detail',      [AssignmentsController::class, 'getSubmissionAssignmentDetail']);
        Route::post('submit-result',                    [AssignmentsController::class, 'submitResult']);
        Route::get('submission-assignments-list',       [AssignmentsController::class, 'getSubmissionAssignmentsList']);
        Route::get('assignment-detail',                 [AssignmentsController::class, 'assignmentDetail']);
        Route::get('assignments-list',                  [AssignmentsController::class, 'getAssignmentsList']);
    });

    Route::middleware('role:student')->group(function () {
        Route::get('assignments',                       [AssignmentsController::class, 'getAssignments']);
        Route::get('assignment',                        [AssignmentsController::class, 'getAssignment']);
        Route::post('submit-assignment',                [AssignmentsController::class, 'submitAssignment']);
    });

});





