<?php

use App\Http\Controllers\Backend\AuthenticationController;
use App\Http\Controllers\Backend\UserController;
use App\Http\Controllers\Backend\DashboardController;
use App\Http\Controllers\Backend\RoleController;
use App\Http\Controllers\Backend\CompanyController;
use App\Http\Controllers\Backend\BranchController;
use App\Http\Controllers\Backend\DebitDeviceController;
use App\Http\Controllers\Backend\DepartmentController;
use App\Http\Controllers\Backend\FileTypeController;
use App\Http\Controllers\Backend\HolidayController;
use App\Http\Controllers\Backend\HourlyLeaveController;
use App\Http\Controllers\Backend\OfficialHolidayController;
use App\Http\Controllers\Backend\ShiftDefinitionController;
use App\Http\Controllers\Backend\MenuController;
use App\Http\Controllers\Backend\ShiftFollowController;
use App\Http\Controllers\Backend\UserShiftCustomController;
use App\Http\Controllers\Backend\UserDebitDeviceController;
use App\Http\Controllers\Backend\UserFileController;
use App\Http\Controllers\Backend\AnnouncementController;
use App\Http\Controllers\Backend\EventController;
use App\Http\Controllers\Backend\ShiftAssignmentController;
use App\Http\Controllers\Backend\WeeklyHolidayController;
use Illuminate\Support\Facades\Route;


