<?php

use App\Livewire\DocsViewer;
use App\Services\CommandCheatSheetService;
use Illuminate\Support\Facades\Route;

Route::get('/', DocsViewer::class)->name('home');
Route::redirect('/docs', '/', 301);
Route::redirect('/docs/', '/', 301);
Route::get('/docs/{page}', DocsViewer::class)
    ->name('docs.show')
    ->where(['page' => '[a-z0-9-]+']);

Route::get('/cheat-sheet', function (CommandCheatSheetService $sheet) {
    $cheatSheet = $sheet->build();

    return view('cheat-sheet', $cheatSheet);
})->name('cheat-sheet');
