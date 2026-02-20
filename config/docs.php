<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Documentation Path
    |--------------------------------------------------------------------------
    |
    | The path to the documentation directory containing markdown files.
    | This can be an absolute path or a path relative to the project root.
    |
    | Examples:
    |   Absolute: /Users/lucian/Developer/deployer-php/docs
    |   Relative: vendor/loadinglucian/deployer-php/docs
    |
    */

    'path' => env('DOCS_PATH', 'vendor/loadinglucian/deployer-php/docs'),

    /*
    |--------------------------------------------------------------------------
    | Docs Output Cache
    |--------------------------------------------------------------------------
    |
    | Shared output cache for docs pages and command index payloads.
    | This cache is file-backed and can be cleared via optimize:clear.
    |
    */

    'cache' => [
        'enabled' => (bool) env('DOCS_CACHE_ENABLED', env('APP_ENV', 'local') !== 'local'),
        'path' => env('DOCS_CACHE_PATH', storage_path('framework/cache/docs-output')),
        'version' => env('DOCS_CACHE_VERSION', 'v1'),
        'lock_timeout_seconds' => (int) env('DOCS_CACHE_LOCK_TIMEOUT_SECONDS', 5),
    ],

    /*
    |--------------------------------------------------------------------------
    | GitHub Repository
    |--------------------------------------------------------------------------
    |
    | Configuration for transforming relative links in documentation to
    | GitHub blob URLs. Relative links like `section/file.md` will be
    | converted to full GitHub URLs pointing to the source repository.
    |
    */

    'github' => [
        'repo' => env('DOCS_GITHUB_REPO', 'loadinglucian/deployer-php'),
        'branch' => env('DOCS_GITHUB_BRANCH', 'main'),
        'dir' => env('DOCS_GITHUB_DIR', 'docs'),
    ],

];
