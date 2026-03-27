<?php

declare(strict_types=1);

namespace Cartino\Http\Requests\CP;

use Cartino\Enums\FulfillmentStatus;
use Cartino\Enums\OrderStatus;
use Cartino\Enums\PaymentStatus;
use Cartino\Models\Country;
use Cartino\Models\Customer;
use Cartino\Models\Order;
use Cartino\Models\Product;
use Cartino\Models\ProductVariant;
use Cartino\Models\ShippingMethod;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreOrderRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'customer_id' => ['nullable', Rule::exists(Customer::class, 'id')],
            'number' => ['nullable', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:20'],
            'status' => ['required', 'string', Rule::enum(OrderStatus::class)],
            'payment_status' => ['required', 'string', Rule::enum(PaymentStatus::class)],
            'fulfillment_status' => ['nullable', 'string', Rule::enum(FulfillmentStatus::class)],
            'currency' => ['required', 'string', 'size:3'],
            'notes' => ['nullable', 'string'],
            'tags' => ['nullable', 'array'],
            'tags.*' => ['string', 'max:50'],
            // Financial fields
            'subtotal' => ['required', 'numeric', 'min:0'],
            'tax_total' => ['nullable', 'numeric', 'min:0'],
            'shipping_total' => ['nullable', 'numeric', 'min:0'],
            'discount_total' => ['nullable', 'numeric', 'min:0'],
            'total' => ['required', 'numeric', 'min:0'],
            // Order items
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', Rule::exists(Product::class, 'id')],
            'items.*.product_variant_id' => ['nullable', Rule::exists(ProductVariant::class, 'id')],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
            'items.*.price' => ['required', 'numeric', 'min:0'],
            'items.*.name' => ['required', 'string', 'max:255'],
            'items.*.sku' => ['nullable', 'string', 'max:100'],
            // Shipping address
            'shipping_address' => ['nullable', 'array'],
            'shipping_address.first_name' => ['nullable', 'string', 'max:255'],
            'shipping_address.last_name' => ['nullable', 'string', 'max:255'],
            'shipping_address.company' => ['nullable', 'string', 'max:255'],
            'shipping_address.address_line_1' => ['nullable', 'string', 'max:255'],
            'shipping_address.address_line_2' => ['nullable', 'string', 'max:255'],
            'shipping_address.city' => ['nullable', 'string', 'max:255'],
            'shipping_address.state' => ['nullable', 'string', 'max:255'],
            'shipping_address.postal_code' => ['nullable', 'string', 'max:20'],
            'shipping_address.country_id' => ['nullable', Rule::exists(Country::class, 'id')],
            'shipping_address.phone' => ['nullable', 'string', 'max:20'],
            // Billing address
            'billing_address' => ['nullable', 'array'],
            'billing_address.first_name' => ['nullable', 'string', 'max:255'],
            'billing_address.last_name' => ['nullable', 'string', 'max:255'],
            'billing_address.company' => ['nullable', 'string', 'max:255'],
            'billing_address.address_line_1' => ['nullable', 'string', 'max:255'],
            'billing_address.address_line_2' => ['nullable', 'string', 'max:255'],
            'billing_address.city' => ['nullable', 'string', 'max:255'],
            'billing_address.state' => ['nullable', 'string', 'max:255'],
            'billing_address.postal_code' => ['nullable', 'string', 'max:20'],
            'billing_address.country_id' => ['nullable', Rule::exists(Country::class, 'id')],
            'billing_address.phone' => ['nullable', 'string', 'max:20'],
            // Shipping method
            'shipping_method_id' => ['nullable', Rule::exists(ShippingMethod::class, 'id')],
            'shipping_method_name' => ['nullable', 'string', 'max:255'],
            'shipping_method_price' => ['nullable', 'numeric', 'min:0'],
            // Payment method
            'payment_method' => ['nullable', 'string', 'max:255'],
            'payment_reference' => ['nullable', 'string', 'max:255'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'customer_id.exists' => 'Il cliente selezionato non esiste.',
            'email.required' => 'L\'email è obbligatoria.',
            'email.email' => 'L\'email deve essere valida.',
            'status.required' => 'Lo status dell\'ordine è obbligatorio.',
            'status.in' => 'Lo status dell\'ordine selezionato non è valido.',
            'payment_status.required' => 'Lo status del pagamento è obbligatorio.',
            'payment_status.in' => 'Lo status del pagamento selezionato non è valido.',
            'currency.required' => 'La valuta è obbligatoria.',
            'currency.size' => 'La valuta deve essere di 3 caratteri.',
            'subtotal.required' => 'Il subtotale è obbligatorio.',
            'subtotal.numeric' => 'Il subtotale deve essere un numero.',
            'total.required' => 'Il totale è obbligatorio.',
            'total.numeric' => 'Il totale deve essere un numero.',
            'items.required' => 'Almeno un articolo è obbligatorio.',
            'items.min' => 'Almeno un articolo è obbligatorio.',
            'items.*.product_id.required' => 'Il prodotto è obbligatorio per ogni articolo.',
            'items.*.product_id.exists' => 'Il prodotto selezionato non esiste.',
            'items.*.quantity.required' => 'La quantità è obbligatoria per ogni articolo.',
            'items.*.quantity.min' => 'La quantità deve essere almeno 1.',
            'items.*.price.required' => 'Il prezzo è obbligatorio per ogni articolo.',
            'items.*.name.required' => 'Il nome è obbligatorio per ogni articolo.',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'customer_id' => 'cliente',
            'payment_status' => 'status pagamento',
            'fulfillment_status' => 'status evasione',
            'subtotal' => 'subtotale',
            'tax_total' => 'totale tasse',
            'shipping_total' => 'totale spedizione',
            'discount_total' => 'totale sconto',
            'shipping_address' => 'indirizzo di spedizione',
            'billing_address' => 'indirizzo di fatturazione',
            'shipping_method_id' => 'metodo di spedizione',
            'payment_method' => 'metodo di pagamento',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'status' => $this->input('status', 'pending'),
            'payment_status' => $this->input('payment_status', 'pending'),
            'currency' => $this->input('currency', config('cartino.currency.default', 'EUR')),
        ]);

        // Generate order number if not provided
        if (! $this->input('number')) {
            $this->merge([
                'number' => $this->generateOrderNumber(),
            ]);
        }

        // Calculate totals if not provided
        if ($this->has('items') && ! $this->input('subtotal')) {
            $subtotal = collect($this->input('items'))->sum(fn ($item) => $item['price'] * $item['quantity']);
            $taxTotal = $this->input('tax_total', 0);
            $shippingTotal = $this->input('shipping_total', 0);
            $discountTotal = $this->input('discount_total', 0);
            $total = ($subtotal + $taxTotal + $shippingTotal) - $discountTotal;

            $this->merge([
                'subtotal' => $subtotal,
                'total' => $total,
            ]);
        }
    }

    /**
     * Generate a unique order number.
     */
    private function generateOrderNumber(): string
    {
        return
            'ORD-'.
            date('Y').
            '-'.
            str_pad(Order::whereYear('created_at', date('Y'))->count() + 1, 6, '0', STR_PAD_LEFT);
    }
}
