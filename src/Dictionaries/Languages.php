<?php

declare(strict_types=1);

namespace Cartino\Dictionaries;

class Languages extends BasicDictionary
{
    protected string $valueKey = 'code';

    protected array $keywords = ['languages', 'language', 'locale', 'translation'];

    protected array $searchable = ['code', 'name', 'native'];

    protected function getItemLabel(array $item): string
    {
        return "{$item['name']} - {$item['native']}";
    }

    protected function getItems(): array
    {
        return [
            ['code' => 'it', 'name' => 'Italian', 'native' => 'Italiano', 'rtl' => false],
            ['code' => 'en', 'name' => 'English', 'native' => 'English', 'rtl' => false],
            ['code' => 'de', 'name' => 'German', 'native' => 'Deutsch', 'rtl' => false],
            ['code' => 'fr', 'name' => 'French', 'native' => 'Français', 'rtl' => false],
            ['code' => 'es', 'name' => 'Spanish', 'native' => 'Español', 'rtl' => false],
            ['code' => 'pt', 'name' => 'Portuguese', 'native' => 'Português', 'rtl' => false],
            ['code' => 'nl', 'name' => 'Dutch', 'native' => 'Nederlands', 'rtl' => false],
            ['code' => 'pl', 'name' => 'Polish', 'native' => 'Polski', 'rtl' => false],
            ['code' => 'ru', 'name' => 'Russian', 'native' => 'Русский', 'rtl' => false],
            ['code' => 'ar', 'name' => 'Arabic', 'native' => 'العربية', 'rtl' => true],
            ['code' => 'he', 'name' => 'Hebrew', 'native' => 'עברית', 'rtl' => true],
            ['code' => 'zh', 'name' => 'Chinese', 'native' => '中文', 'rtl' => false],
            ['code' => 'ja', 'name' => 'Japanese', 'native' => '日本語', 'rtl' => false],
            ['code' => 'ko', 'name' => 'Korean', 'native' => '한국어', 'rtl' => false],
            ['code' => 'hi', 'name' => 'Hindi', 'native' => 'हिन्दी', 'rtl' => false],
        ];
    }
}
