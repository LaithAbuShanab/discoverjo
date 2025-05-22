<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\User\AuthUser\AuthUserController;
use App\Http\Controllers\Api\User\AuthUser\EmailVerificationNotificationController;
use App\Http\Controllers\Api\User\AuthUser\NewPasswordController;
use App\Http\Controllers\Api\User\AuthUser\PasswordResetLinkController;
use App\Http\Controllers\Api\User\AuthUser\VerifyEmailController;
use App\Http\Controllers\Api\User\AuthUser\CurrentUserNewPasswordController;

Route::post('/login', [AuthUserController::class, 'login'])->name('login')->withoutMiddleware('inactiveUser');
Route::post('/register', [AuthUserController::class, 'register'])->name('register');
Route::post('/logout', [AuthUserController::class, 'logout'])->middleware('auth:api')->name('logout');

//enhance the process of socialite
Route::get('/auth/{provider}', [AuthUserController::class, 'redirectToProvider'])->withoutMiddleware('apiKey');;
Route::get('/auth/{provider}/callback', [AuthUserController::class, 'handleProviderCallback'])->withoutMiddleware('apiKey');;


Route::get('/email/verify/{id}/{hash}', VerifyEmailController::class)->middleware('signed')->name('verification.verify')->withoutMiddleware('apiKey');;
Route::post('/email/verification-notification', EmailVerificationNotificationController::class)->name('verification.send')->middleware('auth:api')->withoutMiddleware('apiKey');;


Route::post('/forgot-password', PasswordResetLinkController::class)->name('password.email');
Route::get('user/reset-password/{token}', [AuthUserController::class, 'resetPassword'])->name('password.reset')->withoutMiddleware('apiKey');
Route::post('/reset-password', NewPasswordController::class)->name('password.store')->withoutMiddleware('apiKey');;
Route::post('/change-password', CurrentUserNewPasswordController::class)->middleware('auth:api');
