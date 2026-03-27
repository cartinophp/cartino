<?php

namespace Cartino\Database\Seeders;

use Cartino\Database\Factories\AnalyticsEventFactory;
use Cartino\Database\Factories\CustomerFactory;
use Cartino\Database\Factories\FidelityCardFactory;
use Cartino\Database\Factories\FidelityTransactionFactory;
use Cartino\Database\Factories\ShippingRateFactory;
use Cartino\Models\Address;
use Cartino\Models\AnalyticsEvent;
use Cartino\Models\Asset;
use Cartino\Models\AssetContainer;
use Cartino\Models\Brand;
use Cartino\Models\Cart;
use Cartino\Models\CartLine;
use Cartino\Models\Category;
use Cartino\Models\Channel;
use Cartino\Models\Country;
use Cartino\Models\Courier;
use Cartino\Models\Currency;
use Cartino\Models\Customer;
use Cartino\Models\CustomerGroup;
use Cartino\Models\Favorite;
use Cartino\Models\Menu;
use Cartino\Models\MenuItem;
use Cartino\Models\Order;
use Cartino\Models\OrderLine;
use Cartino\Models\PriceList;
use Cartino\Models\Product;
use Cartino\Models\ProductOption;
use Cartino\Models\ProductOptionValue;
use Cartino\Models\ProductReview;
use Cartino\Models\ProductType;
use Cartino\Models\ProductVariant;
use Cartino\Models\ReviewMedia;
use Cartino\Models\ReviewVote;
use Cartino\Models\Setting;
use Cartino\Models\ShippingZone;
use Cartino\Models\Site;
use Cartino\Models\StockNotification;
use Cartino\Models\Subscription;
use Cartino\Models\Supplier;
use Cartino\Models\TaxRate;
use Cartino\Models\Transaction;
use Cartino\Models\User;
use Cartino\Models\UserGroup;
use Cartino\Models\Wishlist;
use Cartino\Models\WishlistItem;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class CartinoSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('🌱 Seeding Cartino with MASSIVE multi-site demo data...');
        $this->command->warn('⚠️  This will create LARGE amounts of data - please be patient!');

        DB::transaction(function () {
            $this->seedCoreData();
            $this->seedDemoCatalog();
            $this->seedCustomersAndOrders();
        });

        $this->command->info('');
        $this->command->info('✅ ========================================');
        $this->command->info('✅ Cartino MASSIVE seeding completed!');
        $this->command->info('✅ ========================================');
        $this->command->info('');
        $this->command->info('📊 Data Summary:');
        $this->command->info('   • Sites: 2');
        $this->command->info('   • Users: 26');
        $this->command->info('   • Currencies: 11');
        $this->command->info('   • Countries: 50');
        $this->command->info('   • Brands: 50 (with assets: logo + banner)');
        $this->command->info('   • Categories: 80 (with assets: featured_image + banner)');
        $this->command->info('   • Product Options: 4 (Color, Size, Material, Style with ~35 values)');
        $this->command->info('   • Price Lists: 5 (Standard, Wholesale, Tier, 2 Promotional)');
        $this->command->info('   • Products: 500 (with ~2000 variants)');
        $this->command->info('   • Assets: ~2500-3000 (images, galleries, documents, videos)');
        $this->command->info('   • Prices: ~6000-8000 (multi-tier, multi-site pricing)');
        $this->command->info('   • Customers: 100 (with 100-200 addresses)');
        $this->command->info('   • Subscriptions: ~40-60 (active recurring billing)');
        $this->command->info('   • Orders: ~200-250 (regular + subscription billing)');
        $this->command->info('   • Couriers: 10');
        $this->command->info('   • Suppliers: 30');
        $this->command->info('   • Shipping Zones: 10 (with 50 rates)');
        $this->command->info('   • Discounts: 10');
        $this->command->info('   • Tax Rates: 200');
        $this->command->info('   • Settings: 50');
        $this->command->info('   • User Groups: 18');
        $this->command->info('   • Customer Groups: 11');
        $this->command->info('   • Menus: 5 (with 50 menu items)');
        $this->command->info('');
        $this->command->info('🔑 Admin login: admin@admin.com / password');
        $this->command->info('');
    }

    /**
     * Seed base configuration (sites, currencies, permissions, settings...)
     */
    protected function seedCoreData(): void
    {
        $this->command->info('⚙️ Core setup...');

        // Seed Sites
        $this->command->info('🌐 Seeding sites...');
        // Use firstOrCreate to avoid unique violations on re-runs
        $mainSite = Site::firstOrCreate(
            ['handle' => 'main'],
            [
                'handle' => 'main',
                'name' => 'Main Store',
                'url' => 'https://main.test',
                'locale' => 'en_US',
                'lang' => 'en',
                'is_default' => true,
                'status' => 'active',
                'order' => 1,
            ],
        );

        Site::firstOrCreate(
            ['handle' => 'it'],
            [
                'handle' => 'it',
                'name' => 'Store Italia',
                'url' => 'https://it.test',
                'locale' => 'it_IT',
                'lang' => 'it',
                'status' => 'active',
                'order' => 2,
            ],
        );

        // Seed currencies (ensure uniqueness by code)
        $this->command->info('💰 Seeding currencies...');
        Currency::query()->firstOrCreate(
            ['code' => 'EUR'],
            [
                'name' => 'Euro',
                'symbol' => '€',
                'is_default' => true,
                'rate' => 1.0000,
            ],
        );

        $uniqueCurrencies = [
            ['code' => 'USD', 'name' => 'US Dollar', 'symbol' => '$', 'rate' => 1.08],
            ['code' => 'GBP', 'name' => 'British Pound', 'symbol' => '£', 'rate' => 0.85],
            ['code' => 'JPY', 'name' => 'Japanese Yen', 'symbol' => '¥', 'rate' => 160.0],
            ['code' => 'CHF', 'name' => 'Swiss Franc', 'symbol' => 'CHF', 'rate' => 0.95],
            ['code' => 'AUD', 'name' => 'Australian Dollar', 'symbol' => 'A$', 'rate' => 1.60],
            ['code' => 'CAD', 'name' => 'Canadian Dollar', 'symbol' => 'C$', 'rate' => 1.45],
            ['code' => 'SEK', 'name' => 'Swedish Krona', 'symbol' => 'kr', 'rate' => 11.2],
            ['code' => 'NOK', 'name' => 'Norwegian Krone', 'symbol' => 'kr', 'rate' => 11.5],
            ['code' => 'DKK', 'name' => 'Danish Krone', 'symbol' => 'kr', 'rate' => 7.45],
            ['code' => 'PLN', 'name' => 'Polish Złoty', 'symbol' => 'zł', 'rate' => 4.30],
        ];

        foreach ($uniqueCurrencies as $c) {
            Currency::query()->firstOrCreate(
                ['code' => $c['code']],
                [
                    'name' => $c['name'],
                    'symbol' => $c['symbol'],
                    'rate' => $c['rate'],
                    'is_default' => false,
                ],
            );
        }

        // Seed countries
        $this->command->info('🌍 Seeding countries...');
        if (Country::count() === 0) {
            Country::factory()->count(50)->create();
        } else {
            $this->command->info('  ⏭️  Countries already seeded, skipping...');
        }

        // Create permissions and roles
        $this->command->info('🔑 Creating permissions and roles...');
        $this->createPermissionsAndRoles();

        // Create default admin user
        $this->command->info('👤 Creating admin user...');
        $user = User::factory()->admin()->create();

        // Assign super-admin role to user
        if (! $user->hasRole('super-admin')) {
            $user->assignRole('super-admin');
            $this->command->info('✅ Assigned super-admin role to admin user');
        }

        // Create additional users
        $this->command->info('👥 Creating additional users...');
        User::factory()->count(25)->create();

        // User groups
        $this->command->info('👥 Seeding user groups...');
        UserGroup::factory()->count(18)->create();

        // Seed channels
        $this->command->info('📺 Seeding channels...');
        Channel::factory()
            ->state([
                'site_id' => $mainSite->id,
                'slug' => 'default',
                'name' => 'Default Channel',
                'is_default' => true,
                'status' => 'active',
            ])
            ->create();

        Channel::factory()
            ->count(10)
            ->state([
                'site_id' => $mainSite->id,
            ])
            ->create();

        // Seed customer groups
        $this->command->info('👥 Seeding customer groups...');
        CustomerGroup::factory()->default()->create();
        CustomerGroup::factory()->count(10)->create();

        // Seed brands
        $this->command->info('🏷️ Seeding brands...');
        $brands = Brand::factory()->count(50)->create();

        // Seed product types
        $this->command->info('📦 Seeding product types...');
        ProductType::factory()->state(['name' => 'Physical', 'slug' => 'physical', 'status' => 'active'])->create();
        ProductType::factory()->state(['name' => 'Digital', 'slug' => 'digital', 'status' => 'active'])->create();
        ProductType::factory()
            ->count(10)
            ->active()
            ->create();

        // Settings
        $this->command->info('⚙️ Seeding settings...');
        Setting::factory()->count(50)->create();

        // Globals (like Statamic)
        $this->command->info('🌐 Seeding globals...');
        if (class_exists(GlobalSeeder::class)) {
            $this->call(GlobalSeeder::class);
        } else {
            $this->command->warn('  Skipping GlobalSeeder (class not found)');
        }

        // Shipping zones and rates
        $this->command->info('🚚 Seeding shipping zones & rates...');
        $zones = ShippingZone::factory()
            ->count(10)
            ->state([
                'site_id' => $mainSite->id,
            ])
            ->create();

        foreach ($zones as $zone) {
            ShippingRateFactory::new()
                ->count(5)
                ->state([
                    'shipping_zone_id' => $zone->id,
                    'channel_id' => Channel::where('slug', 'default')->value('id'),
                ])
                ->create();
        }

        // Taxes
        $this->command->info('🧾 Seeding tax rates...');
        TaxRate::factory()->count(200)->create();

        // Payment methods
        $this->command->info('💳 Seeding payment methods...');
        DB::table('payment_methods')->insert([
            'name' => 'Stripe Card',
            'slug' => 'stripe-card',
            'provider' => 'stripe',
            'description' => 'Stripe card payments',
            'configuration' => json_encode(['mode' => 'test']),
            'status' => 'active',
            'is_test_mode' => true,
            'fixed_fee' => 0,
            'percentage_fee' => 0.029,
            'supported_currencies' => json_encode(['EUR', 'USD']),
            'supported_countries' => json_encode(['IT', 'US', 'DE']),
            'sort_order' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('payment_methods')->insert([
            'name' => 'PayPal',
            'slug' => 'paypal',
            'provider' => 'paypal',
            'description' => 'PayPal payments',
            'configuration' => json_encode(['mode' => 'test']),
            'status' => 'active',
            'is_test_mode' => true,
            'fixed_fee' => 0,
            'percentage_fee' => 0.03,
            'supported_currencies' => json_encode(['EUR', 'USD']),
            'supported_countries' => json_encode(['IT', 'US', 'DE']),
            'sort_order' => 2,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Discounts
        $this->command->info('🎟️ Seeding discounts...');
        $seedDiscount = function (array $d) {
            DB::table('discounts')->insert([
                'site_id' => null,
                'code' => $d['code'],
                'title' => $d['title'],
                'description' => $d['description'],
                'type' => $d['type'],
                'value' => $d['value'],
                'maximum_discount_amount' => $d['maximum_discount_amount'] ?? null,
                'minimum_amount' => $d['minimum_amount'] ?? null,
                'usage_limit' => $d['usage_limit'] ?? null,
                'usage_limit_per_customer' => $d['usage_limit_per_customer'] ?? null,
                'usage_count' => 0,
                'starts_at' => now()->subWeek(),
                'expires_at' => null,
                'is_active' => true,
                'target_type' => 'all',
                'target_selection' => null,
                'customer_eligibility' => 'all',
                'customer_selection' => null,
                'shipping_countries' => null,
                'exclude_shipping_rates' => false,
                'created_by' => null,
                'admin_notes' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        };
        foreach (range(1, 10) as $i) {
            $seedDiscount([
                'code' => strtoupper(Str::random(4)).'-'.rand(1000, 9999),
                'title' => 'Promo '.$i,
                'description' => 'Generic discount '.$i,
                'type' => 'percentage',
                'value' => 10,
                'maximum_discount_amount' => null,
                'minimum_amount' => null,
            ]);
        }

        // Entries (like Statamic Collections)
        $this->command->info('📝 Seeding entries...');
        if (class_exists(EntrySeeder::class)) {
            $this->call(EntrySeeder::class);
        } else {
            $this->command->warn('  Skipping EntrySeeder (class not found)');
        }

        // Menus
        $this->command->info('🗂️ Seeding menus...');
        $menus = Menu::factory()->count(5)->create();

        foreach ($menus as $menu) {
            MenuItem::factory()
                ->count(10)
                ->state(['menu_id' => $menu->id])
                ->create();
        }

        // Suppliers
        $this->command->info('🏭 Seeding suppliers...');
        Supplier::factory()
            ->count(30)
            ->active()
            ->create();

        // Couriers
        $this->command->info('🚚 Seeding couriers...');
        Courier::factory()->count(10)->create();

        // Asset Containers
        $this->command->info('🗂️ Seeding asset containers...');
        if (\Schema::hasTable('asset_containers') && class_exists(AssetContainer::class)) {
            AssetContainer::firstOrCreate(
                ['handle' => 'main'],
                [
                    'handle' => 'main',
                    'title' => 'Main Assets',
                    'disk' => 'public',
                ],
            );
        }

        // Assets for Brands
        $this->command->info('🖼️ Seeding brand assets...');
        $this->seedBrandAssets($brands);
    }

    /**
     * Seed catalog data: categories, products, variants, prices, wishlists.
     */
    protected function seedDemoCatalog(): void
    {
        $this->command->info('🛍️ Building demo catalog...');
        $mainSite = Site::where('handle', 'main')->firstOrFail();

        // Product Options (like WooCommerce attributes)
        $this->command->info('🎨 Seeding product options...');
        $productOptions = $this->createProductOptions();
        $this->command->info('✅ Product options created: '.$productOptions->count());

        // Price Lists (Standard, Wholesale, Promotional)
        $this->command->info('💰 Seeding price lists...');
        $priceLists = $this->createPriceLists();
        $this->command->info('✅ Price lists created: '.$priceLists->count());

        // Categories with factory
        $this->command->info('📂 Seeding categories...');
        $categories = collect();

        // Create root categories
        $rootCategories = [
            ['name' => 'Men', 'slug' => 'men', 'left' => 1, 'right' => 20],
            ['name' => 'Women', 'slug' => 'women', 'left' => 21, 'right' => 40],
            ['name' => 'Kids', 'slug' => 'kids', 'left' => 41, 'right' => 60],
            ['name' => 'Electronics', 'slug' => 'electronics', 'left' => 61, 'right' => 80],
            ['name' => 'Home & Garden', 'slug' => 'home-garden', 'left' => 81, 'right' => 100],
        ];

        foreach ($rootCategories as $rootData) {
            $rootCat = Category::factory()
                ->state([
                    'site_id' => $mainSite->id,
                    'name' => $rootData['name'],
                    'slug' => $rootData['slug'],
                    'parent_id' => null,
                    'level' => 0,
                    'left' => $rootData['left'],
                    'right' => $rootData['right'],
                ])
                ->create();
            $categories->push($rootCat);

            // Create subcategories for each root category
            for ($i = 0; $i < 5; $i++) {
                $subCat = Category::factory()
                    ->state([
                        'site_id' => $mainSite->id,
                        'parent_id' => $rootCat->id,
                        'level' => 1,
                        'path' => $rootData['slug'].'/'.fake()->slug(2),
                    ])
                    ->create();
                $categories->push($subCat);
            }
        }

        // Create even more categories
        Category::factory()
            ->count(50)
            ->state([
                'site_id' => $mainSite->id,
            ])
            ->create()
            ->each(fn ($cat) => $categories->push($cat));

        $this->command->info('✅ Categories created: '.$categories->count());

        // Assets for Categories
        $this->command->info('🖼️ Seeding category assets...');
        $this->seedCategoryAssets($categories);

        // Get currencies for pricing
        $currencies = Currency::all();
        $eurCurrency = $currencies->where('code', 'EUR')->first();

        // Products with variants using factory - MASSIVE QUANTITY (OPTIMIZED)
        $this->command->info('🛍️ Seeding MASSIVE product catalog with OPTIMIZED batch insert...');
        $this->command->info('⏳ Creating 500 products with variants using batch operations...');

        $products = collect();
        $batchSize = 500; // Larger batches for better performance
        $totalProducts = 500;
        $totalBatches = (int) ceil($totalProducts / $batchSize);

        $this->command->getOutput()->progressStart($totalProducts);

        for ($batch = 0; $batch < $totalBatches; $batch++) {
            $this->command->info('');
            $this->command->info('📦 Processing batch '.($batch + 1)." of {$totalBatches}");

            // Prepare batch data
            $productsBatch = [];
            $now = now();

            for ($i = 0; $i < $batchSize && (($batch * $batchSize) + $i) < $totalProducts; $i++) {
                $raw = Product::factory()
                    ->state([
                        'site_id' => $mainSite->id,
                    ])
                    ->raw();
                // Ensure JSON fields are properly encoded for batch insert
                foreach (['options', 'tags', 'data', 'seo'] as $jsonKey) {
                    if (array_key_exists($jsonKey, $raw) && is_array($raw[$jsonKey])) {
                        $raw[$jsonKey] = json_encode($raw[$jsonKey]);
                    }
                }
                $productsBatch[] = $raw;
            }

            // BATCH INSERT products (10-20x faster!)
            \DB::table('products')->insert($productsBatch);

            // Get inserted products IDs
            $insertedProducts = Product::where('site_id', $mainSite->id)
                ->latest('id')
                ->limit(count($productsBatch))
                ->get();

            // Prepare batch data for variants and relations
            $variantsBatch = [];
            $categoryProductBatch = [];
            $variantPricesBatch = [];

            foreach ($insertedProducts as $product) {
                // Variants (3-5 per product)
                $variantCount = rand(3, 5);
                $productVariants = [];

                for ($v = 0; $v < $variantCount; $v++) {
                    $variantData = ProductVariant::factory()
                        ->state([
                            'product_id' => $product->id,
                            'site_id' => $product->site_id,
                        ])
                        ->raw();
                    // Ensure JSON fields are properly encoded for batch insert
                    foreach (['options', 'data', 'dimensions'] as $jsonKey) {
                        if (array_key_exists($jsonKey, $variantData) && is_array($variantData[$jsonKey])) {
                            $variantData[$jsonKey] = json_encode($variantData[$jsonKey]);
                        }
                    }
                    $variantsBatch[] = $variantData;
                    $productVariants[] = $variantData;
                }

                // Category relations (1-3 per product)
                $numCategories = rand(1, min(3, $categories->count()));
                $chosenCategories = $categories->random($numCategories);

                foreach ($chosenCategories as $cat) {
                    $categoryProductBatch[] = [
                        'category_id' => $cat->id,
                        'product_id' => $product->id,
                        'sort_order' => 0,
                        'is_primary' => false,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ];
                }

                $products->push($product);
                $this->command->getOutput()->progressAdvance();
            }

            // BATCH INSERT variants
            if (! empty($variantsBatch)) {
                DB::table('product_variants')->insert($variantsBatch);
            }

            // BATCH INSERT category relations
            if (! empty($categoryProductBatch)) {
                DB::table('category_product')->insert($categoryProductBatch);
            }

            // Update product with variant info (still needs individual updates)
            foreach ($insertedProducts as $product) {
                $productVariants = ProductVariant::where('product_id', $product->id)->get();
                if ($productVariants->isNotEmpty()) {
                    $product->update([
                        'default_variant_id' => $productVariants->first()->id,
                        'variants_count' => $productVariants->count(),
                        'price_min' => $productVariants->min('price'),
                        'price_max' => $productVariants->max('price'),
                    ]);

                    // BATCH INSERT prices using new Price model
                    $pricesBatch = [];
                    foreach ($productVariants as $variant) {
                        $basePrice = (int) ($variant->price * 100); // Convert to cents
                        $comparePrice = $basePrice > 2000 ? ((int) ($basePrice * 1.25)) : null;
                        $costPrice = (int) ($basePrice * 0.6);

                        // Standard retail price (global, no site, no price list)
                        $pricesBatch[] = [
                            'product_variant_id' => $variant->id,
                            'site_id' => null,
                            'price_list_id' => null,
                            'currency' => 'EUR',
                            'amount' => $basePrice,
                            'compare_at_amount' => $comparePrice,
                            'cost_amount' => $costPrice,
                            'tax_included' => false,
                            'tax_rate' => 22.0000,
                            'min_quantity' => 1,
                            'max_quantity' => null,
                            'starts_at' => now()->subMonth(),
                            'ends_at' => null,
                            'is_active' => true,
                            'metadata' => json_encode([]),
                            'created_at' => now(),
                            'updated_at' => now(),
                        ];

                        // Site-specific price for main site
                        $pricesBatch[] = [
                            'product_variant_id' => $variant->id,
                            'site_id' => $mainSite->id,
                            'price_list_id' => null,
                            'currency' => 'EUR',
                            'amount' => $basePrice,
                            'compare_at_amount' => $comparePrice,
                            'cost_amount' => $costPrice,
                            'tax_included' => false,
                            'tax_rate' => 22.0000,
                            'min_quantity' => 1,
                            'max_quantity' => null,
                            'starts_at' => now()->subMonth(),
                            'ends_at' => null,
                            'is_active' => true,
                            'metadata' => json_encode([]),
                            'created_at' => now(),
                            'updated_at' => now(),
                        ];

                        // Wholesale price (15% discount)
                        $wholesaleList = $priceLists->where('code', 'WHOLESALE')->first();
                        if ($wholesaleList) {
                            $pricesBatch[] = [
                                'product_variant_id' => $variant->id,
                                'site_id' => null,
                                'price_list_id' => $wholesaleList->id,
                                'currency' => 'EUR',
                                'amount' => (int) ($basePrice * 0.85),
                                'compare_at_amount' => $basePrice,
                                'cost_amount' => $costPrice,
                                'tax_included' => false,
                                'tax_rate' => 22.0000,
                                'min_quantity' => 1,
                                'max_quantity' => null,
                                'starts_at' => now()->subMonth(),
                                'ends_at' => null,
                                'is_active' => true,
                                'metadata' => json_encode(['discount_percentage' => 15]),
                                'created_at' => now(),
                                'updated_at' => now(),
                            ];
                        }

                        // Tier pricing (quantity breaks)
                        $tierList = $priceLists->where('code', 'TIER')->first();
                        if ($tierList && rand(1, 100) > 70) {
                            // 10+ items: 10% discount
                            $pricesBatch[] = [
                                'product_variant_id' => $variant->id,
                                'site_id' => null,
                                'price_list_id' => $tierList->id,
                                'currency' => 'EUR',
                                'amount' => (int) ($basePrice * 0.90),
                                'compare_at_amount' => $basePrice,
                                'cost_amount' => $costPrice,
                                'tax_included' => false,
                                'tax_rate' => 22.0000,
                                'min_quantity' => 10,
                                'max_quantity' => 49,
                                'starts_at' => now()->subMonth(),
                                'ends_at' => null,
                                'is_active' => true,
                                'metadata' => json_encode(['tier' => 1, 'discount_percentage' => 10]),
                                'created_at' => now(),
                                'updated_at' => now(),
                            ];

                            // 50+ items: 20% discount
                            $pricesBatch[] = [
                                'product_variant_id' => $variant->id,
                                'site_id' => null,
                                'price_list_id' => $tierList->id,
                                'currency' => 'EUR',
                                'amount' => (int) ($basePrice * 0.80),
                                'compare_at_amount' => $basePrice,
                                'cost_amount' => $costPrice,
                                'tax_included' => false,
                                'tax_rate' => 22.0000,
                                'min_quantity' => 50,
                                'max_quantity' => null,
                                'starts_at' => now()->subMonth(),
                                'ends_at' => null,
                                'is_active' => true,
                                'metadata' => json_encode(['tier' => 2, 'discount_percentage' => 20]),
                                'created_at' => now(),
                                'updated_at' => now(),
                            ];
                        }
                    }

                    if (! empty($pricesBatch)) {
                        DB::table('prices')->insert($pricesBatch);
                    }
                }
            }

            // Clear memory
            unset($productsBatch, $variantsBatch, $categoryProductBatch, $variantPricesBatch, $insertedProducts);
        }

        $this->command->getOutput()->progressFinish();
        $this->command->info('');
        $this->command->info('✅ Products with variants seeded: '.$products->count().' (OPTIMIZED)');

        // Assets for Products
        $this->command->info('🖼️ Seeding product assets...');
        $this->seedProductAssets($products);

        // Product Reviews - MASSIVE (guarded if tables/models exist)
        if (
            \Schema::hasTable('product_reviews') &&
                class_exists(ProductReview::class) &&
                class_exists(ReviewMedia::class) &&
                class_exists(ReviewVote::class)
        ) {
            $this->command->info('⭐ Seeding product reviews...');
            $productIds = $products->pluck('id')->toArray();

            foreach (array_slice($productIds, 0, 200) as $productId) {
                $reviewCount = rand(2, 15);
                $reviews = ProductReview::factory()
                    ->count($reviewCount)
                    ->state([
                        'product_id' => $productId,
                    ])
                    ->approved()
                    ->create();

                foreach ($reviews->take(rand(1, 3)) as $review) {
                    ReviewMedia::factory()
                        ->count(rand(1, 3))
                        ->state([
                            'product_review_id' => $review->id,
                        ])
                        ->create();
                }

                foreach ($reviews as $review) {
                    ReviewVote::factory()
                        ->count(rand(0, 20))
                        ->state([
                            'product_review_id' => $review->id,
                        ])
                        ->create();
                }
            }
        } else {
            $this->command->info('⏭️ Skipping product reviews (table or models not present)');
        }

        // Purchase Orders & Suppliers (guarded)
        $this->command->info('⏭️ Skipping purchase orders (disabled in sandbox)');

        // Stock Notifications (guarded)
        if (
            \Schema::hasTable('stock_notifications') &&
                \Schema::hasColumn('stock_notifications', 'product_variant_id') &&
                class_exists(StockNotification::class)
        ) {
            $this->command->info('🔔 Seeding stock notifications...');
            StockNotification::factory()->count(500)->create();
        } else {
            $this->command->info('⏭️ Skipping stock notifications (table or model not present)');
        }

        // Analytics Events
        $this->command->info('📊 Seeding analytics events...');
        if (
            \Schema::hasTable('analytics_events') &&
                class_exists(AnalyticsEvent::class) &&
                class_exists(AnalyticsEventFactory::class)
        ) {
            $this->command->info('📊 Seeding analytics events...');
            AnalyticsEvent::factory()->count(10000)->create();
        } else {
            $this->command->info('⏭️ Skipping analytics events (table or models not present)');
        }
    }

    /**
     * Seed customers, carts, wishlists, orders.
     */
    protected function seedCustomersAndOrders(): void
    {
        // Sandbox-friendly: seed only customers if table & factory exist
        if (! \Schema::hasTable('customers') || ! class_exists(CustomerFactory::class)) {
            $this->command->info('⏭️ Skipping customers (table or factory missing)');

            return;
        }

        $this->command->info('👥 Generating customer data...');
        $mainSite = Site::where('handle', 'main')->firstOrFail();
        $currency = Currency::where('code', 'EUR')->first() ?? Currency::factory()->state(['code' => 'EUR'])->create();

        // Get products with variants
        $products = Product::with('variants')->limit(100)->get();

        // Create customers
        $this->command->info('👥 Creating 100 customers...');
        $customers = Customer::factory()
            ->count(100)
            ->state([
                'site_id' => $mainSite->id,
                'status' => 'active',
            ])
            ->create();

        $this->command->info('✅ Customers seeded: '.$customers->count());

        // Create addresses for customers
        $this->command->info('📍 Creating customer addresses...');
        foreach ($customers as $customer) {
            Address::factory()
                ->count(rand(1, 2))
                ->state([
                    'addressable_type' => Customer::class,
                    'addressable_id' => $customer->id,
                ])
                ->create();
        }

        // Create subscriptions for some customers
        $this->command->info('🔄 Creating subscriptions...');
        $subscriptionCount = 0;
        foreach ($customers->take(30) as $customer) {
            // Create 1-2 subscriptions per customer (30% of customers)
            $numSubscriptions = rand(1, 2);

            for ($s = 0; $s < $numSubscriptions; $s++) {
                $product = $products->random();
                $variant = $product->variants->random();

                Subscription::factory()
                    ->state([
                        'site_id' => $mainSite->id,
                        'customer_id' => $customer->id,
                        'product_id' => $product->id,
                        'product_variant_id' => $variant->id,
                        'currency_id' => $currency->id,
                        'price' => $variant->price ?? rand(10, 100),
                    ])
                    ->create();

                $subscriptionCount++;
            }
        }

        $this->command->info('✅ Subscriptions seeded: '.$subscriptionCount);

        // Create subscription orders (recurring billing)
        $this->command->info('📦 Creating subscription orders (recurring billing)...');
        $subscriptionOrderCount = 0;
        $subscriptions = Subscription::all();

        foreach ($subscriptions as $subscription) {
            // Create 1-3 billing orders for each subscription
            $numBillingOrders = rand(1, 3);

            for ($b = 0; $b < $numBillingOrders; $b++) {
                $order = Order::factory()
                    ->state([
                        'customer_id' => $subscription->customer_id,
                        'subscription_id' => $subscription->id,
                        'site_id' => $subscription->site_id,
                        'currency_id' => $subscription->currency_id,
                    ])
                    ->create();

                // Create order line for subscription product
                $quantity = 1; // subscriptions are usually quantity 1
                $unitPrice = $subscription->price;
                $lineTotal = $unitPrice * $quantity;

                OrderLine::factory()
                    ->state([
                        'order_id' => $order->id,
                        'product_id' => $subscription->product_id,
                        'product_variant_id' => $subscription->product_variant_id,
                        'product_name' => $subscription->product->title ?? 'Subscription Product',
                        'product_sku' => $subscription->variant->sku ?? 'SUB-SKU',
                        'quantity' => $quantity,
                        'unit_price' => $unitPrice,
                        'line_total' => $lineTotal,
                    ])
                    ->create();

                // Update order totals
                $taxTotal = round($lineTotal * 0.22, 2);
                $shippingTotal = 0; // subscriptions usually don't have shipping

                $order->update([
                    'subtotal' => $lineTotal,
                    'tax_total' => $taxTotal,
                    'shipping_total' => $shippingTotal,
                    'total' => $lineTotal + $taxTotal + $shippingTotal,
                ]);

                $subscriptionOrderCount++;
            }
        }

        $this->command->info('✅ Subscription orders seeded: '.$subscriptionOrderCount);

        // Create regular orders for customers
        $this->command->info('📦 Creating regular orders...');
        $orderCount = 0;

        foreach ($customers->take(50) as $customer) {
            // Create 1-3 orders per customer
            $numOrders = rand(1, 3);

            for ($o = 0; $o < $numOrders; $o++) {
                $order = Order::factory()
                    ->state([
                        'customer_id' => $customer->id,
                        'site_id' => $mainSite->id,
                        'currency_id' => $currency->id,
                    ])
                    ->create();

                // Create 1-5 order lines per order
                $numLines = rand(1, 5);
                $orderSubtotal = 0;

                for ($l = 0; $l < $numLines; $l++) {
                    $product = $products->random();
                    $variant = $product->variants->random();

                    $quantity = rand(1, 3);
                    $unitPrice = $variant->price ?? rand(10, 100);
                    $lineTotal = $unitPrice * $quantity;

                    OrderLine::factory()
                        ->state([
                            'order_id' => $order->id,
                            'product_id' => $product->id,
                            'product_variant_id' => $variant->id,
                            'product_name' => $product->title,
                            'product_sku' => $variant->sku,
                            'quantity' => $quantity,
                            'unit_price' => $unitPrice,
                            'line_total' => $lineTotal,
                        ])
                        ->create();

                    $orderSubtotal += $lineTotal;
                }

                // Update order totals
                $taxTotal = round($orderSubtotal * 0.22, 2);
                $shippingTotal = rand(0, 10);

                $order->update([
                    'subtotal' => $orderSubtotal,
                    'tax_total' => $taxTotal,
                    'shipping_total' => $shippingTotal,
                    'total' => $orderSubtotal + $taxTotal + $shippingTotal,
                ]);

                $orderCount++;
            }
        }

        $this->command->info('✅ Orders seeded: '.$orderCount);

        return;

        foreach ($customers as $index => $customer) {
            if (($index % 20) === 0) {
                $this->command->info('  Processing customer '.($index + 1).' of '.$customers->count());
            }

            // Address using factory (1-3 addresses per customer)
            Address::factory()
                ->count(rand(1, 3))
                ->state([
                    'addressable_type' => Customer::class,
                    'addressable_id' => $customer->id,
                ])
                ->create();

            // Wishlist using factory
            $wishlist = Wishlist::factory()
                ->state([
                    'customer_id' => $customer->id,
                ])
                ->create();

            // Wishlist items using factory (3-15 items)
            $wishlistItemCount = rand(3, 15);
            WishlistItem::factory()
                ->count($wishlistItemCount)
                ->state([
                    'wishlist_id' => $wishlist->id,
                    'product_id' => $products->random()->id,
                ])
                ->create();

            // Favorites (guarded)
            if (\Schema::hasTable('favorites') && \Schema::hasColumn('favorites', 'favoriteable_type')) {
                Favorite::factory()
                    ->count(rand(5, 20))
                    ->state([
                        'customer_id' => $customer->id,
                    ])
                    ->create();
            }

            // Cart with lines using factory
            $cart = Cart::factory()
                ->state([
                    'customer_id' => $customer->id,
                    'status' => 'active',
                    'currency' => 'EUR',
                ])
                ->create();

            $lineCount = rand(1, 8);
            $lineVariants = $products->flatMap(fn ($p) => $p->variants)->shuffle()->take($lineCount);
            $totals = ['subtotal' => 0, 'tax' => 0, 'shipping' => 5];

            foreach ($lineVariants as $variant) {
                $quantity = rand(1, 5);
                $line = CartLine::factory()
                    ->state([
                        'cart_id' => $cart->id,
                        'product_id' => $variant->product_id,
                        'product_variant_id' => $variant->id,
                        'unit_price' => $variant->price,
                        'quantity' => $quantity,
                        'line_total' => $variant->price * $quantity,
                    ])
                    ->create();

                $totals['subtotal'] += $line->line_total;
            }

            $cart->update([
                'subtotal' => $totals['subtotal'],
                'tax_amount' => round($totals['subtotal'] * 0.22, 2),
                'shipping_amount' => $totals['shipping'],
                'total_amount' => $totals['subtotal'] + round($totals['subtotal'] * 0.22, 2) + $totals['shipping'],
                'items' => null,
            ]);

            // Create multiple orders per customer (1-5 orders)
            $orderCount = rand(1, 5);

            for ($o = 0; $o < $orderCount; $o++) {
                $order = Order::factory()
                    ->state([
                        'customer_id' => $customer->id,
                        'site_id' => $mainSite->id,
                        'currency_id' => $currency->id,
                        'shipping_address' => $cart->shipping_address ?? [],
                        'billing_address' => $cart->billing_address ?? [],
                    ])
                    ->create();

                $orderLineCount = rand(1, 10);
                $orderLineVariants = $products->flatMap(fn ($p) => $p->variants)->shuffle()->take($orderLineCount);
                $orderSubtotal = 0;

                foreach ($orderLineVariants as $variant) {
                    $line = OrderLine::factory()
                        ->state([
                            'order_id' => $order->id,
                            'product_id' => $variant->product_id,
                            'product_variant_id' => $variant->id,
                        ])
                        ->create();

                    $orderSubtotal += $line->line_total;
                }

                $orderTax = round($orderSubtotal * 0.22, 2);
                $orderShipping = rand(0, 20);

                $order->update([
                    'subtotal' => $orderSubtotal,
                    'tax_total' => $orderTax,
                    'shipping_total' => $orderShipping,
                    'total' => $orderSubtotal + $orderTax + $orderShipping,
                ]);

                // Transactions for orders
                Transaction::factory()
                    ->count(rand(1, 2))
                    ->state([
                        'order_id' => $order->id,
                    ])
                    ->successful()
                    ->create();
            }

            // Fidelity card using factory
            $card = $customer
                ->fidelityCard()
                ->create(
                    FidelityCardFactory::new()
                        ->state([
                            'total_points' => rand(100, 5000),
                            'available_points' => rand(50, 3000),
                            'total_earned' => rand(500, 10000),
                            'total_redeemed' => rand(0, 2000),
                            'is_active' => true,
                            'issued_at' => now()->subMonths(rand(1, 24)),
                        ])
                        ->raw(),
                );

            // Multiple fidelity transactions per customer
            for ($t = 0; $t < rand(5, 20); $t++) {
                $card->transactions()->create(
                    FidelityTransactionFactory::new()
                        ->state([
                            'type' => fake()->randomElement(['earned', 'redeemed', 'expired']),
                            'points' => rand(10, 500),
                            'expires_at' => fake()->boolean(70) ? now()->addYear() : null,
                        ])
                        ->raw(),
                );
            }
        }

        $this->command->info('✅ Customers seeded: '.$customers->count());
        $this->command->info('✅ Total orders created: ~'.($customers->count() * 3));
    }

    /**
     * Create permissions and roles for the control panel.
     */
    protected function createPermissionsAndRoles(): void
    {
        // Clear Spatie Permission cache
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $this->command->info('  Creating permissions...');

        // Define Control Panel permissions (similar to Statamic)
        $permissions = [
            // Dashboard
            'access-cp' => 'Access Control Panel',
            'view-dashboard' => 'View Dashboard',
            // Products
            'view-products' => 'View Products',
            'create-products' => 'Create Products',
            'edit-products' => 'Edit Products',
            'delete-products' => 'Delete Products',
            'manage-product-variants' => 'Manage Product Variants',
            // Categories
            'view-categories' => 'View Categories',
            'create-categories' => 'Create Categories',
            'edit-categories' => 'Edit Categories',
            'delete-categories' => 'Delete Categories',
            // Brands
            'view-brands' => 'View Brands',
            'create-brands' => 'Create Brands',
            'edit-brands' => 'Edit Brands',
            'delete-brands' => 'Delete Brands',
            // Collections
            'view-collections' => 'View Collections',
            'create-collections' => 'Create Collections',
            'edit-collections' => 'Edit Collections',
            'delete-collections' => 'Delete Collections',
            // Orders
            'view-orders' => 'View Orders',
            'edit-orders' => 'Edit Orders',
            'delete-orders' => 'Delete Orders',
            'manage-order-status' => 'Manage Order Status',
            // Customers
            'view-customers' => 'View Customers',
            'create-customers' => 'Create Customers',
            'edit-customers' => 'Edit Customers',
            'delete-customers' => 'Delete Customers',
            // Settings
            'view-settings' => 'View Settings',
            'edit-settings' => 'Edit Settings',
            'manage-sites' => 'Manage Sites',
            'manage-channels' => 'Manage Channels',
            // Users & Permissions
            'view-users' => 'View Users',
            'create-users' => 'Create Users',
            'edit-users' => 'Edit Users',
            'delete-users' => 'Delete Users',
            'manage-roles' => 'Manage Roles',
            'manage-permissions' => 'Manage Permissions',
            // Analytics & Reports
            'view-analytics' => 'View Analytics',
            'view-reports' => 'View Reports',
            // Media
            'view-media' => 'View Media',
            'upload-media' => 'Upload Media',
            'delete-media' => 'Delete Media',
        ];

        // Create permissions
        $createdPermissions = 0;
        $guard = config('auth.defaults.guard', 'web');
        foreach ($permissions as $name => $description) {
            $permission = Permission::firstOrCreate(
                [
                    'name' => $name,
                    'guard_name' => $guard,
                ],
                [
                    'name' => $name,
                    'guard_name' => $guard,
                ],
            );
            $createdPermissions++;
        }

        $this->command->info("  ✓ Created {$createdPermissions} permissions");
        $this->command->info('  Creating roles...');

        // Define roles
        $roles = [
            'super-admin' => [
                'description' => 'Super Administrator - Full access to everything',
                'permissions' => array_keys($permissions), // All permissions
            ],
            'admin' => [
                'description' => 'Administrator - Full access to store management',
                'permissions' => [
                    'access-cp',
                    'view-dashboard',
                    'view-products',
                    'create-products',
                    'edit-products',
                    'delete-products',
                    'manage-product-variants',
                    'view-categories',
                    'create-categories',
                    'edit-categories',
                    'delete-categories',
                    'view-brands',
                    'create-brands',
                    'edit-brands',
                    'delete-brands',
                    'view-collections',
                    'create-collections',
                    'edit-collections',
                    'delete-collections',
                    'view-orders',
                    'edit-orders',
                    'manage-order-status',
                    'view-customers',
                    'create-customers',
                    'edit-customers',
                    'view-settings',
                    'edit-settings',
                    'view-analytics',
                    'view-reports',
                    'view-media',
                    'upload-media',
                ],
            ],
            'manager' => [
                'description' => 'Store Manager - Limited administrative access',
                'permissions' => [
                    'access-cp',
                    'view-dashboard',
                    'view-products',
                    'create-products',
                    'edit-products',
                    'view-categories',
                    'create-categories',
                    'edit-categories',
                    'view-brands',
                    'create-brands',
                    'edit-brands',
                    'view-collections',
                    'create-collections',
                    'edit-collections',
                    'view-orders',
                    'edit-orders',
                    'manage-order-status',
                    'view-customers',
                    'create-customers',
                    'edit-customers',
                    'view-analytics',
                    'view-reports',
                    'view-media',
                    'upload-media',
                ],
            ],
            'editor' => [
                'description' => 'Content Editor - Content management only',
                'permissions' => [
                    'access-cp',
                    'view-dashboard',
                    'view-products',
                    'create-products',
                    'edit-products',
                    'view-categories',
                    'create-categories',
                    'edit-categories',
                    'view-brands',
                    'create-brands',
                    'edit-brands',
                    'view-collections',
                    'create-collections',
                    'edit-collections',
                    'view-media',
                    'upload-media',
                ],
            ],
        ];

        // Create roles and assign permissions
        $createdRoles = 0;
        foreach ($roles as $roleName => $roleData) {
            $role = Role::firstOrCreate(
                [
                    'name' => $roleName,
                    'guard_name' => $guard,
                ],
                [
                    'name' => $roleName,
                    'guard_name' => $guard,
                ],
            );

            // Sync permissions
            $availablePermissions = Permission::where('guard_name', $guard)
                ->whereIn('name', $roleData['permissions'])
                ->get();
            $role->syncPermissions($availablePermissions);

            $createdRoles++;
            $this->command->info("  ✓ Created role: {$roleName} with {$availablePermissions->count()} permissions");
        }

        $this->command->info(
            "✅ Roles and permissions setup completed ({$createdRoles} roles, {$createdPermissions} permissions)",
        );
    }

    /**
     * Create global product options (like WooCommerce attributes)
     */
    protected function createProductOptions(): Collection
    {
        $options = collect();

        // Color option with swatch values
        $colorOption = ProductOption::create([
            'name' => 'Color',
            'slug' => 'color',
            'type' => 'swatch',
            'is_global' => true,
            'use_for_variants' => true,
            'is_visible' => true,
            'position' => 1,
        ]);

        $colors = [
            ['label' => 'Red', 'value' => 'red', 'color_hex' => '#FF0000'],
            ['label' => 'Blue', 'value' => 'blue', 'color_hex' => '#0000FF'],
            ['label' => 'Green', 'value' => 'green', 'color_hex' => '#00FF00'],
            ['label' => 'Black', 'value' => 'black', 'color_hex' => '#000000'],
            ['label' => 'White', 'value' => 'white', 'color_hex' => '#FFFFFF'],
            ['label' => 'Yellow', 'value' => 'yellow', 'color_hex' => '#FFFF00'],
            ['label' => 'Orange', 'value' => 'orange', 'color_hex' => '#FFA500'],
            ['label' => 'Purple', 'value' => 'purple', 'color_hex' => '#800080'],
            ['label' => 'Pink', 'value' => 'pink', 'color_hex' => '#FFC0CB'],
            ['label' => 'Gray', 'value' => 'gray', 'color_hex' => '#808080'],
        ];

        foreach ($colors as $index => $color) {
            ProductOptionValue::create([
                'product_option_id' => $colorOption->id,
                'label' => $color['label'],
                'value' => $color['value'],
                'color_hex' => $color['color_hex'],
                'position' => $index + 1,
            ]);
        }

        $options->push($colorOption);

        // Size option
        $sizeOption = ProductOption::create([
            'name' => 'Size',
            'slug' => 'size',
            'type' => 'select',
            'is_global' => true,
            'use_for_variants' => true,
            'is_visible' => true,
            'position' => 2,
        ]);

        $sizes = ['XS', 'S', 'M', 'L', 'XL', 'XXL', 'XXXL'];
        foreach ($sizes as $index => $size) {
            ProductOptionValue::create([
                'product_option_id' => $sizeOption->id,
                'label' => $size,
                'value' => strtolower($size),
                'position' => $index + 1,
            ]);
        }

        $options->push($sizeOption);

        // Material option
        $materialOption = ProductOption::create([
            'name' => 'Material',
            'slug' => 'material',
            'type' => 'select',
            'is_global' => true,
            'use_for_variants' => false,
            'is_visible' => true,
            'position' => 3,
        ]);

        $materials = ['Cotton', 'Polyester', 'Wool', 'Silk', 'Leather', 'Denim', 'Linen'];
        foreach ($materials as $index => $material) {
            ProductOptionValue::create([
                'product_option_id' => $materialOption->id,
                'label' => $material,
                'value' => strtolower($material),
                'position' => $index + 1,
            ]);
        }

        $options->push($materialOption);

        // Style option
        $styleOption = ProductOption::create([
            'name' => 'Style',
            'slug' => 'style',
            'type' => 'radio',
            'is_global' => true,
            'use_for_variants' => false,
            'is_visible' => true,
            'position' => 4,
        ]);

        $styles = ['Casual', 'Formal', 'Sport', 'Vintage', 'Modern'];
        foreach ($styles as $index => $style) {
            ProductOptionValue::create([
                'product_option_id' => $styleOption->id,
                'label' => $style,
                'value' => strtolower($style),
                'position' => $index + 1,
            ]);
        }

        $options->push($styleOption);

        return $options;
    }

    /**
     * Create price lists for different pricing tiers
     */
    protected function createPriceLists(): Collection
    {
        $priceLists = collect();

        // Standard retail price list (default)
        $standardList = PriceList::create([
            'name' => 'Standard Retail',
            'code' => 'STANDARD',
            'type' => 'standard',
            'priority' => 1,
            'is_active' => true,
            'starts_at' => now()->subMonth(),
            'ends_at' => null,
            'description' => 'Standard retail pricing for all customers',
        ]);
        $priceLists->push($standardList);

        // Wholesale price list
        $wholesaleList = PriceList::create([
            'name' => 'Wholesale',
            'code' => 'WHOLESALE',
            'type' => 'wholesale',
            'priority' => 2,
            'is_active' => true,
            'starts_at' => now()->subMonth(),
            'ends_at' => null,
            'description' => 'Special wholesale pricing for bulk buyers',
        ]);
        $priceLists->push($wholesaleList);

        // Black Friday promotional price list
        $blackFridayList = PriceList::create([
            'name' => 'Black Friday Sale',
            'code' => 'BLACKFRIDAY2025',
            'type' => 'promotional',
            'priority' => 10,
            'is_active' => true,
            'starts_at' => now()->addMonths(11)->startOfDay(),
            'ends_at' => now()->addMonths(11)->addDays(4)->endOfDay(),
            'description' => 'Black Friday promotional pricing',
        ]);
        $priceLists->push($blackFridayList);

        // Summer Sale promotional price list
        $summerList = PriceList::create([
            'name' => 'Summer Sale',
            'code' => 'SUMMER2025',
            'type' => 'promotional',
            'priority' => 9,
            'is_active' => true,
            'starts_at' => now()->addMonths(6)->startOfDay(),
            'ends_at' => now()->addMonths(8)->endOfDay(),
            'description' => 'Summer seasonal pricing',
        ]);
        $priceLists->push($summerList);

        // Tier pricing (quantity breaks)
        $tierList = PriceList::create([
            'name' => 'Volume Discounts',
            'code' => 'TIER',
            'type' => 'tier',
            'priority' => 5,
            'is_active' => true,
            'starts_at' => now()->subMonth(),
            'ends_at' => null,
            'description' => 'Quantity-based tier pricing',
        ]);
        $priceLists->push($tierList);

        return $priceLists;
    }

    /**
     * Seed assets for brands (logo + banner)
     */
    protected function seedBrandAssets($brands): void
    {
        if (! \Schema::hasTable('assets') || ! class_exists(Asset::class)) {
            $this->command->warn('  ⏭️  Skipping brand assets (assets table or model not present)');

            return;
        }

        $assetContainer = AssetContainer::first();
        if (! $assetContainer) {
            $this->command->warn('  ⏭️  Skipping brand assets (no asset container found)');

            return;
        }

        foreach ($brands->take(20) as $brand) {
            // Logo (SVG or PNG)
            $logoAsset = Asset::factory()
                ->state([
                    'container' => $assetContainer->handle,
                    'mime_type' => fake()->randomElement(['image/svg+xml', 'image/png']),
                    'width' => 200,
                    'height' => 200,
                ])
                ->create();

            $brand->attachAsset($logoAsset, 'logo', ['is_primary' => true]);

            // Banner (optional, 70% chance)
            if (rand(1, 100) <= 70) {
                $bannerAsset = Asset::factory()
                    ->state([
                        'container' => $assetContainer->handle,
                        'mime_type' => 'image/jpeg',
                        'width' => 1920,
                        'height' => 600,
                    ])
                    ->create();

                $brand->attachAsset($bannerAsset, 'banner', ['is_primary' => true]);
            }
        }

        $this->command->info('  ✅ Brand assets seeded for 20 brands');
    }

    /**
     * Seed assets for categories (featured_image + banner)
     */
    protected function seedCategoryAssets($categories): void
    {
        if (! \Schema::hasTable('assets') || ! class_exists(Asset::class)) {
            $this->command->warn('  ⏭️  Skipping category assets (assets table or model not present)');

            return;
        }

        $assetContainer = AssetContainer::first();
        if (! $assetContainer) {
            $this->command->warn('  ⏭️  Skipping category assets (no asset container found)');

            return;
        }

        foreach ($categories->take(30) as $category) {
            // Featured image (80% chance)
            if (rand(1, 100) <= 80) {
                $featuredAsset = Asset::factory()
                    ->state([
                        'container' => $assetContainer->handle,
                        'mime_type' => 'image/webp',
                        'width' => 800,
                        'height' => 800,
                    ])
                    ->create();

                $category->attachAsset($featuredAsset, 'featured_image', ['is_primary' => true]);
            }

            // Banner (50% chance)
            if (rand(1, 100) <= 50) {
                $bannerAsset = Asset::factory()
                    ->state([
                        'container' => $assetContainer->handle,
                        'mime_type' => 'image/jpeg',
                        'width' => 1920,
                        'height' => 400,
                    ])
                    ->create();

                $category->attachAsset($bannerAsset, 'banner', ['is_primary' => true]);
            }
        }

        $this->command->info('  ✅ Category assets seeded for 30 categories');
    }

    /**
     * Seed assets for products (images, gallery, documents, videos)
     */
    protected function seedProductAssets($products): void
    {
        if (! \Schema::hasTable('assets') || ! class_exists(Asset::class)) {
            $this->command->warn('  ⏭️  Skipping product assets (assets table or model not present)');

            return;
        }

        $assetContainer = AssetContainer::first();
        if (! $assetContainer) {
            $this->command->warn('  ⏭️  Skipping product assets (no asset container found)');

            return;
        }

        $this->command->getOutput()->progressStart($products->count());

        foreach ($products as $product) {
            // Product images (1-5 images per product)
            $imageCount = rand(1, 5);
            for ($i = 0; $i < $imageCount; $i++) {
                $imageAsset = Asset::factory()
                    ->state([
                        'container' => $assetContainer->handle,
                        'mime_type' => fake()->randomElement(['image/jpeg', 'image/png', 'image/webp']),
                        'width' => 1200,
                        'height' => 1200,
                    ])
                    ->create();

                $product->attachAsset($imageAsset, 'images', [
                    'is_primary' => $i === 0,
                    'sort_order' => $i,
                ]);
            }

            // Gallery images (30% chance, 3-8 images)
            if (rand(1, 100) <= 30) {
                $galleryCount = rand(3, 8);
                for ($g = 0; $g < $galleryCount; $g++) {
                    $galleryAsset = Asset::factory()
                        ->state([
                            'container' => $assetContainer->handle,
                            'mime_type' => 'image/webp',
                            'width' => 1920,
                            'height' => 1280,
                        ])
                        ->create();

                    $product->attachAsset($galleryAsset, 'gallery', ['sort_order' => $g]);
                }
            }

            // Documents (20% chance, 1-3 PDFs)
            if (rand(1, 100) <= 20) {
                $docCount = rand(1, 3);
                for ($d = 0; $d < $docCount; $d++) {
                    $docAsset = Asset::factory()
                        ->state([
                            'container' => $assetContainer->handle,
                            'mime_type' => 'application/pdf',
                            'width' => null,
                            'height' => null,
                            'size' => rand(100000, 5000000), // 100KB - 5MB
                        ])
                        ->create();

                    $product->attachAsset($docAsset, 'documents', ['sort_order' => $d]);
                }
            }

            // Videos (10% chance, 1-2 videos)
            if (rand(1, 100) <= 10) {
                $videoCount = rand(1, 2);
                for ($v = 0; $v < $videoCount; $v++) {
                    $videoAsset = Asset::factory()
                        ->state([
                            'container' => $assetContainer->handle,
                            'mime_type' => fake()->randomElement(['video/mp4', 'video/webm']),
                            'width' => 1920,
                            'height' => 1080,
                            'size' => rand(5000000, 50000000), // 5MB - 50MB
                        ])
                        ->create();

                    $product->attachAsset($videoAsset, 'videos', ['sort_order' => $v]);
                }
            }

            $this->command->getOutput()->progressAdvance();
        }

        $this->command->getOutput()->progressFinish();
        $this->command->info('  ✅ Product assets seeded for all products');
    }
}
