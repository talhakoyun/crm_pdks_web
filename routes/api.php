<?php

declare(strict_types=1);

use App\Http\Controllers\Api\AnnouncementController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\GeneralController;
use App\Http\Controllers\Api\HolidayController;
use App\Http\Controllers\Api\ShiftFollowController;
use App\Http\Controllers\Api\ShiftFollowReportController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\EventController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::get('isavailable', [GeneralController::class, 'isAvailable']);

// Açık rotalar
Route::group(['prefix' => 'auth'], function () {
    Route::post('login', [AuthController::class, 'login']);
    Route::post('refresh', [AuthController::class, 'refresh']);
    Route::post('register', [AuthController::class, 'register']);
});

// Yetkilendirme gerektiren rotalar
Route::group(['middleware' => 'auth:api'], function () {
    // Auth rotaları
    Route::group(['prefix' => 'auth'], function () {
        Route::post('logout', [AuthController::class, 'logout']);
        Route::get('me', [AuthController::class, 'me']);
    });

    // Vardiya takip rotaları
    Route::group(['prefix' => 'shift/follow'], function () {
        Route::post('list', [ShiftFollowController::class, 'list']);
        Route::get('types', [ShiftFollowController::class, 'types']);
        Route::post('store', [ShiftFollowController::class, 'store']);
        Route::post('qr/store', [ShiftFollowController::class, 'qrStore']);
    });

    // Vardiya takip rapor rotaları
    Route::group(['prefix' => 'shift/follow/report'], function () {
        Route::post('daily', [ShiftFollowReportController::class, 'daily']);
        Route::post('weekly', [ShiftFollowReportController::class, 'weeklyReport']);
    });

    // İzin Yönetimi
    Route::prefix('holidays')->group(function () {
        Route::get('list', [HolidayController::class, 'list']);
        Route::post('store', [HolidayController::class, 'store']);
    });

    // Kullanıcı bilgileri
    Route::group(['prefix' => 'user'], function () {
        Route::get('profile', [UserController::class, 'profile']);
        Route::post('profile/update', [UserController::class, 'profileUpdate']);
        Route::post('password/update', [UserController::class, 'passwordUpdate']);
    });

    // Duyuru rotaları
    Route::group(['prefix' => 'announcements'], function () {
        Route::get('list', [AnnouncementController::class, 'index']);
        Route::post('detail', [AnnouncementController::class, 'show']);
    });

    // Etkinlik rotaları
    Route::group(['prefix' => 'events'], function () {
        Route::get('list', [EventController::class, 'index']);
        Route::post('detail', [EventController::class, 'show']);
        Route::post('participate', [EventController::class, 'participate']);
        Route::get('my-participations', [EventController::class, 'myParticipations']);
    });
});
