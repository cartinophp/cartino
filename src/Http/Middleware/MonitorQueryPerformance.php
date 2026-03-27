<?php

declare(strict_types=1);

namespace Cartino\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

/**
 * Middleware per monitorare N+1 queries e performance
 */
class MonitorQueryPerformance
{
    protected int $queryThreshold = 50;

    protected int $timeThreshold = 1000; // ms

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (! config('app.debug')) {
            return $next($request);
        }

        $startTime = microtime(true);
        $startQueries = count(DB::getQueryLog());

        DB::enableQueryLog();

        $response = $next($request);

        $endTime = microtime(true);
        $queries = DB::getQueryLog();
        $queryCount = count($queries) - $startQueries;
        $executionTime = ($endTime - $startTime) * 1000; // ms

        // Log se supera le soglie
        if ($queryCount > $this->queryThreshold || $executionTime > $this->timeThreshold) {
            Log::warning('Performance issue detected', [
                'url' => $request->fullUrl(),
                'method' => $request->method(),
                'query_count' => $queryCount,
                'execution_time_ms' => round($executionTime, 2),
                'threshold_exceeded' => [
                    'queries' => $queryCount > $this->queryThreshold,
                    'time' => $executionTime > $this->timeThreshold,
                ],
            ]);

            // Detect N+1
            $this->detectNPlusOne($queries, $request);
        }

        // Add headers per debugging
        $response->headers->set('X-Query-Count', $queryCount);
        $response->headers->set('X-Execution-Time', round($executionTime, 2).'ms');

        return $response;
    }

    /**
     * Rileva potenziali N+1 queries
     */
    protected function detectNPlusOne(array $queries, Request $request): void
    {
        $similarQueries = [];

        foreach ($queries as $query) {
            // Normalizza la query rimuovendo i binding
            $normalized = preg_replace('/\d+/', '?', $query['query']);

            if (! isset($similarQueries[$normalized])) {
                $similarQueries[$normalized] = 0;
            }

            $similarQueries[$normalized]++;
        }

        // Se una query simile si ripete molte volte, probabile N+1
        foreach ($similarQueries as $query => $count) {
            if ($count > 10) {
                Log::warning('Possible N+1 query detected', [
                    'url' => $request->fullUrl(),
                    'query' => $query,
                    'count' => $count,
                    'suggestion' => 'Consider using eager loading with ->with()',
                ]);
            }
        }
    }
}
