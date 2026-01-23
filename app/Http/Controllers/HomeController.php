<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;

final class HomeController extends Controller
{
    /**
     * Redirect to documentation index.
     */
    public function __invoke(): RedirectResponse
    {
        return redirect()->route('docs.index');
    }
}
