<?php

use App\Http\Controllers\Api\V1\ContactController;
use Illuminate\Support\Facades\Route;

Route::apiResource('contacts', ContactController::class);
