<?php

namespace Cartino\Database\Seeders;

use Cartino\Models\GlobalSet;
use Illuminate\Database\Seeder;

class GlobalSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Site Settings
        GlobalSet::create([
            'handle' => 'site_settings',
            'title' => 'Site Settings',
            'data' => [
                'site_name' => 'Cartino Shop',
                'site_description' => 'Il tuo e-commerce di fiducia',
                'contact_email' => 'info@cartino.shop',
                'contact_phone' => '+39 123 456 7890',
                'address' => [
                    'street' => 'Via Roma 123',
                    'city' => 'Milano',
                    'postcode' => '20100',
                    'country' => 'IT',
                ],
                'vat_number' => 'IT12345678901',
                'company_name' => 'Cartino S.r.l.',
            ],
        ]);

        // Social Media
        GlobalSet::create([
            'handle' => 'social_media',
            'title' => 'Social Media',
            'data' => [
                'facebook' => 'https://facebook.com/cartino',
                'instagram' => 'https://instagram.com/cartino',
                'twitter' => 'https://twitter.com/cartino',
                'linkedin' => 'https://linkedin.com/company/cartino',
                'youtube' => 'https://youtube.com/cartino',
                'tiktok' => null,
            ],
        ]);

        // Header Settings
        GlobalSet::create([
            'handle' => 'header',
            'title' => 'Header',
            'data' => [
                'logo_url' => '/images/logo.png',
                'tagline' => 'Qualità e convenienza',
                'show_search' => true,
                'show_cart' => true,
                'show_wishlist' => true,
                'announcement_bar' => [
                    'enabled' => true,
                    'text' => 'Spedizione gratuita per ordini sopra €50',
                    'background_color' => '#000000',
                    'text_color' => '#ffffff',
                ],
            ],
        ]);

        // Footer Settings
        GlobalSet::create([
            'handle' => 'footer',
            'title' => 'Footer',
            'data' => [
                'copyright' => '© 2024 Cartino. Tutti i diritti riservati.',
                'payment_methods' => ['visa', 'mastercard', 'paypal', 'stripe'],
                'newsletter' => [
                    'enabled' => true,
                    'title' => 'Iscriviti alla Newsletter',
                    'description' => 'Ricevi le ultime offerte e novità',
                ],
                'links' => [
                    [
                        'title' => 'Chi Siamo',
                        'url' => '/about',
                    ],
                    [
                        'title' => 'Termini e Condizioni',
                        'url' => '/terms',
                    ],
                    [
                        'title' => 'Privacy Policy',
                        'url' => '/privacy',
                    ],
                    [
                        'title' => 'Contatti',
                        'url' => '/contact',
                    ],
                ],
            ],
        ]);

        // SEO Settings
        GlobalSet::create([
            'handle' => 'seo',
            'title' => 'SEO Settings',
            'data' => [
                'meta_title' => 'Cartino - Il tuo e-commerce di fiducia',
                'meta_description' => 'Scopri i migliori prodotti online con Cartino. Qualità, convenienza e spedizione veloce.',
                'meta_keywords' => 'e-commerce, shop online, prodotti italiani',
                'og_image' => '/images/og-image.jpg',
                'twitter_handle' => '@cartino',
                'google_analytics_id' => null,
                'google_tag_manager_id' => null,
                'facebook_pixel_id' => null,
            ],
        ]);

        // Checkout Settings
        GlobalSet::create([
            'handle' => 'checkout',
            'title' => 'Checkout Settings',
            'data' => [
                'guest_checkout_enabled' => true,
                'require_account' => false,
                'terms_url' => '/terms',
                'privacy_url' => '/privacy',
                'minimum_order_amount' => 10.00,
                'free_shipping_threshold' => 50.00,
                'tax_included_in_prices' => true,
            ],
        ]);
    }
}
