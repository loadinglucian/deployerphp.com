<?php

declare(strict_types=1);

namespace App\Livewire;

use App\Services\DocsOutputCacheService;
use App\Services\DocumentService;
use App\Services\TocParserService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Exceptions\HttpResponseException;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Symfony\Component\HttpFoundation\RedirectResponse;

#[Layout('components.layouts.docs')]
final class DocsViewer extends Component
{
    public string $page = '';

    public string $title = '';

    public string $content = '';

    /** @var array<int, array{level: int, text: string, id: string}> */
    public array $headings = [];

    /**
     * @var array<int, array{
     *     name: string,
     *     anchor: string,
     *     links: array<int, array{title: string, path: string}>
     * }>
     */
    public array $toc = [];

    public function mount(
        DocsOutputCacheService $docsOutputCache,
        TocParserService $tocParser,
        DocumentService $documentService,
        ?string $page = null,
    ): void {
        $routeIdentity = $page === null || $page === '' ? 'home' : "docs:{$page}";

        /** @var array{
         *     page: string,
         *     title: string,
         *     content: string,
         *     headings: array<int, array{level: int, text: string, id: string}>,
         *     toc: array<int, array{
         *         name: string,
         *         anchor: string,
         *         links: array<int, array{title: string, path: string}>
         *     }>
         * } $payload
         */
        $payload = $docsOutputCache->remember(
            $docsOutputCache->key('docs-viewer', [$routeIdentity]),
            fn (): array => $this->buildPayload($tocParser, $documentService, $page),
        );

        $this->hydrateFromPayload($payload);
    }

    public function render(): View
    {
        return view('livewire.docs-viewer');
    }

    /**
     * @return array{
     *     page: string,
     *     title: string,
     *     content: string,
     *     headings: array<int, array{level: int, text: string, id: string}>,
     *     toc: array<int, array{
     *         name: string,
     *         anchor: string,
     *         links: array<int, array{title: string, path: string}>
     *     }>
     * }
     */
    private function buildPayload(
        TocParserService $tocParser,
        DocumentService $documentService,
        ?string $page,
    ): array {
        $toc = $tocParser->parse();

        if ($page === null || $page === '') {
            $readme = $documentService->loadReadme();

            if ($readme === null) {
                throw new HttpResponseException(new RedirectResponse('/', 301));
            }

            return [
                'page' => '',
                'title' => $readme['title'],
                'content' => $readme['content'],
                'headings' => $readme['headings'],
                'toc' => $toc,
            ];
        }

        $document = $documentService->load($page);

        if ($document === null) {
            throw new HttpResponseException(new RedirectResponse('/', 301));
        }

        return [
            'page' => $page,
            'title' => $document['title'],
            'content' => $document['content'],
            'headings' => $document['headings'],
            'toc' => $toc,
        ];
    }

    /**
     * @param  array{
     *     page: string,
     *     title: string,
     *     content: string,
     *     headings: array<int, array{level: int, text: string, id: string}>,
     *     toc: array<int, array{
     *         name: string,
     *         anchor: string,
     *         links: array<int, array{title: string, path: string}>
     *     }>
     * }  $payload
     */
    private function hydrateFromPayload(array $payload): void
    {
        $this->page = $payload['page'];
        $this->title = $payload['title'];
        $this->content = $payload['content'];
        $this->headings = $payload['headings'];
        $this->toc = $payload['toc'];
    }
}
