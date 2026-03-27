<?php

namespace Cartino\Database\Seeders;

use Cartino\Models\Entry;
use Illuminate\Database\Seeder;

class EntrySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Home Page
        Entry::create([
            'collection' => 'pages',
            'slug' => 'home',
            'title' => 'Home',
            'data' => [
                'template' => 'home',
                'hero' => [
                    'title' => 'Benvenuto su Cartino',
                    'subtitle' => 'Il tuo e-commerce di fiducia',
                    'cta_text' => 'Scopri i Prodotti',
                    'cta_url' => '/products',
                    'background_image' => '/images/hero-bg.jpg',
                ],
                'featured_categories' => [1, 2, 3],
                'seo_title' => 'Cartino - Il tuo e-commerce di fiducia',
                'seo_description' => 'Scopri i migliori prodotti online con Cartino',
            ],
            'status' => 'published',
            'published_at' => now(),
            'locale' => 'it',
            'order' => 0,
        ]);

        // About Page
        Entry::create([
            'collection' => 'pages',
            'slug' => 'about',
            'title' => 'Chi Siamo',
            'data' => [
                'template' => 'default',
                'content' => '<h2>La Nostra Storia</h2><p>Cartino è nato nel 2024 con l\'obiettivo di offrire prodotti di qualità a prezzi competitivi...</p>',
                'team' => [
                    [
                        'name' => 'Mario Rossi',
                        'role' => 'CEO',
                        'image' => '/images/team/mario.jpg',
                    ],
                    [
                        'name' => 'Laura Bianchi',
                        'role' => 'CTO',
                        'image' => '/images/team/laura.jpg',
                    ],
                ],
                'seo_title' => 'Chi Siamo - Cartino',
                'seo_description' => 'Scopri la storia di Cartino e il nostro team',
            ],
            'status' => 'published',
            'published_at' => now(),
            'locale' => 'it',
            'order' => 1,
        ]);

        // Contact Page
        Entry::create([
            'collection' => 'pages',
            'slug' => 'contact',
            'title' => 'Contatti',
            'data' => [
                'template' => 'contact',
                'content' => '<p>Hai domande? Siamo qui per aiutarti!</p>',
                'show_contact_form' => true,
                'contact_info' => [
                    'address' => 'Via Roma 123, 20100 Milano',
                    'phone' => '+39 123 456 7890',
                    'email' => 'info@cartino.shop',
                ],
                'seo_title' => 'Contatti - Cartino',
                'seo_description' => 'Contattaci per qualsiasi informazione',
            ],
            'status' => 'published',
            'published_at' => now(),
            'locale' => 'it',
            'order' => 2,
        ]);

        // Privacy Policy
        Entry::create([
            'collection' => 'pages',
            'slug' => 'privacy',
            'title' => 'Privacy Policy',
            'data' => [
                'template' => 'legal',
                'content' => '<h2>Informativa sulla Privacy</h2><p>La presente informativa sulla privacy descrive come raccogliamo e utilizziamo i tuoi dati personali...</p>',
                'last_updated' => now()->format('d/m/Y'),
                'seo_title' => 'Privacy Policy - Cartino',
            ],
            'status' => 'published',
            'published_at' => now(),
            'locale' => 'it',
            'order' => 3,
        ]);

        // Terms and Conditions
        Entry::create([
            'collection' => 'pages',
            'slug' => 'terms',
            'title' => 'Termini e Condizioni',
            'data' => [
                'template' => 'legal',
                'content' => '<h2>Termini e Condizioni di Vendita</h2><p>I presenti termini e condizioni regolano l\'utilizzo del nostro sito web...</p>',
                'last_updated' => now()->format('d/m/Y'),
                'seo_title' => 'Termini e Condizioni - Cartino',
            ],
            'status' => 'published',
            'published_at' => now(),
            'locale' => 'it',
            'order' => 4,
        ]);

        // Blog Posts
        Entry::create([
            'collection' => 'blog',
            'slug' => 'benvenuto-su-cartino',
            'title' => 'Benvenuto su Cartino: La Nuova Era dello Shopping Online',
            'data' => [
                'excerpt' => 'Scopri come Cartino sta rivoluzionando l\'esperienza di shopping online',
                'content' => '<p>Siamo entusiasti di presentarvi Cartino, la nuova piattaforma di e-commerce che cambia le regole del gioco...</p>',
                'featured_image' => '/images/blog/welcome.jpg',
                'categories' => ['News', 'Aggiornamenti'],
                'tags' => ['e-commerce', 'shopping', 'novità'],
                'author_name' => 'Redazione Cartino',
                'reading_time' => '5 min',
                'seo_title' => 'Benvenuto su Cartino - Blog',
                'seo_description' => 'Scopri la nuova era dello shopping online con Cartino',
            ],
            'status' => 'published',
            'published_at' => now()->subDays(7),
            'locale' => 'it',
            'order' => 0,
        ]);

        Entry::create([
            'collection' => 'blog',
            'slug' => 'guida-acquisti-online',
            'title' => '10 Consigli per Acquisti Online Sicuri e Convenienti',
            'data' => [
                'excerpt' => 'Una guida completa per fare acquisti online in sicurezza e risparmiare',
                'content' => '<h2>1. Verifica l\'affidabilità del sito</h2><p>Prima di effettuare un acquisto, assicurati che il sito sia sicuro...</p>',
                'featured_image' => '/images/blog/shopping-guide.jpg',
                'categories' => ['Guide', 'Shopping'],
                'tags' => ['consigli', 'sicurezza', 'risparmio'],
                'author_name' => 'Laura Bianchi',
                'reading_time' => '8 min',
                'seo_title' => 'Guida agli Acquisti Online Sicuri - Cartino Blog',
                'seo_description' => '10 consigli essenziali per fare shopping online in sicurezza',
            ],
            'status' => 'published',
            'published_at' => now()->subDays(3),
            'locale' => 'it',
            'order' => 1,
        ]);

        Entry::create([
            'collection' => 'blog',
            'slug' => 'tendenze-ecommerce-2024',
            'title' => 'Le Tendenze E-commerce del 2024: Cosa Aspettarsi',
            'data' => [
                'excerpt' => 'Scopri le principali tendenze che stanno plasmando il futuro dell\'e-commerce',
                'content' => '<p>Il 2024 porta con sé innovazioni significative nel mondo dell\'e-commerce...</p>',
                'featured_image' => '/images/blog/trends-2024.jpg',
                'categories' => ['Tendenze', 'Business'],
                'tags' => ['e-commerce', '2024', 'innovazione', 'tendenze'],
                'author_name' => 'Mario Rossi',
                'reading_time' => '6 min',
                'seo_title' => 'Tendenze E-commerce 2024 - Cartino Blog',
                'seo_description' => 'Scopri le tendenze che stanno rivoluzionando l\'e-commerce nel 2024',
            ],
            'status' => 'published',
            'published_at' => now()->subDay(),
            'locale' => 'it',
            'order' => 2,
        ]);

        // Draft blog post
        Entry::create([
            'collection' => 'blog',
            'slug' => 'sostenibilita-ecommerce',
            'title' => 'E-commerce Sostenibile: Come Ridurre l\'Impatto Ambientale',
            'data' => [
                'excerpt' => 'L\'importanza della sostenibilità nel commercio elettronico',
                'content' => '<p>Bozza in lavorazione...</p>',
                'featured_image' => null,
                'categories' => ['Sostenibilità'],
                'tags' => ['ambiente', 'green', 'sostenibilità'],
                'author_name' => 'Redazione Cartino',
                'reading_time' => null,
            ],
            'status' => 'draft',
            'published_at' => null,
            'locale' => 'it',
            'order' => 3,
        ]);
    }
}
