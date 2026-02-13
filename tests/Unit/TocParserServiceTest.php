<?php

declare(strict_types=1);

use App\Services\DocsPathService;
use App\Services\TocParserService;

it('parses grouped toc sections and maps readme links to root path', function (): void {
    ensureDocsPathConfigured();

    $toc = (new TocParserService(new DocsPathService))->parse();

    expect($toc)->toHaveCount(2)
        ->and($toc[0]['name'])->toBe('Guides')
        ->and($toc[0]['links'])->toBe([
            ['title' => 'Introduction', 'path' => ''],
            ['title' => 'Installation', 'path' => 'installation'],
            ['title' => 'Zero to Deploy', 'path' => 'zero-to-deploy'],
        ])
        ->and($toc[1]['name'])->toBe('References')
        ->and($toc[1]['links'])->toBe([
            ['title' => 'Managing Sites', 'path' => 'managing-sites'],
            ['title' => 'Managing Servers', 'path' => 'managing-servers'],
            ['title' => 'Managing Services', 'path' => 'managing-services'],
            ['title' => 'Managing Databases', 'path' => 'managing-databases'],
            ['title' => 'Cloud Providers', 'path' => 'cloud-providers'],
            ['title' => 'Automation & AI', 'path' => 'automation'],
        ]);
});
