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

        $parts = explode('/', $firstPath);

        if (count($parts) !== 2) {
            throw new NotFoundHttpException('Invalid documentation structure.');
        }

        return redirect()->route('docs.show', [
            'section' => $parts[0],
            'page' => $parts[1],
        ]);
    }

    /**
     * Show a documentation page.
     */
    public function show(string $section, string $page): View
    {
        $document = $this->documentService->load($section, $page);

        if ($document === null) {
            throw new NotFoundHttpException("Documentation page not found: {$section}/{$page}");
        }

        $toc = $this->tocParser->parse();
        $currentPath = "{$section}/{$page}";

        return view('docs.show', [
            'title' => $document['title'],
            'content' => $document['content'],
            'headings' => $document['headings'],
            'toc' => $toc,
            'currentPath' => $currentPath,
        ]);
    }
}
