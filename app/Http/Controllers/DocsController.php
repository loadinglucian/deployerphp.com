<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Services\DocumentService;
use App\Services\TocParserService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

final class DocsController extends Controller
{
    public function __construct(
        private readonly TocParserService $tocParser,
        private readonly DocumentService $documentService,
    ) {}

    /**
     * Show the README or redirect to the first document.
     */
    public function index(): View|RedirectResponse
    {
        $readme = $this->documentService->loadReadme();

        if ($readme !== null) {
            $toc = $this->tocParser->parse();

            return view('docs.show', [
                'title' => $readme['title'],
                'content' => $readme['content'],
                'headings' => $readme['headings'],
                'toc' => $toc,
                'currentPath' => null,
            ]);
        }

        // Fallback: redirect to first doc if README doesn't exist
        $firstPath = $this->tocParser->firstDocPath();

        if ($firstPath === null) {
            throw new NotFoundHttpException('No documentation found.');
        }

        return redirect()->route('docs.show', ['page' => $firstPath]);
    }

    /**
     * Show a documentation page.
     */
    public function show(string $page): View
    {
        $document = $this->documentService->load($page);

        if ($document === null) {
            throw new NotFoundHttpException("Documentation page not found: {$page}");
        }

        $toc = $this->tocParser->parse();

        return view('docs.show', [
            'title' => $document['title'],
            'content' => $document['content'],
            'headings' => $document['headings'],
            'toc' => $toc,
            'currentPath' => $page,
        ]);
    }
}
