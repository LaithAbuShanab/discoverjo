<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\User\AuthUser\AuthUserController;
use App\Http\Controllers\Api\User\AuthUser\EmailVerificationNotificationController;
use App\Http\Controllers\Api\User\AuthUser\NewPasswordController;
use App\Http\Controllers\Api\User\AuthUser\PasswordResetLinkController;
use App\Http\Controllers\Api\User\AuthUser\VerifyEmailController;
use App\Http\Controllers\Api\User\AuthUser\CurrentUserNewPasswordController;

Route::post('/login', [AuthUserController::class, 'login'])->name('login')->withoutMiddleware(['inactiveUser','sanitize']);
Route::post('/register', [AuthUserController::class, 'register'])->name('register')->withoutMiddleware('sanitize');;
Route::post('/logout', [AuthUserController::class, 'logout'])->middleware('auth:api')->name('logout')->withoutMiddleware('sanitize');;;

//enhance the process of socialite
Route::get('/auth/{provider}', [AuthUserController::class, 'redirectToProvider'])->withoutMiddleware(['apiKey','sanitize']);
Route::get('/auth/{provider}/callback', [AuthUserController::class, 'handleProviderCallback'])->withoutMiddleware(['apiKey','sanitize']);


Route::get('/email/verify/{id}/{hash}', VerifyEmailController::class)->middleware('signed')->name('verification.verify')->withoutMiddleware(['apiKey','sanitize']);
Route::post('/email/verification-notification', EmailVerificationNotificationController::class)->name('verification.send')->middleware('auth:api')->withoutMiddleware(['apiKey','sanitize']);


Route::post('/forgot-password', PasswordResetLinkController::class)->name('password.email')->withoutMiddleware('sanitize');
Route::get('user/reset-password/{token}', [AuthUserController::class, 'resetPassword'])->name('password.reset')->withoutMiddleware(['apiKey','sanitize']);
Route::post('/reset-password', NewPasswordController::class)->name('password.store')->withoutMiddleware(['apiKey','sanitize']);
Route::post('/change-password', CurrentUserNewPasswordController::class)->middleware('auth:api')->withoutMiddleware('sanitize');
