<?php

return [
    'title' => 'Categorie',
    'single' => 'Categoria',
    'create' => 'Nuova Categoria',
    'edit' => 'Modifica Categoria',
    'list' => 'Elenco Categorie',
    'search_placeholder' => 'Cerca categorie...',
    'no_categories' => 'Nessuna categoria trovata',

    // Fields
    'fields' => [
        'name' => 'Nome',
        'slug' => 'Slug',
        'description' => 'Descrizione',
        'parent' => 'Categoria padre',
        'image' => 'Immagine',
        'icon' => 'Icona',
        'sort_order' => 'Ordine',
        'is_visible' => 'Visibile',
        'seo_title' => 'Titolo SEO',
        'seo_description' => 'Descrizione SEO',
        'meta_keywords' => 'Parole chiave meta',
    ],

    // Messages
    'messages' => [
        'created' => 'Categoria creata con successo',
        'updated' => 'Categoria aggiornata con successo',
        'deleted' => 'Categoria eliminata con successo',
        'bulk_deleted' => 'Categorie eliminate con successo',
        'cannot_delete_parent' => 'Impossibile eliminare una categoria con sottocategorie',
        'reordered' => 'Categorie riordinate con successo',
    ],

    'tree' => [
        'root' => 'Radice',
        'expand_all' => 'Espandi tutto',
        'collapse_all' => 'Comprimi tutto',
        'move_up' => 'Sposta su',
        'move_down' => 'Sposta giù',
        'make_child' => 'Rendi figlio',
        'make_sibling' => 'Rendi fratello',
    ],
];
