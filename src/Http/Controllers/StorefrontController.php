<?php

namespace Cartino\Http\Controllers;

use Cartino\Models\Category;
use Cartino\Models\Product;
use Cartino\Services\TemplateEngine;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class StorefrontController extends Controller
{
    protected TemplateEngine $templateEngine;

    public function __construct(TemplateEngine $templateEngine)
    {
        $this->templateEngine = $templateEngine;
    }

    /**
     * Home page
     */
    public function home(Request $request): Response
    {
        $site = app('laravel-cartino.site');

        $content = $this->templateEngine->render('index', null, $request->attributes->get('custom_template'));

        return response($content);
    }

    /**
     * Product listing page
     */
    public function productIndex(Request $request): Response
    {
        $products = Product::active()->with(['media', 'variants', 'category'])->paginate(20);

        $content = $this->templateEngine->render(
            'collection',
            (object) [
                'title' => 'All Products',
                'handle' => 'all-products',
                'products' => $products,
                'type' => 'products',
            ],
            $request->attributes->get('custom_template'),
        );

        return response($content);
    }

    /**
     * Single product page
     */
    public function productShow(Request $request, string $handle): Response
    {
        $product = Product::where('handle', $handle)
            ->with(['media', 'variants', 'category', 'reviews'])
            ->active()
            ->firstOrFail();

        $content = $this->templateEngine->render('product', $product, $request->attributes->get('custom_template'));

        return response($content);
    }

    /**
     * Category listing page
     */
    public function categoryIndex(Request $request): Response
    {
        $categories = Category::active()
            ->with(['media'])
            ->orderBy('name')
            ->get();

        $content = $this->templateEngine->render(
            'collection',
            (object) [
                'title' => 'All Collections',
                'handle' => 'all-collections',
                'collections' => $categories,
                'type' => 'collections',
            ],
            $request->attributes->get('custom_template'),
        );

        return response($content);
    }

    /**
     * Single category page
     */
    public function categoryShow(Request $request, string $handle): Response
    {
        $category = Category::where('handle', $handle)
            ->with(['media', 'products.media'])
            ->active()
            ->firstOrFail();

        $products = $category->products()->active()->with(['media', 'variants'])->paginate(20);

        $category->products = $products;

        $content = $this->templateEngine->render('collection', $category, $request->attributes->get('custom_template'));

        return response($content);
    }

    /**
     * Page show
     */
    public function pageShow(Request $request, string $handle): Response
    {
        // For now, create a basic page structure
        // You can extend this with a Page model later
        $page = (object) [
            'title' => str_replace('-', ' ', $handle),
            'handle' => $handle,
            'content' => "Content for {$handle} page",
        ];

        $content = $this->templateEngine->render('page', $page, $request->attributes->get('custom_template'));

        return response($content);
    }

    /**
     * Blog index
     */
    public function blogIndex(Request $request): Response
    {
        // Placeholder for blog functionality
        $blog = (object) [
            'title' => 'Blog',
            'handle' => 'blog',
            'articles' => collect([]),
        ];

        $content = $this->templateEngine->render('blog', $blog, $request->attributes->get('custom_template'));

        return response($content);
    }

    /**
     * Blog article show
     */
    public function blogShow(Request $request, string $handle): Response
    {
        // Placeholder for article functionality
        $article = (object) [
            'title' => str_replace('-', ' ', $handle),
            'handle' => $handle,
            'content' => "Article content for {$handle}",
        ];

        $content = $this->templateEngine->render('article', $article, $request->attributes->get('custom_template'));

        return response($content);
    }

    /**
     * Search results
     */
    public function search(Request $request): Response
    {
        $query = $request->get('q', '');
        $results = collect([]);

        if ($query) {
            $products = Product::where('name', 'like', "%{$query}%")
                ->orWhere('description', 'like', "%{$query}%")
                ->active()
                ->with(['media', 'variants'])
                ->limit(20)
                ->get();

            $results = $results->merge($products);
        }

        $searchData = (object) [
            'query' => $query,
            'results' => $results,
            'total_results' => $results->count(),
        ];

        $content = $this->templateEngine->render('search', $searchData);

        return response($content);
    }

    /**
     * Cart page
     */
    public function cartShow(Request $request): Response
    {
        // Placeholder for cart functionality
        $cart = (object) [
            'lines' => collect([]),
            'total' => 0,
            'item_count' => 0,
        ];

        $content = $this->templateEngine->render('cart', $cart);

        return response($content);
    }

    /**
     * Add to cart
     */
    public function cartAdd(Request $request)
    {
        // Cart logic will be implemented separately
        return response()->json(['success' => true]);
    }

    /**
     * Update cart
     */
    public function cartUpdate(Request $request, $line)
    {
        // Cart logic will be implemented separately
        return response()->json(['success' => true]);
    }

    /**
     * Remove from cart
     */
    public function cartRemove(Request $request, $line)
    {
        // Cart logic will be implemented separately
        return response()->json(['success' => true]);
    }

    /**
     * Apply coupon to cart
     */
    public function cartApplyCoupon(Request $request)
    {
        $request->validate([
            'code' => 'required|string',
        ]);

        // Coupon logic will be implemented separately
        return response()->json([
            'success' => true,
            'message' => __('storefront.messages.coupon_applied'),
        ]);
    }

    /**
     * Checkout page
     */
    public function checkoutShow(Request $request): Response
    {
        // Get cart from session or database
        $cart = (object) [
            'lines' => collect([]),
            'subtotal' => 0,
            'shipping' => 0,
            'tax' => 0,
            'discount' => 0,
            'total' => 0,
        ];

        $shippingMethods = [
            [
                'id' => 'standard',
                'name' => 'Standard Shipping',
                'description' => '5-7 business days',
                'price' => 500,
                'price_formatted' => '$5.00',
            ],
            [
                'id' => 'express',
                'name' => 'Express Shipping',
                'description' => '2-3 business days',
                'price' => 1500,
                'price_formatted' => '$15.00',
            ],
            [
                'id' => 'overnight',
                'name' => 'Overnight Shipping',
                'description' => 'Next business day',
                'price' => 2500,
                'price_formatted' => '$25.00',
            ],
        ];

        $paymentMethods = [
            [
                'id' => 'stripe',
                'name' => 'Credit Card',
                'description' => 'Pay with Visa, Mastercard, Amex',
                'icon' => null,
            ],
            ['id' => 'paypal', 'name' => 'PayPal', 'description' => 'Pay with your PayPal account', 'icon' => null],
        ];

        $content = $this->templateEngine->render(
            'checkout',
            (object) [
                'cart' => $cart,
                'shippingMethods' => $shippingMethods,
                'paymentMethods' => $paymentMethods,
            ],
        );

        return response($content);
    }

    /**
     * Process checkout
     */
    public function checkoutProcess(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'billing.first_name' => 'required|string',
            'billing.last_name' => 'required|string',
            'billing.address_line_1' => 'required|string',
            'billing.city' => 'required|string',
            'billing.postal_code' => 'required|string',
            'billing.country' => 'required|string',
            'shipping_method' => 'required|string',
            'payment_method' => 'required|string',
        ]);

        // Checkout processing logic will be implemented separately
        return response()->json([
            'success' => true,
            'redirect_url' => route('storefront.account.orders'),
            'message' => __('storefront.messages.order_placed'),
        ]);
    }

    /**
     * Login page
     */
    public function loginShow(Request $request): Response
    {
        $content = $this->templateEngine->render('customers/login', null);

        return response($content);
    }

    /**
     * Process login
     */
    public function loginProcess(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        if (auth('customers')->attempt($request->only('email', 'password'), $request->boolean('remember'))) {
            $request->session()->regenerate();

            return redirect()->intended(route('storefront.account.dashboard'));
        }

        return back()->withErrors([
            'email' => __('storefront.messages.invalid_credentials'),
        ])->onlyInput('email');
    }

    /**
     * Register page
     */
    public function registerShow(Request $request): Response
    {
        $content = $this->templateEngine->render('customers/register', null);

        return response($content);
    }

    /**
     * Process registration
     */
    public function registerProcess(Request $request)
    {
        $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|unique:customers,email',
            'password' => 'required|string|min:8|confirmed',
        ]);

        // Customer registration logic will be implemented separately
        // This should create a new customer and log them in

        return redirect()->route('storefront.account.dashboard');
    }

    /**
     * Logout
     */
    public function logout(Request $request)
    {
        auth('customers')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('storefront.home');
    }

    /**
     * Account dashboard
     */
    public function accountDashboard(Request $request): Response
    {
        $customer = $request->user('customers');

        // Get customer stats
        $stats = [
            'total_orders' => 0, // $customer->orders()->count()
            'total_spent' => 0, // $customer->orders()->sum('total')
            'pending_orders' => 0, // $customer->orders()->where('status', 'pending')->count()
        ];

        $recentOrders = collect([]); // $customer->orders()->latest()->take(5)->get()

        $content = $this->templateEngine->render(
            'account/dashboard',
            (object) [
                'customer' => $customer,
                'stats' => $stats,
                'recentOrders' => $recentOrders,
            ],
        );

        return response($content);
    }

    /**
     * Account orders
     */
    public function accountOrders(Request $request): Response
    {
        $customer = $request->user('customers');

        $query = collect([]); // Replace with: $customer->orders()->with('lines.product')

        // Apply filters
        if ($request->filled('status')) {
            // $query->where('status', $request->status);
        }

        if ($request->filled('date_range')) {
            // $query->where('created_at', '>=', now()->subDays($request->date_range));
        }

        $orders = $query; // Replace with: $query->paginate(10)

        $content = $this->templateEngine->render(
            'account/orders',
            (object) [
                'customer' => $customer,
                'orders' => $orders,
            ],
        );

        return response($content);
    }

    /**
     * Account order show
     */
    public function accountOrderShow(Request $request, $order): Response
    {
        $customer = $request->user('customers');
        // $order = $customer->orders()->with('lines.product')->findOrFail($order);

        $content = $this->templateEngine->render(
            'account/order',
            (object) [
                'customer' => $customer,
                'order' => null, // Replace with actual $order
            ],
        );

        return response($content);
    }

    /**
     * Track order
     */
    public function accountOrderTrack(Request $request, $order): Response
    {
        $customer = $request->user('customers');
        // $order = $customer->orders()->findOrFail($order);

        $content = $this->templateEngine->render(
            'account/order-tracking',
            (object) [
                'customer' => $customer,
                'order' => null, // Replace with actual $order
            ],
        );

        return response($content);
    }

    /**
     * Download invoice
     */
    public function accountOrderInvoice(Request $request, $order)
    {
        $customer = $request->user('customers');
        // $order = $customer->orders()->findOrFail($order);

        // Generate PDF invoice
        // return response()->download($invoicePath);

        return response()->json(['message' => 'Invoice download will be implemented']);
    }

    /**
     * Account addresses
     */
    public function accountAddresses(Request $request): Response
    {
        $customer = $request->user('customers');
        $addresses = collect([]); // Replace with: $customer->addresses

        $content = $this->templateEngine->render(
            'account/addresses',
            (object) [
                'customer' => $customer,
                'addresses' => $addresses,
            ],
        );

        return response($content);
    }

    /**
     * Store new address
     */
    public function accountAddressStore(Request $request)
    {
        $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'address_line_1' => 'required|string',
            'city' => 'required|string',
            'postal_code' => 'required|string',
            'country' => 'required|string',
            'type' => 'required|in:billing,shipping,both',
        ]);

        $customer = $request->user('customers');
        // Create address: $customer->addresses()->create($request->all())

        return response()->json([
            'success' => true,
            'message' => __('storefront.messages.address_added'),
        ]);
    }

    /**
     * Update address
     */
    public function accountAddressUpdate(Request $request, $address)
    {
        $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'address_line_1' => 'required|string',
            'city' => 'required|string',
            'postal_code' => 'required|string',
            'country' => 'required|string',
            'type' => 'required|in:billing,shipping,both',
        ]);

        $customer = $request->user('customers');
        // $address = $customer->addresses()->findOrFail($address);
        // $address->update($request->all());

        return response()->json([
            'success' => true,
            'message' => __('storefront.messages.address_updated'),
        ]);
    }

    /**
     * Delete address
     */
    public function accountAddressDestroy(Request $request, $address)
    {
        $customer = $request->user('customers');
        // $address = $customer->addresses()->findOrFail($address);
        // $address->delete();

        return response()->json([
            'success' => true,
            'message' => __('storefront.messages.address_deleted'),
        ]);
    }

    /**
     * Account settings
     */
    public function accountSettings(Request $request): Response
    {
        $customer = $request->user('customers');

        $content = $this->templateEngine->render(
            'account/settings',
            (object) [
                'customer' => $customer,
            ],
        );

        return response($content);
    }

    /**
     * Update account settings
     */
    public function accountSettingsUpdate(Request $request)
    {
        $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|unique:customers,email,'.$request->user('customers')->id,
            'phone' => 'nullable|string',
        ]);

        $customer = $request->user('customers');
        // $customer->update($request->only('first_name', 'last_name', 'email', 'phone'));

        if ($request->filled('password')) {
            $request->validate([
                'password' => 'required|string|min:8|confirmed',
                'current_password' => 'required|string',
            ]);

            // Verify current password and update
        }

        return back()->with('success', __('storefront.messages.settings_updated'));
    }

    /**
     * Newsletter subscription
     */
    public function newsletterSubscribe(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
        ]);

        // Newsletter subscription logic will be implemented separately

        return back()->with('success', __('storefront.messages.newsletter_subscribed'));
    }

    /**
     * Template preview (Admin only)
     */
    public function templatePreview(Request $request, string $template): Response
    {
        // Sample data for preview
        $sampleData = [
            'title' => 'Preview Template',
            'handle' => 'preview',
            'content' => 'This is a preview of the template.',
        ];

        return response($this->templateEngine->preview($template, $sampleData));
    }
}