Route::group(['prefix' => '', 'middleware' => ['auth:user', 'company.access']], function () {
    Route::controller(DashboardController::class)->group(function () {
        Route::get('/', 'index')->name('backend.index');
    });
    Route::prefix('profile')->group(function () {
        Route::controller(UserController::class)->group(function () {
            Route::get('/', 'profile')->name('backend.profile')->desc('Profil');
            Route::post('/', 'profile_save')->name('backend.profile_save')->desc('Profil');
            Route::post('password', 'password')->name('backend.profile_password')->desc('Profil');
        });
    });
    Route::prefix('shift/follow')->group(function () {
        Route::controller(ShiftFollowController::class)->group(function () {
            Route::post('get-user-branches', 'getUserBranches')->name('backend.shift_follow_get_user_branches')->desc('Personel Şubeleri');
        });
    });
    Route::group(['middleware' => ['permissions']], function () {
        Route::prefix('user')->group(function () {
            Route::controller(UserController::class)->group(function () {
                Route::any('/', 'list')->name('backend.user_list')->desc('Kullanıcılar');
                Route::get('form/{unique?}', 'form')->name('backend.user_form')->desc('Kullanıcılar');
                Route::post('form/{unique?}', 'save')->name('backend.user_save')->desc('Kullanıcılar');
                Route::delete('delete', 'delete')->name('backend.user_delete')->desc('Kullanıcılar');
            });
        });
        Route::prefix('role')->group(function () {
            Route::controller(RoleController::class)->group(function () {
                Route::any('/', 'list')->name('backend.role_list')->desc('Roller');
                Route::get('form/{unique?}', 'form')->name('backend.role_form')->desc('Roller');
                Route::post('form/{unique?}', 'save')->name('backend.role_save')->desc('Roller');
                Route::delete('delete', 'delete')->name('backend.role_delete')->desc('Roller');
            });
        });
        Route::prefix('company')->group(function () {
            Route::controller(CompanyController::class)->group(function () {
                Route::any('/', 'list')->name('backend.company_list')->desc('Şirketler');
                Route::get('form/{unique?}', 'form')->name('backend.company_form')->desc('Şirketler');
                Route::post('form/{unique?}', 'save')->name('backend.company_save')->desc('Şirketler');
                Route::delete('delete', 'delete')->name('backend.company_delete')->desc('Şirketler');
            });
        });
        Route::prefix('branch')->group(function () {
            Route::controller(BranchController::class)->group(function () {
                Route::any('/', 'list')->name('backend.branch_list')->desc('Şubeler');
                Route::get('form/{unique?}', 'form')->name('backend.branch_form')->desc('Şubeler');
                Route::post('form/{unique?}', 'save')->name('backend.branch_save')->desc('Şubeler');
                Route::delete('delete', 'delete')->name('backend.branch_delete')->desc('Şubeler');
            });
        });
        Route::prefix('department')->group(function () {
            Route::controller(DepartmentController::class)->group(function () {
                Route::any('/', 'list')->name('backend.department_list')->desc('Departmanlar');
                Route::get('form/{unique?}', 'form')->name('backend.department_form')->desc('Departmanlar');
                Route::post('form/{unique?}', 'save')->name('backend.department_save')->desc('Departmanlar');
                Route::delete('delete', 'delete')->name('backend.department_delete')->desc('Departmanlar');
            });
        });
        Route::prefix('shift/definition')->group(function () {
            Route::controller(ShiftDefinitionController::class)->group(function () {
                Route::any('/', 'list')->name('backend.shift_definition_list')->desc('Vardiya Tanımlamaları');
                Route::get('form/{unique?}', 'form')->name('backend.shift_definition_form')->desc('Vardiya Tanımlamaları');
                Route::post('form/{unique?}', 'save')->name('backend.shift_definition_save')->desc('Vardiya Tanımlamaları');
                Route::delete('delete', 'delete')->name('backend.shift_definition_delete')->desc('Vardiya Tanımlamaları');
            });
        });
        Route::prefix('shift/assignment')->group(function () {
            Route::controller(ShiftAssignmentController::class)->group(function () {
                Route::any('/', 'list')->name('backend.shift_assignment_list')->desc('Vardiya Atamaları');
                Route::get('form/{unique?}', 'form')->name('backend.shift_assignment_form')->desc('Vardiya Atamaları');
                Route::post('form/{unique?}', 'save')->name('backend.shift_assignment_save')->desc('Vardiya Atamaları');
                Route::get('get-users', 'getUsers')->name('backend.shift_assignment_get_users')->desc('Vardiya Atamaları');
                Route::post('assign', 'assignShift')->name('backend.shift_assignment_assign')->desc('Vardiya Atamaları');
                Route::put('update/{id}', 'updateAssignment')->name('backend.shift_assignment_update')->desc('Vardiya Atamaları');
                Route::delete('delete', 'delete')->name('backend.shift_assignment_delete')->desc('Vardiya Atamaları');
                Route::delete('remove/{id}', 'removeAssignment')->name('backend.shift_assignment_remove')->desc('Vardiya Atamaları');
            });
        });
        Route::prefix('weekly/holiday')->group(function () {
            Route::controller(WeeklyHolidayController::class)->group(function () {
                Route::any('/', 'list')->name('backend.weekly_holiday_list')->desc('Haftalık Tatil Günleri');
                Route::get('form/{unique?}', 'form')->name('backend.weekly_holiday_form')->desc('Haftalık Tatil Günleri');
                Route::post('form/{unique?}', 'save')->name('backend.weekly_holiday_save')->desc('Haftalık Tatil Günleri');
                Route::get('get-users', 'getUsers')->name('backend.weekly_holiday_get_users')->desc('Haftalık Tatil Günleri');
                Route::post('assign', 'assignHolidays')->name('backend.weekly_holiday_assign')->desc('Haftalık Tatil Günleri');
                Route::delete('delete', 'delete')->name('backend.weekly_holiday_delete')->desc('Haftalık Tatil Günleri');
            });
        });
        Route::prefix('menu')->group(function () {
            Route::controller(MenuController::class)->group(function () {
                Route::any('/', 'list')->name('backend.menu_list')->desc('Menüler');
                Route::get('form/{unique?}', 'form')->name('backend.menu_form')->desc('Menüler');
                Route::post('form/{unique?}', 'save')->name('backend.menu_save')->desc('Menüler');
                Route::delete('delete', 'delete')->name('backend.menu_delete')->desc('Menüler');
            });
        });
        Route::prefix('debit/device')->group(function () {
            Route::controller(DebitDeviceController::class)->group(function () {
                Route::any('/', 'list')->name('backend.debit_device_list')->desc('Zimmet Cihazları');
                Route::get('form/{unique?}', 'form')->name('backend.debit_device_form')->desc('Zimmet Cihazları');
                Route::post('form/{unique?}', 'save')->name('backend.debit_device_save')->desc('Zimmet Cihazları');
                Route::delete('delete', 'delete')->name('backend.debit_device_delete')->desc('Zimmet Cihazları');
            });
        });

        Route::prefix('shift/follow')->group(function () {
            Route::controller(ShiftFollowController::class)->group(function () {
                Route::any('/', 'list')->name('backend.shift_follow_list')->desc('Giriş-Çıkış Kayıtları');
                Route::get('form/{unique?}', 'form')->name('backend.shift_follow_form')->desc('Giriş-Çıkış Kayıtları');
                Route::post('form/{unique?}', 'save')->name('backend.shift_follow_save')->desc('Giriş-Çıkış Kayıtları');
                Route::delete('delete', 'delete')->name('backend.shift_follow_delete')->desc('Giriş-Çıkış Kayıtları');
            });
        });

        Route::prefix('user/shift/custom')->group(function () {
            Route::controller(UserShiftCustomController::class)->group(function () {
                Route::any('/', 'list')->name('backend.user_shift_custom_list')->desc('Özel Vardiya Atama');
                Route::get('form/{unique?}', 'add')->name('backend.user_shift_custom_form')->desc('Özel Vardiya Atama');
                Route::post('save-bulk', 'saveBulk')->name('backend.user_shift_custom_save_bulk')->desc('Özel Vardiya Atama');
                Route::delete('delete', 'delete')->name('backend.user_shift_custom_delete')->desc('Özel Vardiya Atama');
            });
        });

        Route::prefix('user/debit/device')->group(function () {
            Route::controller(UserDebitDeviceController::class)->group(function () {
                Route::any('/', 'list')->name('backend.user_debit_device_list')->desc('Zimmet Atamaları');
                Route::get('form/{unique?}', 'form')->name('backend.user_debit_device_form')->desc('Zimmet Atamaları');
                Route::post('form/{unique?}', 'save')->name('backend.user_debit_device_save')->desc('Zimmet Atamaları');
                Route::delete('delete', 'delete')->name('backend.user_debit_device_delete')->desc('Zimmet Atamaları');
                Route::post('return', 'returnDevice')->name('backend.user_debit_device_return')->desc('Zimmet Teslim Alma');
            });
        });

        Route::prefix('holiday')->group(function () {
            Route::controller(HolidayController::class)->group(function () {
                Route::any('/', 'list')->name('backend.holiday_list')->desc('İzin Talepleri');
                Route::get('form/{unique?}', 'form')->name('backend.holiday_form')->desc('İzin Talepleri');
                Route::post('form/{unique?}', 'save')->name('backend.holiday_save')->desc('İzin Talepleri');
                Route::delete('delete', 'delete')->name('backend.holiday_delete')->desc('İzin Talepleri');
                Route::post('change-status', 'changeStatus')->name('backend.holiday_change_status')->desc('İzin Talepleri');
            });
        });
        Route::prefix('hourly-leave')->group(function () {
            Route::controller(HourlyLeaveController::class)->group(function () {
                Route::any('/', 'list')->name('backend.hourly_leave_list')->desc('Saatlik İzin Talepleri');
                Route::get('form/{unique?}', 'form')->name('backend.hourly_leave_form')->desc('Saatlik İzin Talepleri');
                Route::post('form/{unique?}', 'save')->name('backend.hourly_leave_save')->desc('Saatlik İzin Talepleri');
                Route::delete('delete', 'delete')->name('backend.hourly_leave_delete')->desc('Saatlik İzin Talepleri');
                Route::post('change-status', 'changeStatus')->name('backend.hourly_leave_change_status')->desc('Saatlik İzin Talepleri');
            });
        });

        Route::prefix('official-holiday')->group(function () {
            Route::controller(OfficialHolidayController::class)->group(function () {
                Route::any('/', 'list')->name('backend.official_holiday_list')->desc('Resmi Tatil Günleri');
                Route::get('calendar', 'calendar')->name('backend.official_holiday_calendar')->desc('Resmi Tatil Takvimi');
                Route::get('get-calendar-events', 'getCalendarEvents')->name('backend.official_holiday_get_events')->desc('Resmi Tatil Etkinlikleri');
                Route::get('form/{unique?}', 'form')->name('backend.official_holiday_form')->desc('Resmi Tatil Günleri');
                Route::post('form/{unique?}', 'save')->name('backend.official_holiday_save')->desc('Resmi Tatil Günleri');
                Route::delete('delete', 'delete')->name('backend.official_holiday_delete')->desc('Resmi Tatil Günleri');
                Route::post('fetch-official-holidays', 'fetchOfficialHolidays')->name('backend.official_holiday_fetch')->desc('Resmi Tatil Günleri');
                Route::post('bulk-add-to-users', 'bulkAddToUsers')->name('backend.official_holiday_bulk_add')->desc('Resmi Tatil Günleri');
            });
        });

        // Dosya Tipleri için Route'lar
        Route::prefix('file/type')->group(function () {
            Route::controller(FileTypeController::class)->group(function () {
                Route::any('/', 'list')->name('backend.file_type_list')->desc('Dosya Tipleri');
                Route::get('form/{unique?}', 'form')->name('backend.file_type_form')->desc('Dosya Tipleri');
                Route::post('form/{unique?}', 'save')->name('backend.file_type_save')->desc('Dosya Tipleri');
                Route::delete('delete', 'delete')->name('backend.file_type_delete')->desc('Dosya Tipleri');
            });
        });

        // Personel Dosyaları için Route'lar
        Route::prefix('user/file')->group(function () {
            Route::controller(UserFileController::class)->group(function () {
                Route::any('/', 'list')->name('backend.user_file_list')->desc('Personel Dosyaları');
                Route::get('form/{unique?}', 'form')->name('backend.user_file_form')->desc('Personel Dosyaları');
                Route::post('form/{unique?}', 'save')->name('backend.user_file_save')->desc('Personel Dosyaları');
                Route::delete('delete', 'delete')->name('backend.user_file_delete')->desc('Personel Dosyaları');
                Route::post('upload-temp', 'uploadTemp')->name('backend.user_file_upload_temp')->desc('Personel Dosyaları Geçici Yükleme');
                Route::post('delete-temp-file', 'deleteTempFile')->name('backend.user_file_delete_temp')->desc('Personel Dosyaları Geçici Silme');
            });
        });

        Route::prefix('announcements')->group(function () {
            Route::controller(AnnouncementController::class)->group(function () {
                Route::any('/', 'list')->name('backend.announcements_list')->desc('Duyurular');
                Route::get('form/{unique?}', 'form')->name('backend.announcements_form')->desc('Duyuru Formu');
                Route::post('form/{unique?}', 'save')->name('backend.announcements_save')->desc('Duyuru Kaydet');
                Route::delete('delete', 'delete')->name('backend.announcements_delete')->desc('Duyuru Sil');
                Route::post('get-users', 'getUsers')->name('backend.announcements_get_users')->desc('Duyuru Kullanıcıları');
            });
        });

        Route::prefix('event')->group(function () {
            Route::controller(EventController::class)->group(function () {
                Route::any('/', 'list')->name('backend.event_list')->desc('Etkinlikler');
                Route::get('form/{unique?}', 'form')->name('backend.event_form')->desc('Etkinlik Formu');
                Route::post('form/{unique?}', 'save')->name('backend.event_save')->desc('Etkinlik Kaydet');
                Route::delete('delete', 'delete')->name('backend.event_delete')->desc('Etkinlik Sil');
                Route::get('participants/{eventId}', 'participants')->name('backend.event_participants')->desc('Etkinlik Katılımcıları');
                Route::post('participant-status', 'participantStatus')->name('backend.event_participant_status')->desc('Katılımcı Durumu Güncelle');
                Route::post('participant-bulk-status', 'participantBulkStatus')->name('backend.event_participant_bulk_status')->desc('Toplu Katılımcı Durumu Güncelle');
            });
        });
    });
});

Route::prefix('authentication')->group(function () {
    Route::controller(AuthenticationController::class)->group(function () {
        Route::get('/forgotpassword', 'forgotPassword')->name('forgotPassword');
        Route::get('/signin', 'signin')->name('signin');
        Route::get('/signup', 'signup')->name('signup');
        Route::post('login', 'access')->name('signin.post');
        Route::get('logout', 'logout')->name('logout');
    });
});
