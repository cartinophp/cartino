<?php

declare(strict_types=1);

namespace Cartino\Http\Middleware;

use Cartino\Services\VocabularyService;
use Illuminate\Http\Request;
use Inertia\Inertia;

/**
 * ShareVocabularies Middleware
 *
 * Shares vocabulary data with Inertia for use in Vue/React components.
 */
class ShareVocabularies
{
    public function __construct(
        protected VocabularyService $vocabularyService
    ) {}

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, \Closure $next): mixed
    {
        Inertia::share([
            'vocabularies' => fn () => $this->vocabularyService->getCommonVocabularies(),
        ]);

        return $next($request);
    }
}
