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
    | Cheat Sheet
    |--------------------------------------------------------------------------
    |
    | Cache TTL for the command cheat sheet payload in seconds.
    | Set this to 0 (or a negative value) to disable caching.
    |
    */

    'cheat_sheet' => [
        'cache_ttl_seconds' => (int) env('DOCS_CHEAT_SHEET_CACHE_TTL', 300),
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
