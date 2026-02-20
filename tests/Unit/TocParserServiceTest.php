<?php

declare(strict_types=1);

use App\Services\DocsPathService;
use App\Services\TocParserService;
use Illuminate\Support\Facades\File;
use Tests\Concerns\UsesDocsFixtures;

uses(UsesDocsFixtures::class);

beforeEach(function (): void {
    $this->configureDocsFixtures();
});

it('parses grouped toc sections from fixture docs', function (): void {
    $toc = app(TocParserService::class)->parse();

    expect($toc)->toBe([
        [
            'name' => 'Guides',
            'anchor' => 'guides',
            'links' => [
                ['title' => 'Introduction', 'path' => ''],
                ['title' => 'Installation', 'path' => 'installation'],
                ['title' => 'Link Behavior', 'path' => 'link-behavior'],
            ],
        ],
        [
            'name' => 'References',
            'anchor' => 'references',
            'links' => [
                ['title' => 'Command Index', 'path' => 'command-index'],
                ['title' => 'Operations', 'path' => 'runbooks'],
            ],
        ],
    ]);
});

it('returns an empty toc when documentation file is missing', function (): void {
    config()->set('docs.path', storage_path('framework/testing/missing-docs-'.uniqid('', true)));
    app()->forgetInstance(DocsPathService::class);
    app()->forgetInstance(TocParserService::class);

    expect(app(TocParserService::class)->parse())->toBe([]);
});

it('parses flat toc links into the default documentation section', function (): void {
    $docsPath = storage_path('framework/testing/docs-flat-'.uniqid('', true));

    File::ensureDirectoryExists($docsPath);
    File::put("{$docsPath}/documentation.md", "- [Readme](README.md)\n- [Guide](guides/guide.md)\n");

    config()->set('docs.path', $docsPath);
    app()->forgetInstance(DocsPathService::class);
    app()->forgetInstance(TocParserService::class);

    $toc = app(TocParserService::class)->parse();

    expect($toc)->toBe([
        [
            'name' => 'Documentation',
            'anchor' => 'documentation',
            'links' => [
                ['title' => 'Readme', 'path' => ''],
                ['title' => 'Guide', 'path' => 'guide'],
            ],
        ],
    ]);

    File::deleteDirectory($docsPath);
});

it('memoizes parsed toc values per service instance', function (): void {
    $docsPath = storage_path('framework/testing/docs-memoized-'.uniqid('', true));
    File::ensureDirectoryExists($docsPath);
    File::put("{$docsPath}/documentation.md", "- [Original](original.md)\n");

    config()->set('docs.path', $docsPath);
    app()->forgetInstance(DocsPathService::class);
    app()->forgetInstance(TocParserService::class);

    $service = app(TocParserService::class);

    $first = $service->parse();

    File::put("{$docsPath}/documentation.md", "- [Changed](changed.md)\n");

    $second = $service->parse();

    expect($second)->toBe($first);

    File::deleteDirectory($docsPath);
});
