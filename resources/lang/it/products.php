<?php

return [
    'title' => 'Prodotti',
    'single' => 'Prodotto',
    'create' => 'Nuovo Prodotto',
    'edit' => 'Modifica Prodotto',
    'list' => 'Elenco Prodotti',
    'search_placeholder' => 'Cerca prodotti...',
    'no_products' => 'Nessun prodotto trovato',

    // Fields
    'fields' => [
        'name' => 'Nome',
        'slug' => 'Slug',
        'description' => 'Descrizione',
        'excerpt' => 'Riassunto',
        'sku' => 'Codice SKU',
        'price' => 'Prezzo',
        'compare_price' => 'Prezzo di confronto',
        'cost_price' => 'Prezzo di costo',
        'weight' => 'Peso',
        'weight_unit' => 'Unità di peso',
        'category' => 'Categoria',
        'brand' => 'Marchio',
        'vendor' => 'Fornitore',
        'barcode' => 'Codice a barre',
        'track_quantity' => 'Traccia quantità',
        'quantity' => 'Quantità',
        'min_quantity' => 'Quantità minima',
        'security_stock' => 'Scorta di sicurezza',
        'stock_status' => 'Stato scorte',
        'backorder' => 'Ordine arretrato',
        'require_shipping' => 'Richiede spedizione',
        'is_digital' => 'Prodotto digitale',
        'seo_title' => 'Titolo SEO',
        'seo_description' => 'Descrizione SEO',
        'meta_keywords' => 'Parole chiave meta',
        'featured' => 'In evidenza',
        'is_visible' => 'Visibile',
        'publish_date' => 'Data di pubblicazione',
        'images' => 'Immagini',
        'gallery' => 'Galleria',
        'variants' => 'Varianti',
        'attributes' => 'Attributi',
        'tags' => 'Tag',
        'related_products' => 'Prodotti correlati',
        'cross_selling' => 'Cross-selling',
        'up_selling' => 'Up-selling',
    ],

    // Tabs
    'tabs' => [
        'general' => 'Generale',
        'pricing' => 'Prezzi',
        'inventory' => 'Inventario',
        'shipping' => 'Spedizione',
        'seo' => 'SEO',
        'media' => 'Media',
        'variants' => 'Varianti',
        'attributes' => 'Attributi',
        'related' => 'Prodotti correlati',
        'reviews' => 'Recensioni',
    ],

    // Stock status
    'stock_status' => [
        'in_stock' => 'In magazzino',
        'out_of_stock' => 'Esaurito',
        'on_backorder' => 'Su ordinazione',
        'low_stock' => 'Scorte basse',
    ],

    // Weight units
    'weight_units' => [
        'kg' => 'Chilogrammi',
        'g' => 'Grammi',
        'lb' => 'Libbre',
        'oz' => 'Once',
    ],

    // Product types
    'types' => [
        'simple' => 'Semplice',
        'variable' => 'Variabile',
        'grouped' => 'Raggruppato',
        'external' => 'Esterno',
        'digital' => 'Digitale',
    ],

    // Messages
    'messages' => [
        'created' => 'Prodotto creato con successo',
        'updated' => 'Prodotto aggiornato con successo',
        'deleted' => 'Prodotto eliminato con successo',
        'bulk_deleted' => 'Prodotti eliminati con successo',
        'published' => 'Prodotto pubblicato con successo',
        'unpublished' => 'Prodotto nascosto con successo',
        'duplicated' => 'Prodotto duplicato con successo',
        'restored' => 'Prodotto ripristinato con successo',
        'featured' => 'Prodotto messo in evidenza',
        'unfeatured' => 'Prodotto rimosso dall\'evidenza',
        'stock_updated' => 'Scorte aggiornate con successo',
        'price_updated' => 'Prezzo aggiornato con successo',
        'sku_exists' => 'Questo codice SKU esiste già',
        'slug_exists' => 'Questo slug esiste già',
        'bulk_activated' => ':count prodotti attivati con successo',
        'bulk_archived' => ':count prodotti archiviati con successo',
        'bulk_exported' => 'Esportazione di :count prodotti in corso',
    ],

    // Bulk actions
    'bulk_actions' => [
        'delete' => 'Elimina selezionati',
        'publish' => 'Pubblica selezionati',
        'unpublish' => 'Nascondi selezionati',
        'feature' => 'Metti in evidenza',
        'unfeature' => 'Rimuovi dall\'evidenza',
        'duplicate' => 'Duplica selezionati',
        'export' => 'Esporta selezionati',
        'update_category' => 'Aggiorna categoria',
        'update_brand' => 'Aggiorna marchio',
        'update_price' => 'Aggiorna prezzo',
        'update_stock' => 'Aggiorna scorte',
    ],

    // Filters
    'filters' => [
        'all_products' => 'Tutti i prodotti',
        'published' => 'Pubblicati',
        'draft' => 'Bozze',
        'archived' => 'Archiviati',
        'featured' => 'In evidenza',
        'out_of_stock' => 'Esauriti',
        'low_stock' => 'Scorte basse',
        'price_range' => 'Fascia di prezzo',
        'category' => 'Per categoria',
        'brand' => 'Per marchio',
        'vendor' => 'Per fornitore',
        'created_date' => 'Data di creazione',
        'updated_date' => 'Data di modifica',
        'has_images' => 'Con immagini',
        'no_images' => 'Senza immagini',
        'has_variants' => 'Con varianti',
        'no_variants' => 'Senza varianti',
        'digital' => 'Digitali',
        'physical' => 'Fisici',
    ],

    // Import/Export
    'import' => [
        'title' => 'Importa Prodotti',
        'description' => 'Carica un file CSV o Excel per importare prodotti',
        'download_template' => 'Scarica template',
        'mapping' => 'Mappatura campi',
        'preview' => 'Anteprima import',
        'errors' => 'Errori di importazione',
        'success' => ':count prodotti importati con successo',
    ],

    'export' => [
        'title' => 'Esporta Prodotti',
        'description' => 'Esporta prodotti in formato CSV o Excel',
        'fields_to_export' => 'Campi da esportare',
        'file_format' => 'Formato file',
        'export_all' => 'Esporta tutti',
        'export_selected' => 'Esporta selezionati',
        'export_filtered' => 'Esporta filtrati',
    ],

    // Variants
    'variants' => [
        'title' => 'Varianti Prodotto',
        'create' => 'Crea Variante',
        'edit' => 'Modifica Variante',
        'none' => 'Nessuna variante',
        'options' => 'Opzioni variante',
        'combinations' => 'Combinazioni',
        'generate' => 'Genera varianti',
        'bulk_edit' => 'Modifica in blocco',
        'price_difference' => 'Differenza prezzo',
        'weight_difference' => 'Differenza peso',
        'sku_pattern' => 'Pattern SKU',
        'auto_generate_sku' => 'Genera SKU automaticamente',
    ],

    // Reviews
    'reviews' => [
        'title' => 'Recensioni',
        'count' => 'Numero recensioni',
        'average_rating' => 'Valutazione media',
        'pending' => 'In attesa',
        'approved' => 'Approvate',
        'rejected' => 'Rifiutate',
        'stars' => 'stelle',
    ],
];
