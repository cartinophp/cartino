<?php

declare(strict_types=1);

namespace Cartino\Http\Controllers\Cp;

use Cartino\Cp\Listing;
use Cartino\Cp\Page;
use Cartino\Http\Controllers\Controller;
use Cartino\Http\Controllers\Cp\Concerns\HandlesFlashMessages;
use Cartino\Http\Requests\DiscountRequest;
use Cartino\Models\Discount;
use Cartino\Models\DiscountApplication;
use Cartino\Services\DiscountService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class DiscountController extends Controller
{
    use HandlesFlashMessages;

    public function __construct(
        protected DiscountService $discountService,
    ) {}

    public function index(Request $request): Response
    {
        $page = Page::make('Discounts')
            ->breadcrumb('Home', '/cp')
            ->breadcrumb('Discounts')
            ->primaryAction('Add discount', route('cp.discounts.create'));

        $listing = Listing::make()
            ->column('name', 'Name', sortable: true)
            ->column('code', 'Code', sortable: true)
            ->column('type', 'Type', sortable: true)
            ->column('value', 'Value', sortable: true)
            ->column('status', 'Status', sortable: true)
            ->column('usage_count', 'Uses', sortable: true)
            ->column('actions', '', sortable: false, width: '100px')
            ->filter('status', 'Status', 'select', [
                ['value' => '', 'label' => 'All'],
                ['value' => 'active', 'label' => 'Active'],
                ['value' => 'inactive', 'label' => 'Inactive'],
            ], 'All')
            ->filter('type', 'Type', 'select', [
                ['value' => '', 'label' => 'All Types'],
                ['value' => 'percentage', 'label' => 'Percentage'],
                ['value' => 'fixed_amount', 'label' => 'Fixed Amount'],
                ['value' => 'free_shipping', 'label' => 'Free Shipping'],
            ], 'All Types')
            ->bulkAction('activate', 'Activate')
            ->bulkAction('deactivate', 'Deactivate')
            ->bulkAction('delete', 'Delete', destructive: true, confirm: 'Delete selected discounts?')
            ->searchable(placeholder: 'Search discounts...')
            ->emptyState('No discounts yet', 'Create your first discount to attract customers.', ['label' => 'Add discount', 'url' => route('cp.discounts.create')], 'percent')
            ->sort('created_at', 'desc');

        $data = QueryBuilder::for(Discount::class)
            ->with(['applications'])
            ->allowedFilters([
                'name', 'code',
                AllowedFilter::exact('status'),
                AllowedFilter::exact('type'),
            ])
            ->allowedSorts(['name', 'code', 'type', 'status', 'created_at', 'usage_count'])
            ->defaultSort('-created_at')
            ->paginate($request->input('per_page', 15))
            ->withQueryString();

        return Inertia::render('discounts/index', [
            'page' => $page->compile(),
            'listing' => $listing->toArray(),
            'data' => $data,
        ]);
    }

    public function create(): Response
    {
        $page = Page::make('Add discount')
            ->breadcrumb('Home', '/cp')
            ->breadcrumb('Discounts', '/cp/discounts')
            ->breadcrumb('Add discount')
            ->primaryAction('Save discount', null, ['form' => 'resource-form'])
            ->secondaryActions([
                ['label' => 'Save & continue editing', 'action' => 'save_continue'],
                ['label' => 'Save & add another', 'action' => 'save_add_another'],
            ]);

        return Inertia::render('discounts/create', [
            'page' => $page->compile(),
            'discount_types' => $this->getDiscountTypes(),
        ]);
    }

    public function store(DiscountRequest $request)
    {
        $discount = $this->discountService->createDiscount($request->validated());

        $this->flashSuccess(__('discount.messages.created_successfully'));

        return redirect()->route('cp.discounts.show', $discount);
    }

    public function show(Discount $discount): Response
    {
        $discount->load(['applications.applicable']);
        $discount->statistics = $this->discountService->getDiscountStatistics($discount);

        $page = Page::make($discount->name)
            ->breadcrumb('Home', '/cp')
            ->breadcrumb('Discounts', '/cp/discounts')
            ->breadcrumb($discount->name)
            ->primaryAction('Edit discount', "/cp/discounts/{$discount->id}/edit")
            ->secondaryActions([
                ['label' => 'Duplicate', 'action' => 'duplicate'],
                ['label' => 'Delete', 'action' => 'delete', 'destructive' => true],
            ]);

        return Inertia::render('discounts/show', [
            'page' => $page->compile(),
            'discount' => $discount,
        ]);
    }

    public function edit(Discount $discount): Response
    {
        $page = Page::make("Edit {$discount->name}")
            ->breadcrumb('Home', '/cp')
            ->breadcrumb('Discounts', '/cp/discounts')
            ->breadcrumb($discount->name, "/cp/discounts/{$discount->id}")
            ->breadcrumb('Edit')
            ->primaryAction('Update discount', null, ['form' => 'resource-form'])
            ->secondaryActions([
                ['label' => 'View discount', 'url' => "/cp/discounts/{$discount->id}"],
                ['label' => 'Duplicate', 'action' => 'duplicate'],
                ['label' => 'Delete', 'action' => 'delete', 'destructive' => true],
            ]);

        return Inertia::render('discounts/edit', [
            'page' => $page->compile(),
            'discount' => $discount,
            'discount_types' => $this->getDiscountTypes(),
        ]);
    }

    public function update(DiscountRequest $request, Discount $discount)
    {
        $this->discountService->updateDiscount($discount, $request->validated());

        $this->flashSuccess(__('discount.messages.updated_successfully'));

        return redirect()->route('cp.discounts.show', $discount);
    }

    public function destroy(Discount $discount)
    {
        $deleted = $this->discountService->deleteDiscount($discount);

        if ($deleted) {
            $this->flashSuccess(__('discount.messages.deleted_successfully'));

            return redirect()->route('cp.discounts.index');
        }

        $this->flashError(__('discount.messages.delete_failed'));

        return back();
    }

    protected function getDiscountTypes(): array
    {
        return [
            'percentage' => __('discount.types.percentage'),
            'fixed_amount' => __('discount.types.fixed_amount'),
            'free_shipping' => __('discount.types.free_shipping'),
        ];
    }

    protected function getOverallStatistics(): array
    {
        return [
            'total_discounts' => Discount::count(),
            'active_discounts' => Discount::where('status', 'active')->count(),
            'total_applications' => DiscountApplication::count(),
            'total_discount_amount' => DiscountApplication::sum('discount_amount'),
        ];
    }
}
