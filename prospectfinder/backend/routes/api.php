<?php

use App\Http\Controllers\Api\V1\HealthController;
use Illuminate\Support\Facades\Route;

// All routes in this file are prefixed with /api/v1 (see bootstrap/app.php).

Route::get('/health', HealthController::class)->name('health');
