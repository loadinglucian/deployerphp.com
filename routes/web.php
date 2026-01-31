<?php

use App\Http\Controllers\HomeController;
use App\Livewire\DocsViewer;
use Illuminate\Support\Facades\Route;

Route::get('/', HomeController::class)->name('home');
Route::get('/docs/{page?}', DocsViewer::class)
    ->name('docs.show')
    ->where(['page' => '[a-z0-9-]+']);
