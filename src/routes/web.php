<?php

use App\Http\Controllers\GmailController;
use App\Http\Controllers\JobPostController;
use App\Http\Controllers\PreferenceProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::resource('jobs', JobPostController::class);
Route::get('preferences', [PreferenceProfileController::class, 'edit'])->name('preferences.edit');
Route::put('preferences', [PreferenceProfileController::class, 'update'])->name('preferences.update');
Route::get('gmail', [GmailController::class, 'index'])->name('gmail.index');
Route::get('gmail/connect', [GmailController::class, 'connect'])->name('gmail.connect');
Route::get('gmail/callback', [GmailController::class, 'callback'])->name('gmail.callback');
Route::post('gmail/import', [GmailController::class, 'import'])->name('gmail.import');
Route::post('gmail/demo-import', [GmailController::class, 'demoImport'])->name('gmail.demo-import');
