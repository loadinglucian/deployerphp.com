<?php

declare(strict_types=1);

arch()
    ->expect('App')
    ->not->toUse([
        'dd',
        'dump',
        'var_dump',
        'print_r',
        'die',
        'exit',
    ]);

arch()->preset()->php();
arch()->preset()->laravel();

arch()
    ->expect('App\\Http\\Controllers')
    ->toHaveSuffix('Controller')
    ->toExtend(\App\Http\Controllers\Controller::class);

arch()
    ->expect('App\\Livewire')
    ->toExtend(\Livewire\Component::class);

arch()
    ->expect('App\\Services')
    ->toHaveSuffix('Service');

arch()
    ->expect('App\\Repositories')
    ->toHaveSuffix('Repository')
    ->toImplement(\App\Contracts\RepositoryInterface::class);

arch()
    ->expect('App\\Models')
    ->toExtend(\Illuminate\Database\Eloquent\Model::class);
