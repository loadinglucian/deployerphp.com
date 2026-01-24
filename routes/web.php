<?php

use App\Http\Controllers\DocsController;
use App\Http\Controllers\HomeController;
use Illuminate\Support\Facades\Route;

Route::get('/', HomeController::class)->name('home');
Route::get('/docs', [DocsController::class, 'index'])->name('docs.index');
Route::get('/docs/{section}/{page}', [DocsController::class, 'show'])
    ->name('docs.show')
    ->where(['section' => '[a-z0-9-]+', 'page' => '[a-z0-9-]+']);
