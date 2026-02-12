<?php

declare(strict_types=1);

namespace App\Livewire;

use App\Services\DocumentService;
use App\Services\TocParserService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Exceptions\HttpResponseException;
use Livewire\Attributes\Layout;
use Livewire\Component;

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
        TocParserService $tocParser,
        DocumentService $documentService,
        ?string $page = null,
    ): void {
        $this->toc = $tocParser->parse();

        // No page specified - show README or redirect to first doc
        if ($page === null || $page === '') {
            $readme = $documentService->loadReadme();

            if ($readme !== null) {
                $this->title = $readme['title'];
                $this->content = $readme['content'];
                $this->headings = $readme['headings'];
                $this->page = '';

                return;
            }

            throw new HttpResponseException(redirect('/', 301));
        }

        // Load the requested document
        $document = $documentService->load($page);

        if ($document === null) {
            throw new HttpResponseException(redirect('/', 301));
        }

        $this->page = $page;
        $this->title = $document['title'];
        $this->content = $document['content'];
        $this->headings = $document['headings'];
    }

    public function render(): View
    {
        return view('livewire.docs-viewer');
    }
}
