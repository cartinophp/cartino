<?php

declare(strict_types=1);

namespace Cartino\Http\Controllers\Api;

use Cartino\DTO\PricingContext;
use Cartino\Http\Controllers\Controller;
use Cartino\Http\Requests\Api\SetMarketContextRequest;
use Cartino\Http\Resources\MarketResource;
use Cartino\Http\Resources\PricingContextResource;
use Cartino\Models\Market;
use Cartino\Services\MarketConfigurationService;
use Cartino\Support\MarketRouteHelper;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class MarketController extends Controller
{
    public function __construct(
        protected MarketConfigurationService $marketConfig,
    ) {}

    /**
     * Get all available markets.
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $markets = Market::query()
            ->published()
            ->orderByDesc('priority')
            ->get();

        return MarketResource::collection($markets);
    }

    /**
     * Get a specific market by ID or code.
     */
    public function show(Request $request, string $marketIdentifier): MarketResource
    {
        $market = Market::query()
            ->where('id', $marketIdentifier)
            ->orWhere('code', strtoupper($marketIdentifier))
            ->orWhere('handle', $marketIdentifier)
            ->published()
            ->with(['sites', 'catalog'])
            ->firstOrFail();

        return new MarketResource($market);
    }

    /**
     * Get current market from session/context.
     */
    public function current(Request $request): JsonResponse
    {
        $market = MarketRouteHelper::current();

        if (! $market) {
            return response()->json([
                'message' => 'No market context set',
                'data' => null,
            ], 404);
        }

        return response()->json([
            'data' => new MarketResource($market),
        ]);
    }

    /**
     * Set market context in session.
     */
    public function setContext(SetMarketContextRequest $request): JsonResponse
    {
        $validated = $request->validated();

        // Resolve market
        $market = null;
        if (isset($validated['market_id'])) {
            $market = Market::find($validated['market_id']);
        } elseif (isset($validated['market_code'])) {
            $market = Market::where('code', $validated['market_code'])->first();
        }

        if (! $market) {
            return response()->json([
                'message' => 'Market not found',
            ], 404);
        }

        // Create pricing context
        $context = PricingContext::create(
            marketId: $market->id,
            siteId: $validated['site_id'] ?? null,
            channelId: $validated['channel_id'] ?? null,
            catalogId: $validated['catalog_id'] ?? null,
            currency: $validated['currency'] ?? null,
            locale: $validated['locale'] ?? null,
            quantity: $validated['quantity'] ?? 1,
            countryCode: $validated['country_code'] ?? null,
        );

        // Save to session
        $context->saveToSession();

        return response()->json([
            'message' => 'Market context set successfully',
            'data' => [
                'market' => new MarketResource($market),
                'context' => new PricingContextResource($context),
            ],
        ]);
    }

    /**
     * Get current pricing context.
     */
    public function getContext(Request $request): JsonResponse
    {
        try {
            $context = PricingContext::fromRequest();

            return response()->json([
                'data' => new PricingContextResource($context),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Could not resolve pricing context',
                'error' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Get market configuration summary.
     */
    public function configuration(Request $request, string $marketIdentifier): JsonResponse
    {
        $market = Market::query()
            ->where('id', $marketIdentifier)
            ->orWhere('code', strtoupper($marketIdentifier))
            ->orWhere('handle', $marketIdentifier)
            ->published()
            ->firstOrFail();

        $summary = $this->marketConfig->getConfigurationSummary($market);

        return response()->json([
            'data' => $summary,
        ]);
    }

    /**
     * Get available payment methods for a market.
     */
    public function paymentMethods(Request $request, string $marketIdentifier): JsonResponse
    {
        $market = Market::query()
            ->where('id', $marketIdentifier)
            ->orWhere('code', strtoupper($marketIdentifier))
            ->firstOrFail();

        $methods = $this->marketConfig->getAvailablePaymentMethods($market);

        return response()->json([
            'data' => $methods,
        ]);
    }

    /**
     * Get available shipping methods for a market.
     */
    public function shippingMethods(Request $request, string $marketIdentifier): JsonResponse
    {
        $market = Market::query()
            ->where('id', $marketIdentifier)
            ->orWhere('code', strtoupper($marketIdentifier))
            ->firstOrFail();

        $countryCode = $request->query('country_code');

        $methods = $this->marketConfig->getAvailableShippingMethods($market, null, $countryCode);

        return response()->json([
            'data' => $methods,
        ]);
    }

    /**
     * Calculate tax for a market.
     */
    public function calculateTax(Request $request, string $marketIdentifier): JsonResponse
    {
        $market = Market::query()
            ->where('id', $marketIdentifier)
            ->orWhere('code', strtoupper($marketIdentifier))
            ->firstOrFail();

        $validated = $request->validate([
            'amount' => ['required', 'integer', 'min:0'],
            'country_code' => ['sometimes', 'string', 'size:2'],
            'product_type' => ['sometimes', 'string'],
        ]);

        $tax = $this->marketConfig->calculateTax(
            $validated['amount'],
            $market,
            $validated['country_code'] ?? null,
            $validated['product_type'] ?? null,
        );

        return response()->json([
            'data' => $tax,
        ]);
    }

    /**
     * Switch to a different market.
     */
    public function switch(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'market_id' => ['sometimes', 'integer', 'exists:markets,id'],
            'market_code' => ['sometimes', 'string', 'exists:markets,code'],
            'locale' => ['sometimes', 'string', 'max:10'],
            'return_url' => ['sometimes', 'string'],
        ]);

        $market = null;
        if (isset($validated['market_id'])) {
            $market = Market::find($validated['market_id']);
        } elseif (isset($validated['market_code'])) {
            $market = Market::where('code', $validated['market_code'])->first();
        }

        $locale = $validated['locale'] ?? null;
        $returnUrl = $validated['return_url'] ?? null;

        $switchUrl = MarketRouteHelper::switchTo($market, $locale, $returnUrl);

        return response()->json([
            'message' => 'Market switched successfully',
            'data' => [
                'market' => new MarketResource($market),
                'redirect_url' => $switchUrl,
            ],
        ]);
    }
}
