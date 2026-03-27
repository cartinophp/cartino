<?php

namespace Cartino\Http\Controllers\Cp;

use Cartino\Http\Requests\UserPreferenceRequest;
use Cartino\Models\UserPreference;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class UserPreferenceController extends Controller
{
    public function index(Request $request): Response
    {
        $userId = auth()->id();

        // Get all user preferences grouped by type
        $preferences = UserPreference::where('user_id', $userId)->get()->groupBy('type');

        // Get default preferences structure
        $defaultPreferences = $this->getDefaultPreferencesStructure();

        return Inertia::render('UserPreferences/user-preferences-index', [
            'preferences' => $preferences,
            'default_preferences' => $defaultPreferences,
        ]);
    }

    public function store(UserPreferenceRequest $request)
    {
        $validated = $request->validated();

        UserPreference::setForUser(auth()->id(), $validated['type'], $validated['key'], $validated['value']);

        return response()->json([
            'message' => 'Preferences saved successfully',
        ]);
    }

    public function update(UserPreferenceRequest $request, UserPreference $preference)
    {
        $this->authorize('update', $preference);

        $validated = $request->validated();

        $preference->update([
            'value' => $validated['value'],
        ]);

        return response()->json([
            'message' => 'Preference updated successfully',
            'preference' => $preference->fresh(),
        ]);
    }

    public function destroy(UserPreference $preference)
    {
        $this->authorize('delete', $preference);

        $preference->delete();

        return response()->json([
            'message' => 'Preference deleted successfully',
        ]);
    }

    public function bulkUpdate(Request $request)
    {
        $request->validate([
            'preferences' => 'required|array',
            'preferences.*.type' => 'required|string',
            'preferences.*.key' => 'required|string',
            'preferences.*.value' => 'required',
        ]);

        $userId = auth()->id();

        foreach ($request->preferences as $preferenceData) {
            UserPreference::setForUser(
                $userId,
                $preferenceData['type'],
                $preferenceData['key'],
                $preferenceData['value'],
            );
        }

        return response()->json([
            'message' => 'Preferences updated successfully',
        ]);
    }

    public function reset(Request $request)
    {
        $request->validate([
            'type' => 'sometimes|string',
        ]);

        $query = UserPreference::where('user_id', auth()->id());

        if ($request->has('type')) {
            $query->where('type', $request->type);
        }

        $query->delete();

        return response()->json([
            'message' => 'Preferences reset successfully',
        ]);
    }

    public function export(Request $request)
    {
        $userId = auth()->id();

        $preferences = UserPreference::where('user_id', $userId)
            ->get()
            ->mapWithKeys(function ($preference) {
                return ["{$preference->type}.{$preference->key}" => $preference->value];
            });

        return response()->json([
            'preferences' => $preferences,
            'exported_at' => now()->toISOString(),
            'user_id' => $userId,
        ]);
    }

    public function import(Request $request)
    {
        $request->validate([
            'preferences' => 'required|array',
            'overwrite' => 'sometimes|boolean',
        ]);

        $userId = auth()->id();
        $overwrite = $request->boolean('overwrite', false);

        $imported = 0;
        $skipped = 0;

        foreach ($request->preferences as $key => $value) {
            [$type, $preferenceKey] = explode('.', $key, 2);

            if ($overwrite || ! UserPreference::existsForUser($userId, $type, $preferenceKey)) {
                UserPreference::setForUser($userId, $type, $preferenceKey, $value);
                $imported++;
            } else {
                $skipped++;
            }
        }

        return response()->json([
            'message' => 'Preferences imported successfully',
            'imported' => $imported,
            'skipped' => $skipped,
        ]);
    }

    private function getDefaultPreferencesStructure(): array
    {
        return [
            'dashboard' => [
                'name' => 'Dashboard',
                'description' => 'Customize your dashboard layout and widgets',
                'preferences' => [
                    'widgets' => [
                        'name' => 'Visible Widgets',
                        'type' => 'array',
                        'default' => [
                            'revenue_chart',
                            'orders_chart',
                            'visitors_chart',
                            'top_products',
                            'recent_orders',
                        ],
                        'options' => [
                            'revenue_chart' => 'Revenue Chart',
                            'orders_chart' => 'Orders Chart',
                            'visitors_chart' => 'Visitors Chart',
                            'top_products' => 'Top Products',
                            'recent_orders' => 'Recent Orders',
                            'low_stock_alerts' => 'Low Stock Alerts',
                            'new_customers' => 'New Customers',
                        ],
                    ],
                    'layout' => [
                        'name' => 'Layout Style',
                        'type' => 'select',
                        'default' => 'grid',
                        'options' => [
                            'grid' => 'Grid Layout',
                            'list' => 'List Layout',
                        ],
                    ],
                    'refresh_interval' => [
                        'name' => 'Auto Refresh (minutes)',
                        'type' => 'number',
                        'default' => 5,
                        'min' => 1,
                        'max' => 60,
                    ],
                ],
            ],
            'table' => [
                'name' => 'Table Settings',
                'description' => 'Customize table display preferences',
                'preferences' => [
                    'products_columns' => [
                        'name' => 'Products Table Columns',
                        'type' => 'array',
                        'default' => [
                            'name',
                            'sku',
                            'status',
                            'inventory',
                            'price',
                            'created_at',
                        ],
                        'options' => [
                            'name' => 'Name',
                            'sku' => 'SKU',
                            'status' => 'Status',
                            'inventory' => 'Inventory',
                            'price' => 'Price',
                            'category' => 'Category',
                            'created_at' => 'Created',
                            'updated_at' => 'Updated',
                        ],
                    ],
                    'orders_columns' => [
                        'name' => 'Orders Table Columns',
                        'type' => 'array',
                        'default' => [
                            'number',
                            'customer',
                            'status',
                            'total',
                            'created_at',
                        ],
                        'options' => [
                            'number' => 'Order Number',
                            'customer' => 'Customer',
                            'status' => 'Status',
                            'total' => 'Total',
                            'items_count' => 'Items Count',
                            'payment_status' => 'Payment Status',
                            'created_at' => 'Created',
                            'updated_at' => 'Updated',
                        ],
                    ],
                    'customers_columns' => [
                        'name' => 'Customers Table Columns',
                        'type' => 'array',
                        'default' => [
                            'name',
                            'email',
                            'orders_count',
                            'total_spent',
                            'created_at',
                        ],
                        'options' => [
                            'name' => 'Name',
                            'email' => 'Email',
                            'orders_count' => 'Orders Count',
                            'total_spent' => 'Total Spent',
                            'last_order_at' => 'Last Order',
                            'created_at' => 'Created',
                        ],
                    ],
                    'page_size' => [
                        'name' => 'Items per Page',
                        'type' => 'select',
                        'default' => 25,
                        'options' => [
                            10 => '10 items',
                            25 => '25 items',
                            50 => '50 items',
                            100 => '100 items',
                        ],
                    ],
                ],
            ],
            'notifications' => [
                'name' => 'Notifications',
                'description' => 'Configure notification preferences',
                'preferences' => [
                    'email_notifications' => [
                        'name' => 'Email Notifications',
                        'type' => 'array',
                        'default' => [
                            'new_order',
                            'low_stock',
                            'payment_failed',
                        ],
                        'options' => [
                            'new_order' => 'New Orders',
                            'order_cancelled' => 'Order Cancellations',
                            'low_stock' => 'Low Stock Alerts',
                            'out_of_stock' => 'Out of Stock Alerts',
                            'payment_failed' => 'Failed Payments',
                            'new_customer' => 'New Customer Registration',
                            'product_review' => 'New Product Reviews',
                        ],
                    ],
                    'browser_notifications' => [
                        'name' => 'Browser Notifications',
                        'type' => 'boolean',
                        'default' => true,
                    ],
                    'notification_sound' => [
                        'name' => 'Notification Sound',
                        'type' => 'boolean',
                        'default' => false,
                    ],
                ],
            ],
            'appearance' => [
                'name' => 'Appearance',
                'description' => 'Customize the visual appearance',
                'preferences' => [
                    'theme' => [
                        'name' => 'Theme',
                        'type' => 'select',
                        'default' => 'light',
                        'options' => [
                            'light' => 'Light Theme',
                            'dark' => 'Dark Theme',
                            'auto' => 'Auto (System)',
                        ],
                    ],
                    'color_scheme' => [
                        'name' => 'Color Scheme',
                        'type' => 'select',
                        'default' => 'blue',
                        'options' => [
                            'blue' => 'Blue',
                            'green' => 'Green',
                            'purple' => 'Purple',
                            'orange' => 'Orange',
                            'red' => 'Red',
                        ],
                    ],
                    'sidebar_collapsed' => [
                        'name' => 'Sidebar Collapsed',
                        'type' => 'boolean',
                        'default' => false,
                    ],
                    'compact_mode' => [
                        'name' => 'Compact Mode',
                        'type' => 'boolean',
                        'default' => false,
                    ],
                ],
            ],
        ];
    }
}
