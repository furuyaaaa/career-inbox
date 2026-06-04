<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\GmailController;
use App\Http\Controllers\JobPostController;
use App\Http\Controllers\PreferenceProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::middleware('guest')->group(function (): void {
    Route::get('login', [AuthController::class, 'login'])->name('login');
    Route::post('login', [AuthController::class, 'authenticate'])->name('login.authenticate');
    Route::post('login/demo', [AuthController::class, 'demoLogin'])->name('login.demo');
    Route::get('register', [AuthController::class, 'register'])->name('register');
    Route::post('register', [AuthController::class, 'store'])->name('register.store');
});

Route::post('logout', [AuthController::class, 'logout'])
    ->middleware('auth')
    ->name('logout');

Route::middleware('auth')->group(function (): void {
    Route::resource('jobs', JobPostController::class);
    Route::get('preferences', [PreferenceProfileController::class, 'edit'])->name('preferences.edit');
    Route::put('preferences', [PreferenceProfileController::class, 'update'])->name('preferences.update');
    Route::get('gmail', [GmailController::class, 'index'])->name('gmail.index');
    Route::get('gmail/connect', [GmailController::class, 'connect'])->name('gmail.connect');
    Route::get('gmail/callback', [GmailController::class, 'callback'])->name('gmail.callback');
    Route::post('gmail/import', [GmailController::class, 'import'])->name('gmail.import');
    Route::post('gmail/demo-import', [GmailController::class, 'demoImport'])->name('gmail.demo-import');
});
