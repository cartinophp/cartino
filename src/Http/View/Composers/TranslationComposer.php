<?php

declare(strict_types=1);

namespace Cartino\Http\View\Composers;

use Illuminate\View\View;

class TranslationComposer
{
    /**
     * Bind data to the view.
     */
    public function compose(View $view): void
    {
        $view->with('translations', $this->getTranslations());
    }

    /**
     * Get all translations for the current locale.
     * Statamic-style: loads from Laravel translator and formats for JS.
     */
    protected function getTranslations(): array
    {
        $locale = app()->getLocale();

        // Define translation namespaces to load
        // These correspond to resources/lang/{locale}/*.php files
        $namespaces = [
            'cp',           // Control Panel strings
            'validation',   // Validation messages
            'messages',     // General messages
        ];

        $translations = [];

        foreach ($namespaces as $namespace) {
            $key = "cartino::{$namespace}";
            $translation = __($key);

            // Only include if it's actually a translation array (not the key itself)
            if (is_array($translation)) {
                // Store with dot notation for JS: cartino.cp, cartino.validation
                $translations[$namespace] = $translation;
            }
        }

        // Also load Laravel's default translations
        $laravelKeys = ['auth', 'pagination', 'passwords', 'validation'];
        foreach ($laravelKeys as $key) {
            $translation = __($key);
            if (is_array($translation)) {
                $translations[$key] = $translation;
            }
        }

        return $translations;
    }
}
