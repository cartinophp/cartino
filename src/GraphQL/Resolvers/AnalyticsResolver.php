<?php

namespace Cartino\GraphQL\Resolvers;

use Carbon\Carbon;
use Cartino\Models\AnalyticsEvent;
use GraphQL\Type\Definition\ResolveInfo;
use Nuwave\Lighthouse\Support\Contracts\GraphQLContext;

class AnalyticsResolver
{
    public function trackEvent(
        $rootValue,
        array $args,
        GraphQLContext $context,
        ResolveInfo $resolveInfo,
    ): AnalyticsEvent {
        $input = $args['input'] ?? $args;

        return AnalyticsEvent::track(
            $input['eventType'],
            $input['properties'] ?? [],
            $input['context'] ?? [],
            $input['sessionId'] ?? null,
            $input['userId'] ?? null,
        );
    }

    public function getMetrics($rootValue, array $args, GraphQLContext $context, ResolveInfo $resolveInfo): array
    {
        $dateFrom = Carbon::parse($args['dateFrom']);
        $dateTo = Carbon::parse($args['dateTo']);
        $eventTypes = $args['eventTypes'] ?? [];
        $groupBy = $args['groupBy'] ?? 'DAY';

        $query = AnalyticsEvent::query()->whereBetween('occurred_at', [$dateFrom, $dateTo]);

        if (! empty($eventTypes)) {
            $query->whereIn('event_type', $eventTypes);
        }

        $dateFormat = match ($groupBy) {
            'DAY' => '%Y-%m-%d',
            'WEEK' => '%Y-%u',
            'MONTH' => '%Y-%m',
            'YEAR' => '%Y',
            default => '%Y-%m-%d',
        };

        $results = $query
            ->selectRaw("
                DATE_FORMAT(occurred_at, '{$dateFormat}') as date,
                event_type,
                COUNT(*) as count,
                COUNT(DISTINCT user_id) as unique_users,
                COUNT(DISTINCT session_id) as unique_sessions
            ")
            ->groupBy(['date', 'event_type'])
            ->orderBy('date')
            ->get();

        return $results->map(function ($result) {
            return [
                'date' => $result->date,
                'eventType' => $result->event_type,
                'count' => (int) $result->count,
                'uniqueUsers' => (int) $result->unique_users,
                'uniqueSessions' => (int) $result->unique_sessions,
                'properties' => null, // Could be extended to aggregate properties
            ];
        })->toArray();
    }
}
