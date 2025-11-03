<?php

use Illuminate\Support\Facades\Route;
use Webkul\Lead\Http\Controllers\ReAssignController;

Route::middleware(['web', 'admin_locale', 'user'])
    ->prefix(config('app.admin_path'))
    ->group(function () {
        Route::post('/leads/reassign', [ReAssignController::class, 'store'])
            ->name('admin.leads.reassign');
    });
