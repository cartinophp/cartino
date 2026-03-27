<?php

declare(strict_types=1);

namespace Cartino\Http\Controllers\Api\Data;

use Cartino\Http\Controllers\Api\ApiController;
use Cartino\Models\DictionaryItem;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Validation\Rule;

class DictionaryItemsController extends ApiController
{
    /**
     * List all custom dictionary items for a dictionary
     */
    public function index(string $handle): JsonResponse
    {
        $items = DictionaryItem::forDictionary($handle)
            ->orderBy('order')
            ->orderBy('created_at')
            ->get()
            ->map(fn ($item) => [
                'id' => $item->id,
                'value' => $item->value,
                'label' => $item->label,
                'extra' => $item->extra,
                'order' => $item->order,
                'is_enabled' => $item->is_enabled,
                'is_system' => $item->is_system,
                'created_at' => $item->created_at?->toISOString(),
                'updated_at' => $item->updated_at?->toISOString(),
            ]);

        return $this->successResponse($items);
    }

    /**
     * Create a new custom dictionary item
     */
    public function store(Request $request, string $handle): JsonResponse
    {
        // Check if dictionary is extensible
        if (! $this->isExtensible($handle)) {
            return $this->errorResponse("Dictionary '{$handle}' is not extensible", 422);
        }

        $validated = $request->validate([
            'value' => [
                'required',
                'string',
                'max:255',
                Rule::unique('dictionary_items')->where('dictionary', $handle),
            ],
            'label' => ['required', 'string', 'max:255'],
            'extra' => ['nullable', 'array'],
            'order' => ['nullable', 'integer', 'min:0'],
            'is_enabled' => ['nullable', 'boolean'],
        ]);

        $item = DictionaryItem::create([
            'dictionary' => $handle,
            'value' => $validated['value'],
            'label' => $validated['label'],
            'extra' => $validated['extra'] ?? [],
            'order' => $validated['order'] ?? 0,
            'is_enabled' => $validated['is_enabled'] ?? true,
            'is_system' => false,
        ]);

        // Clear cache
        $this->clearDictionaryCache($handle);

        return $this->created([
            'id' => $item->id,
            'value' => $item->value,
            'label' => $item->label,
            'extra' => $item->extra,
            'order' => $item->order,
            'is_enabled' => $item->is_enabled,
        ], 'Dictionary item created successfully');
    }

    /**
     * Update a dictionary item
     */
    public function update(Request $request, string $handle, int $id): JsonResponse
    {
        $item = DictionaryItem::forDictionary($handle)->findOrFail($id);

        // Prevent updating system items
        if ($item->is_system) {
            return $this->errorResponse('Cannot update system dictionary items', 422);
        }

        $validated = $request->validate([
            'value' => [
                'sometimes',
                'string',
                'max:255',
                Rule::unique('dictionary_items')->where('dictionary', $handle)->ignore($id),
            ],
            'label' => ['sometimes', 'string', 'max:255'],
            'extra' => ['sometimes', 'array'],
            'order' => ['sometimes', 'integer', 'min:0'],
            'is_enabled' => ['sometimes', 'boolean'],
        ]);

        $item->update($validated);

        // Clear cache
        $this->clearDictionaryCache($handle);

        return $this->successResponse([
            'id' => $item->id,
            'value' => $item->value,
            'label' => $item->label,
            'extra' => $item->extra,
            'order' => $item->order,
            'is_enabled' => $item->is_enabled,
        ], 'Dictionary item updated successfully');
    }

    /**
     * Delete a dictionary item
     */
    public function destroy(string $handle, int $id): JsonResponse
    {
        $item = DictionaryItem::forDictionary($handle)->findOrFail($id);

        // Prevent deleting system items
        if ($item->is_system) {
            return $this->errorResponse('Cannot delete system dictionary items', 422);
        }

        $item->delete();

        // Clear cache
        $this->clearDictionaryCache($handle);

        return $this->successResponse(null, 'Dictionary item deleted successfully');
    }

    /**
     * Reorder dictionary items
     */
    public function reorder(Request $request, string $handle): JsonResponse
    {
        $validated = $request->validate([
            'items' => ['required', 'array'],
            'items.*.id' => ['required', 'integer', 'exists:dictionary_items,id'],
            'items.*.order' => ['required', 'integer', 'min:0'],
        ]);

        foreach ($validated['items'] as $itemData) {
            DictionaryItem::where('id', $itemData['id'])
                ->where('dictionary', $handle)
                ->update(['order' => $itemData['order']]);
        }

        // Clear cache
        $this->clearDictionaryCache($handle);

        return $this->successResponse(null, 'Dictionary items reordered successfully');
    }

    /**
     * Toggle enabled status
     */
    public function toggle(string $handle, int $id): JsonResponse
    {
        $item = DictionaryItem::forDictionary($handle)->findOrFail($id);

        // Prevent disabling system items
        if ($item->is_system && $item->is_enabled) {
            return $this->errorResponse('Cannot disable system dictionary items', 422);
        }

        $item->update(['is_enabled' => ! $item->is_enabled]);

        // Clear cache
        $this->clearDictionaryCache($handle);

        return $this->successResponse([
            'id' => $item->id,
            'is_enabled' => $item->is_enabled,
        ], 'Dictionary item status toggled successfully');
    }

    /**
     * Check if dictionary is extensible
     */
    private function isExtensible(string $handle): bool
    {
        $extensible = config('cartino.extensible_dictionaries', [
            'address_types',
            'payment_providers',
            'shipping_types',
            'order_statuses',
            'payment_statuses',
            'units',
        ]);

        return in_array($handle, $extensible);
    }

    /**
     * Clear dictionary cache
     */
    private function clearDictionaryCache(string $handle): void
    {
        Cache::forget("dictionary:{$handle}:all");
        Cache::tags(['dictionaries', "dictionary:{$handle}"])->flush();
    }
}
