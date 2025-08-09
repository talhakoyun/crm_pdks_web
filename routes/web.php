<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Backend\AuthenticationController;

Route::get('/', [AuthenticationController::class, 'signIn'])->name('sign-in');