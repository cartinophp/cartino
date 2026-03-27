<?php

declare(strict_types=1);

namespace Cartino\Http\Controllers\Cp;

use Cartino\Cp\Listing;
use Cartino\Cp\Page;
use Cartino\Http\Requests\CP\StoreCustomerRequest;
use Cartino\Http\Resources\CP\CustomerResource;
use Cartino\Models\Customer;
use Cartino\Models\CustomerGroup;
use Cartino\Repositories\CustomerRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class CustomersController extends BaseController
{
    public function __construct(
        protected CustomerRepository $customerRepository,
    ) {
        $this->middleware('can:browse_customers')->only(['index', 'show']);
        $this->middleware('can:create_customers')->only(['create', 'store']);
        $this->middleware('can:update_customers')->only(['edit', 'update']);
        $this->middleware('can:delete_customers')->only(['destroy']);
    }

    /**
     * Display customers listing.
     */
    public function index(Request $request): Response
    {
        $page = Page::make('Customers')
            ->breadcrumb('Home', '/cp')
            ->breadcrumb('Customers')
            ->primaryAction('Add customer', route('cp.customers.create'))
            ->secondaryActions([
                ['label' => 'Import', 'url' => '#'],
                ['label' => 'Export', 'url' => '#'],
            ]);

        $listing = Listing::make()
            ->column('full_name', 'Name', sortable: true)
            ->column('email', 'Email', sortable: true)
            ->column('phone', 'Phone')
            ->column('customer_group', 'Group')
            ->column('orders_count', 'Orders', sortable: true)
            ->column('status', 'Status', sortable: true)
            ->column('actions', '', sortable: false, width: '100px')
            ->filter('status', 'Status', 'select', [
                ['value' => '', 'label' => 'All'],
                ['value' => '1', 'label' => 'Active'],
                ['value' => '0', 'label' => 'Inactive'],
            ], 'All')
            ->filter('customer_group_id', 'Group', 'select',
                CustomerGroup::select('id as value', 'name as label')->get()->prepend(['value' => '', 'label' => 'All Groups'])->toArray(),
                'All Groups'
            )
            ->bulkAction('activate', 'Activate')
            ->bulkAction('deactivate', 'Deactivate')
            ->bulkAction('delete', 'Delete', destructive: true, confirm: 'Delete selected customers?')
            ->bulkAction('export', 'Export')
            ->searchable(placeholder: 'Search customers...')
            ->emptyState('No customers yet', 'Customers will appear here when they register or are added manually.', icon: 'users')
            ->sort('created_at', 'desc');

        $data = QueryBuilder::for(Customer::class)
            ->select('customers.*')
            ->with(['customerGroup:id,name,discount_percentage', 'fidelityCard:id,customer_id,card_number,points'])
            ->withCount('orders')
            ->allowedFilters([
                'first_name', 'last_name', 'email', 'phone_number',
                AllowedFilter::exact('customer_group_id'),
                AllowedFilter::exact('is_active'),
            ])
            ->allowedSorts(['first_name', 'last_name', 'email', 'created_at'])
            ->defaultSort('-created_at')
            ->paginate($request->input('per_page', 15))
            ->withQueryString();

        return Inertia::render('customers/index', [
            'page' => $page->compile(),
            'listing' => $listing->toArray(),
            'data' => $data,
        ]);
    }

    /**
     * Show create form.
     */
    public function create(): Response
    {
        $this->addDashboardBreadcrumb()
            ->addBreadcrumb('Customers', 'cartino.customers.index')
            ->addBreadcrumb('Add customer');

        $page = Page::make('Add customer')
            ->primaryAction('Save customer', null, ['form' => 'customer-form'])
            ->secondaryActions([
                ['label' => 'Save & continue editing', 'action' => 'save_continue'],
                ['label' => 'Save & add another', 'action' => 'save_add_another'],
            ]);

        return Inertia::render('customers/create', [
            'page' => $page->compile(),
            'customerGroups' => CustomerGroup::select('id', 'name')->get(),
        ]);
    }

    /**
     * Store new customer.
     */
    public function store(StoreCustomerRequest $request): JsonResponse
    {
        $customer = $this->customerRepository->create($request->validated());

        $action = $request->input('_action', 'save');

        $redirectUrl = match ($action) {
            'save_continue' => route('cp.customers.edit', $customer),
            'save_add_another' => route('cp.customers.create'),
            default => route('cp.customers.index'),
        };

        return $this->successResponse('Customer created successfully', [
            'customer' => new CustomerResource($customer),
            'redirect' => $redirectUrl,
        ]);
    }

    /**
     * Display customer details.
     */
    public function show(Customer $customer): Response
    {
        $customer = $this->customerRepository->findWithRelations($customer->id, [
            'addresses',
            'orders',
            'customerGroup',
        ]);

        $this->addDashboardBreadcrumb()
            ->addBreadcrumb('Customers', 'cartino.customers.index')
            ->addBreadcrumb($customer->full_name);

        $page = Page::make($customer->full_name)
            ->primaryAction('Edit customer', route('cp.customers.edit', $customer))
            ->secondaryActions([
                ['label' => 'Send email', 'action' => 'send_email'],
                ['label' => 'Create order', 'url' => route('cp.orders.create', ['customer' => $customer->id])],
                ['label' => 'View orders', 'url' => route('cp.orders.index', ['customer' => $customer->id])],
                ['label' => 'Delete', 'action' => 'delete', 'destructive' => true],
            ]);

        return Inertia::render('customers/show', [
            'page' => $page->compile(),
            'customer' => new CustomerResource($customer),
        ]);
    }

    /**
     * Show edit form.
     */
    public function edit(Customer $customer): Response
    {
        $customer = $this->customerRepository->findWithRelations($customer->id, [
            'addresses',
            'customerGroup',
        ]);

        $this->addDashboardBreadcrumb()
            ->addBreadcrumb('Customers', 'cp.customers.index')
            ->addBreadcrumb($customer->full_name, route('cp.customers.show', $customer))
            ->addBreadcrumb('Edit');

        $page = Page::make("Edit {$customer->full_name}")
            ->primaryAction('Update customer', null, ['form' => 'customer-form'])
            ->secondaryActions([
                ['label' => 'View customer', 'url' => route('cp.customers.show', $customer)],
                ['label' => 'Send email', 'action' => 'send_email'],
                ['label' => 'Create order', 'url' => route('cp.orders.create', ['customer' => $customer->id])],
                ['label' => 'Delete', 'action' => 'delete', 'destructive' => true],
            ])
            ->tabs([
                'general' => ['label' => 'General', 'component' => 'CustomerGeneralForm'],
                'addresses' => ['label' => 'Addresses', 'component' => 'CustomerAddressesForm'],
                'orders' => ['label' => 'Orders', 'component' => 'CustomerOrdersForm'],
                'notes' => ['label' => 'Notes', 'component' => 'CustomerNotesForm'],
            ]);

        return Inertia::render('customers/edit', [
            'page' => $page->compile(),
            'customer' => new CustomerResource($customer),
            'customerGroups' => CustomerGroup::select('id', 'name')->get(),
        ]);
    }

    /**
     * Update customer.
     */
    public function update(StoreCustomerRequest $request, Customer $customer): JsonResponse
    {
        $customer = $this->customerRepository->update($customer->id, $request->validated());

        return $this->successResponse('Customer updated successfully', [
            'customer' => new CustomerResource($customer),
        ]);
    }

    /**
     * Delete customer.
     */
    public function destroy(Customer $customer): JsonResponse
    {
        if (! $this->customerRepository->canDelete($customer->id)) {
            return $this->errorResponse('Cannot delete customer with orders');
        }

        $this->customerRepository->delete($customer->id);

        return $this->successResponse('Customer deleted successfully');
    }

    /**
     * Handle bulk operations.
     */
    public function bulk(Request $request): JsonResponse
    {
        $request->validate([
            'action' => 'required|string|in:activate,deactivate,delete,export',
            'ids' => 'required|array|min:1',
            'ids.*' => 'exists:customers,id',
        ]);

        $action = $request->input('action');
        $ids = $request->input('ids');

        return $this->handleBulkOperation($action, $ids, function ($action, $ids) {
            return match ($action) {
                'activate' => $this->customerRepository->bulkUpdate($ids, ['status' => 'active']),
                'deactivate' => $this->customerRepository->bulkUpdate($ids, ['status' => 'inactive']),
                'delete' => $this->customerRepository->bulkDelete($ids),
                'export' => $this->customerRepository->bulkExport($ids),
            };
        });
    }
}
