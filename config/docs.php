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

    'path' => env('DOCS_PATH'),

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
