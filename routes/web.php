<?php

use App\Http\Controllers\AdminExportController;
use App\Http\Controllers\PublicReleaseController;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => redirect('/admin'));

Route::middleware(['auth'])->group(function () {
    Route::get('/admin/releases/{release}/export', [AdminExportController::class, 'export'])
        ->name('admin.releases.export');
});

Route::prefix('r')->name('release.')->group(function () {
    Route::get('/{token}', [PublicReleaseController::class, 'show'])->name('show');
    Route::get('/{token}/form', [PublicReleaseController::class, 'form'])->name('form');
});
