<?php

namespace Cartino\Http\Controllers\Cp;

use Cartino\Http\Controllers\Controller;
use Cartino\Models\Customer;
use Cartino\Models\CustomerAddress;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class AddressController extends Controller
{
    public function index(Customer $customer): Response
    {
        $addresses = $customer
            ->addresses()
            ->orderBy('is_default', 'desc')
            ->orderBy('type')
            ->orderBy('created_at', 'desc')
            ->get();

        return Inertia::render('customers/addresses/index', [
            'customer' => $customer,
            'addresses' => $addresses,
        ]);
    }

    public function create(Customer $customer): Response
    {
        return Inertia::render('customers/addresses/Create', [
            'customer' => $customer,
        ]);
    }

    public function store(Request $request, Customer $customer): JsonResponse
    {
        $validated = $request->validate([
            'type' => 'required|string|in:shipping,billing,both',
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'company' => 'nullable|string|max:255',
            'address_line_1' => 'required|string|max:255',
            'address_line_2' => 'nullable|string|max:255',
            'city' => 'required|string|max:255',
            'state' => 'nullable|string|max:255',
            'postal_code' => 'required|string|max:20',
            'country_code' => 'required|string|size:2',
            'phone' => 'nullable|string|max:20',
            'is_default' => 'boolean',
        ]);

        $validated['customer_id'] = $customer->id;

        $address = CustomerAddress::create($validated);

        if ($validated['is_default'] ?? false) {
            $address->setAsDefault();
        }

        return response()->json([
            'success' => true,
            'address' => $address,
            'message' => 'Address created successfully.',
        ]);
    }

    public function show(Customer $customer, CustomerAddress $address): Response
    {
        $this->authorize('view', $address);

        return Inertia::render('customers/addresses/Show', [
            'customer' => $customer,
            'address' => $address,
        ]);
    }

    public function edit(Customer $customer, CustomerAddress $address): Response
    {
        $this->authorize('update', $address);

        return Inertia::render('customers/addresses/Edit', [
            'customer' => $customer,
            'address' => $address,
        ]);
    }

    public function update(Request $request, Customer $customer, CustomerAddress $address): JsonResponse
    {
        $this->authorize('update', $address);

        $validated = $request->validate([
            'type' => 'required|string|in:shipping,billing,both',
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'company' => 'nullable|string|max:255',
            'address_line_1' => 'required|string|max:255',
            'address_line_2' => 'nullable|string|max:255',
            'city' => 'required|string|max:255',
            'state' => 'nullable|string|max:255',
            'postal_code' => 'required|string|max:20',
            'country_code' => 'required|string|size:2',
            'phone' => 'nullable|string|max:20',
            'is_default' => 'boolean',
        ]);

        $address->update($validated);

        if ($validated['is_default'] ?? false) {
            $address->setAsDefault();
        }

        return response()->json([
            'success' => true,
            'address' => $address,
            'message' => 'Address updated successfully.',
        ]);
    }

    public function destroy(Customer $customer, CustomerAddress $address): JsonResponse
    {
        $this->authorize('delete', $address);

        $address->delete();

        return response()->json([
            'success' => true,
            'message' => 'Address deleted successfully.',
        ]);
    }

    public function setDefault(Customer $customer, CustomerAddress $address): JsonResponse
    {
        $this->authorize('update', $address);

        $address->setAsDefault();

        return response()->json([
            'success' => true,
            'address' => $address,
            'message' => 'Default address updated successfully.',
        ]);
    }
}
