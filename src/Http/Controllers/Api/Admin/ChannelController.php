<?php

declare(strict_types=1);

namespace Cartino\Http\Controllers\Api\Admin;

use Cartino\Http\Requests\Admin\ChannelRequest;
use Cartino\Http\Resources\ChannelResource;
use Cartino\Models\Channel;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Routing\Controller;

class ChannelController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $query = Channel::query()->with('site');

        // Filters
        if ($request->filled('site_id')) {
            $query->where('site_id', $request->site_id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', "%{$request->search}%")->orWhere('slug', 'like', "%{$request->search}%");
            });
        }

        // Sort
        $sortBy = $request->get('sort_by', 'id');
        $sortDirection = $request->get('sort_direction', 'desc');
        $query->orderBy($sortBy, $sortDirection);

        // Pagination
        $perPage = min($request->get('per_page', 15), 100);

        return ChannelResource::collection($query->paginate($perPage));
    }

    public function store(ChannelRequest $request): JsonResponse
    {
        $channel = Channel::create($request->validated());

        // If marked as default for this site, unset other defaults
        if ($channel->is_default) {
            Channel::where('site_id', $channel->site_id)
                ->where('id', '!=', $channel->id)
                ->update(['is_default' => false]);
        }

        return response()->json([
            'message' => 'Channel created successfully.',
            'data' => new ChannelResource($channel->load('site')),
        ], 201);
    }

    public function show(Channel $channel): ChannelResource
    {
        return new ChannelResource($channel->load('site'));
    }

    public function update(ChannelRequest $request, Channel $channel): JsonResponse
    {
        $channel->update($request->validated());

        // If marked as default for this site, unset other defaults
        if ($channel->is_default) {
            Channel::where('site_id', $channel->site_id)
                ->where('id', '!=', $channel->id)
                ->update(['is_default' => false]);
        }

        return response()->json([
            'message' => 'Channel updated successfully.',
            'data' => new ChannelResource($channel->fresh('site')),
        ]);
    }

    public function destroy(Channel $channel): JsonResponse
    {
        // Prevent deleting default channel
        if ($channel->is_default) {
            return response()->json([
                'message' => 'Cannot delete the default channel. Set another channel as default first.',
            ], 422);
        }

        $channel->delete();

        return response()->json([
            'message' => 'Channel deleted successfully.',
        ]);
    }

    public function setDefault(Channel $channel): JsonResponse
    {
        Channel::where('site_id', $channel->site_id)->update(['is_default' => false]);

        $channel->update(['is_default' => true]);

        return response()->json([
            'message' => 'Channel set as default successfully.',
            'data' => new ChannelResource($channel),
        ]);
    }
}
