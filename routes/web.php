<?php

use App\Http\Controllers\Auth\EmailVerificationController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\PasswordResetController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Desk\AccountController;
use App\Http\Controllers\Desk\DeskController;
use App\Http\Controllers\Desk\DriverRosterController;
use App\Http\Controllers\Desk\TaskBoardController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\RunSheetController;
use App\Http\Controllers\ZoneController;
use App\Http\Middleware\EnsureAccountIsActive;
use App\Http\Middleware\EnsureRole;
use App\Models\User;
use Illuminate\Support\Facades\Route;

// The front door: everything else needs a sign-in, so just send people on.
Route::get('/', fn () => redirect()->route('home'));

/*
|--------------------------------------------------------------------------
| Feature 1 and 5: pages for people who are NOT signed in
|--------------------------------------------------------------------------
*/
Route::middleware('guest')->group(function () {
    Route::get('/register', [RegisterController::class, 'create'])->name('register');
    Route::post('/register', [RegisterController::class, 'store'])->middleware('throttle:10,1');

    Route::get('/login', [LoginController::class, 'create'])->name('login');
    Route::post('/login', [LoginController::class, 'store'])->middleware('throttle:10,1');

    // Feature 5: password reset ("Forgot your password?")
    Route::get('/forgot-password', [PasswordResetController::class, 'showForgotForm'])->name('password.request');
    Route::post('/forgot-password', [PasswordResetController::class, 'sendLink'])
        ->middleware('throttle:6,1')->name('password.email');
    Route::get('/reset-password/{token}', [PasswordResetController::class, 'showResetForm'])->name('password.reset');
    Route::post('/reset-password', [PasswordResetController::class, 'reset'])->name('password.update');
});

/*
|--------------------------------------------------------------------------
| Everything below needs a sign-in, and an account that is not on hold
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', EnsureAccountIsActive::class])->group(function () {

    Route::post('/logout', [LoginController::class, 'destroy'])->name('logout');

    // Feature 4: email verification. These pages are for signed-in accounts that have NOT confirmed yet.
    Route::get('/email/verify', [EmailVerificationController::class, 'notice'])->name('verification.notice');
    Route::get('/email/verify/{id}/{hash}', [EmailVerificationController::class, 'verify'])
        ->middleware(['signed', 'throttle:6,1'])->name('verification.verify');
    Route::post('/email/verification-notification', [EmailVerificationController::class, 'resend'])
        ->middleware('throttle:6,1')->name('verification.send');

    // Everything below also needs a confirmed email ("verified").
    Route::middleware('verified')->group(function () {

        Route::get('/home', HomeController::class)->name('home');

        // Feature 2: the zone picker (the pick is saved in the session).
        Route::post('/zone', [ZoneController::class, 'update'])->name('zone.update');

        // Driver pages (Feature 3: role check for the default role).
        Route::middleware(EnsureRole::class.':'.User::ROLE_DRIVER)->group(function () {
            Route::get('/run-sheet', [RunSheetController::class, 'index'])->name('runsheet.index');
            Route::post('/run-sheet/{task}/confirm', [RunSheetController::class, 'confirm'])->name('runsheet.confirm');
        });

        // Feature 3: dispatcher-only pages. Anyone else gets the 403 page, even by typing the address.
        Route::middleware(EnsureRole::class.':'.User::ROLE_DISPATCHER)
            ->prefix('desk')->name('desk.')->group(function () {
                Route::get('/', [DeskController::class, 'index'])->name('index');

                Route::get('/tasks', [TaskBoardController::class, 'index'])->name('tasks');
                Route::post('/tasks', [TaskBoardController::class, 'store'])->name('tasks.store');

                Route::get('/drivers', [DriverRosterController::class, 'index'])->name('drivers');

                Route::get('/accounts', [AccountController::class, 'index'])->name('accounts');
                Route::patch('/accounts/{account}/hold', [AccountController::class, 'hold'])->name('accounts.hold');
                Route::patch('/accounts/{account}/reinstate', [AccountController::class, 'reinstate'])->name('accounts.reinstate');
            });
    });
});
