<?php

use App\Livewire\CommandIndex;
use App\Livewire\DocsViewer;
use Illuminate\Support\Facades\Route;

Route::get('/', DocsViewer::class)->name('home');
Route::redirect('/docs', '/', 301);
Route::redirect('/docs/', '/', 301);
Route::get('/docs/{page}', DocsViewer::class)
    ->name('docs.show')
    ->where(['page' => '[a-z0-9-]+']);

Route::get('/command-index', CommandIndex::class)->name('command-index');
