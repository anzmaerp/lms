<?php


use Illuminate\Support\Facades\Route;
use Modules\CourseBundles\Livewire\Pages\Tutor\BundleCreation\CreateBundle;
use Modules\CourseBundles\Livewire\Pages\Tutor\BundleListing\BundleListing;
use Modules\CourseBundles\Livewire\Pages\Admin\CourseBundleListing;
use Modules\CourseBundles\Livewire\Pages\Search\SearchCoursesBundles;
use Modules\CourseBundles\Livewire\Pages\Bundle\BundleDetails;

Route::middleware(['locale', 'maintenance', 'enabled:coursebundles'])->as('coursebundles.')->group(function () {
    Route::get('/course-bundles', SearchCoursesBundles::class)->name('course-bundles');
    Route::get('/course-bundle/{slug}', BundleDetails::class)->name('bundle-details');
    Route::middleware(['auth', 'verified', 'onlineUser', 'role:tutor'])->name('tutor.')->group(function () {
        Route::get('/create-course-bundle', CreateBundle::class)->name('create-course-bundle');
        Route::get('/edit-course-bundle/{id}', CreateBundle::class)->name('edit-course-bundle');
        Route::get('/manage-course-bundles', BundleListing::class)->name('bundles');
    });

    Route::middleware(['auth', 'verified', 'role:admin|sub_admin','permit-of:can-manage-course-bundles'])->name('admin.')->group(function () {
        Route::get('/admin/course-bundles', CourseBundleListing::class)->name('course-bundles-list');
    });
});
