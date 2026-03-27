<?php

declare(strict_types=1);

namespace Cartino\Http\Controllers\Api;

use Cartino\DTO\PricingContext;
use Cartino\Http\Controllers\Controller;
use Cartino\Http\Requests\Api\GetPriceRequest;
use Cartino\Http\Resources\PriceResource;
use Cartino\Http\Resources\PricingContextResource;
use Cartino\Models\Market;
use Cartino\Models\ProductVariant;
use Cartino\Services\PriceResolutionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PriceController extends Controller
{
    public function __construct(
        protected PriceResolutionService $priceResolution,
    ) {}

    /**
     * Get price for a product variant with context.
     */
    public function show(GetPriceRequest $request): JsonResponse
    {
        $validated = $request->validated();

        // Find variant
        $variant = $this->resolveVariant($validated);

        if (! $variant) {
            return response()->json([
                'message' => 'Product variant not found',
            ], 404);
        }

        // Build pricing context
        $context = $this->buildContext($validated);

        // Resolve price
        $price = $this->priceResolution->resolve($variant, $context);

        if (! $price) {
            return response()->json([
                'message' => 'No price found for this context',
                'data' => [
                    'variant_id' => $variant->id,
                    'sku' => $variant->sku,
                    'context' => new PricingContextResource($context),
                ],
            ], 404);
        }

        $response = [
            'variant_id' => $variant->id,
            'sku' => $variant->sku,
            'price' => new PriceResource($price),
        ];

        // Include context if requested
        if ($validated['include_context'] ?? false) {
            $response['context'] = new PricingContextResource($context);
        }

        // Include quantity tiers if requested
        if ($validated['include_tiers'] ?? false) {
            $tiers = $this->priceResolution->getTiers($variant, $context);
            $response['tiers'] = PriceResource::collection($tiers);
        }

        return response()->json(['data' => $response]);
    }

    /**
     * Get prices for multiple variants in bulk.
     */
    public function bulk(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'variant_ids' => ['required', 'array', 'min:1', 'max:100'],
            'variant_ids.*' => ['integer', 'exists:product_variants,id'],
            'market_id' => ['sometimes', 'integer', 'exists:markets,id'],
            'currency' => ['sometimes', 'string', 'size:3'],
            'quantity' => ['sometimes', 'integer', 'min:1'],
            'include_context' => ['sometimes', 'boolean'],
        ]);

        $variants = ProductVariant::whereIn('id', $validated['variant_ids'])->get();
        $context = $this->buildContext($validated);

        $prices = $this->priceResolution->resolveBulk($variants, $context);

        $response = $variants->map(function ($variant) use ($prices) {
            $price = $prices->get($variant->id);

            return [
                'variant_id' => $variant->id,
                'sku' => $variant->sku,
                'price' => $price ? new PriceResource($price) : null,
            ];
        });

        $data = ['prices' => $response];

        if ($validated['include_context'] ?? false) {
            $data['context'] = new PricingContextResource($context);
        }

        return response()->json(['data' => $data]);
    }

    /**
     * Get quantity tiers for a variant.
     */
    public function tiers(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'variant_id' => ['required', 'integer', 'exists:product_variants,id'],
            'market_id' => ['sometimes', 'integer', 'exists:markets,id'],
            'currency' => ['sometimes', 'string', 'size:3'],
        ]);

        $variant = ProductVariant::findOrFail($validated['variant_id']);
        $context = $this->buildContext($validated);

        $tiers = $this->priceResolution->getTiers($variant, $context);

        return response()->json([
            'data' => [
                'variant_id' => $variant->id,
                'sku' => $variant->sku,
                'tiers' => PriceResource::collection($tiers),
            ],
        ]);
    }

    /**
     * Calculate price with adjustments.
     */
    public function calculate(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'variant_id' => ['required', 'integer', 'exists:product_variants,id'],
            'quantity' => ['required', 'integer', 'min:1'],
            'market_id' => ['sometimes', 'integer', 'exists:markets,id'],
            'currency' => ['sometimes', 'string', 'size:3'],
            'catalog_id' => ['sometimes', 'integer', 'exists:catalogs,id'],
            'apply_catalog_adjustments' => ['sometimes', 'boolean'],
        ]);

        $variant = ProductVariant::findOrFail($validated['variant_id']);
        $context = $this->buildContext($validated);

        // Resolve with or without catalog adjustments
        if ($validated['apply_catalog_adjustments'] ?? false) {
            $price = $this->priceResolution->resolveWithAdjustments($variant, $context);
        } else {
            $price = $this->priceResolution->resolve($variant, $context);
        }

        if (! $price) {
            return response()->json([
                'message' => 'No price found',
            ], 404);
        }

        $lineTotal = $price->amount * $validated['quantity'];

        return response()->json([
            'data' => [
                'variant_id' => $variant->id,
                'quantity' => $validated['quantity'],
                'unit_price' => new PriceResource($price),
                'line_total' => [
                    'amount' => $lineTotal,
                    'formatted' => number_format($lineTotal / 100, 2),
                    'currency' => $price->currency,
                ],
            ],
        ]);
    }

    /**
     * Resolve variant from request data.
     */
    protected function resolveVariant(array $validated): ?ProductVariant
    {
        if (isset($validated['variant_id'])) {
            return ProductVariant::find($validated['variant_id']);
        }

        if (isset($validated['sku'])) {
            return ProductVariant::where('sku', $validated['sku'])->first();
        }

        return null;
    }

    /**
     * Build pricing context from request data.
     */
    protected function buildContext(array $validated): PricingContext
    {
        // Resolve market
        $market = null;
        if (isset($validated['market_id'])) {
            $market = Market::find($validated['market_id']);
        } elseif (isset($validated['market_code'])) {
            $market = Market::where('code', $validated['market_code'])->first();
        }

        return PricingContext::create(
            marketId: $market?->id,
            siteId: $validated['site_id'] ?? null,
            channelId: $validated['channel_id'] ?? null,
            catalogId: $validated['catalog_id'] ?? null,
            currency: $validated['currency'] ?? null,
            locale: $validated['locale'] ?? null,
            quantity: $validated['quantity'] ?? 1,
        );
    }
}
