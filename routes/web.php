<?php

use App\Livewire\DocsViewer;
use Illuminate\Support\Facades\Route;

Route::get('/', DocsViewer::class)->name('home');
Route::redirect('/docs', '/', 301);
Route::redirect('/docs/', '/', 301);
Route::get('/docs/{page}', DocsViewer::class)
    ->name('docs.show')
    ->where(['page' => '[a-z0-9-]+']);
