<?php

use App\Http\Controllers\DocsController;
use App\Http\Controllers\HomeController;
use Illuminate\Support\Facades\Route;

Route::get('/', HomeController::class)->name('home');
Route::get('/docs', [DocsController::class, 'index'])->name('docs.index');
Route::get('/docs/{page}', [DocsController::class, 'show'])
    ->name('docs.show')
    ->where(['page' => '[a-z0-9-]+']);
