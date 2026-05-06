<?php

use App\Http\Controllers\AdminExportController;
use App\Http\Controllers\PublicReleaseController;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => redirect('/admin'));

Route::middleware(['auth'])->group(function () {
    Route::get('/admin/releases/{release}/export', [AdminExportController::class, 'export'])
        ->name('admin.releases.export');
    Route::get('/admin/release-sets/{releaseSet}/export', [AdminExportController::class, 'exportReleaseSet'])
        ->name('admin.release-sets.export');
    Route::get('/admin/form-import-template', [AdminExportController::class, 'formImportTemplate'])
        ->name('admin.form-import-template');
    Route::get('/admin/file/{answer}/{index?}', [AdminExportController::class, 'serveFile'])
        ->name('admin.file.serve');
});

Route::get('/r/file/{answer}/{index?}', [PublicReleaseController::class, 'serveFile'])
    ->name('public.file.serve');

Route::prefix('r')->name('release.')->group(function () {
    // Entry point — shows identify/register form, or redirects to forms list if already identified
    Route::get('/{token}', [PublicReleaseController::class, 'show'])->name('show');

    // Release-set forms list (after identification)
    Route::get('/{token}/forms', [PublicReleaseController::class, 'forms'])->name('forms');

    // Open a specific form release — creates / finds the submission and shows the form
    Route::get('/{token}/form/{releaseId}', [PublicReleaseController::class, 'form'])->name('form');

    // Edit a specific existing submission (multi-submission or single resume)
    Route::get('/{token}/form/{releaseId}/submission/{submissionId}', [PublicReleaseController::class, 'submissionEdit'])
        ->name('submission.edit');

    // Submission history for multi-submission forms
    Route::get('/{token}/form/{releaseId}/history', [PublicReleaseController::class, 'history'])->name('history');
});
