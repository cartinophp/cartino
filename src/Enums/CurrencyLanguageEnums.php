<?php

declare(strict_types=1);

namespace Cartino\Enums;

use Cartino\Traits\TranslatableEnum;

enum CurrencyType: string
{
    use TranslatableEnum;

    case FIAT = 'fiat';
    case CRYPTO = 'crypto';
    case COMMODITY = 'commodity';
    case DIGITAL = 'digital';
}

enum RegulatoryStatus: string
{
    use TranslatableEnum;

    case APPROVED = 'approved';
    case RESTRICTED = 'restricted';
    case BANNED = 'banned';
    case UNREGULATED = 'unregulated';
}

enum CurrencyStatus: string
{
    use TranslatableEnum;

    case ACTIVE = 'active';
    case DEPRECATED = 'deprecated';
    case RESTRICTED = 'restricted';
    case DELISTED = 'delisted';

    public function isUsable(): bool
    {
        return $this === self::ACTIVE;
    }
}

enum ExchangeRateSource: string
{
    use TranslatableEnum;

    case ECB = 'ecb';
    case YAHOO = 'yahoo';
    case COINBASE = 'coinbase';
    case BINANCE = 'binance';
    case FIXER = 'fixer';
    case OPENEXCHANGE = 'openexchange';
    case AI_PREDICTION = 'ai_prediction';

    public function isReliable(): bool
    {
        return in_array($this, [self::ECB, self::COINBASE, self::BINANCE]);
    }
}

enum PriceTrend: string
{
    use TranslatableEnum;

    case STRONG_UP = 'strong_up';
    case UP = 'up';
    case STABLE = 'stable';
    case DOWN = 'down';
    case STRONG_DOWN = 'strong_down';

    public function direction(): int
    {
        return match ($this) {
            self::STRONG_UP => 2,
            self::UP => 1,
            self::STABLE => 0,
            self::DOWN => -1,
            self::STRONG_DOWN => -2,
        };
    }
}

enum PricingStrategy: string
{
    use TranslatableEnum;

    case AUTO_CONVERT = 'auto_convert';
    case MANUAL_OVERRIDE = 'manual_override';
    case REGIONAL_PRICING = 'regional_pricing';
}

enum TextDirection: string
{
    use TranslatableEnum;

    case LTR = 'ltr';
    case RTL = 'rtl';
}

enum LanguageComplexity: string
{
    use TranslatableEnum;

    case SIMPLE = 'simple';
    case MODERATE = 'moderate';
    case COMPLEX = 'complex';

    public function translationMultiplier(): float
    {
        return match ($this) {
            self::SIMPLE => 1.0,
            self::MODERATE => 1.5,
            self::COMPLEX => 2.0,
        };
    }
}

enum BusinessPriority: string
{
    use TranslatableEnum;

    case HIGH = 'high';
    case MEDIUM = 'medium';
    case LOW = 'low';

    public function weight(): int
    {
        return match ($this) {
            self::HIGH => 3,
            self::MEDIUM => 2,
            self::LOW => 1,
        };
    }
}

enum LanguageStatus: string
{
    use TranslatableEnum;

    case ACTIVE = 'active';
    case BETA = 'beta';
    case DEPRECATED = 'deprecated';
    case UNSUPPORTED = 'unsupported';

    public function isUsable(): bool
    {
        return in_array($this, [self::ACTIVE, self::BETA]);
    }
}

enum TranslationType: string
{
    use TranslatableEnum;

    case STRING = 'string';
    case PLURAL = 'plural';
    case RICH_TEXT = 'rich_text';
    case MARKDOWN = 'markdown';
    case HTML = 'html';
}

enum QualityGrade: string
{
    use TranslatableEnum;

    case A = 'A';
    case B = 'B';
    case C = 'C';
    case D = 'D';
    case F = 'F';

    public function score(): int
    {
        return match ($this) {
            self::A => 90,
            self::B => 80,
            self::C => 70,
            self::D => 60,
            self::F => 50,
        };
    }
}

enum PricingMethod: string
{
    use TranslatableEnum;

    case AUTO_CONVERT = 'auto_convert';
    case MANUAL = 'manual';
    case RULE_BASED = 'rule_based';
    case COMPETITIVE = 'competitive';
}

enum PricePosition: string
{
    use TranslatableEnum;

    case BELOW_MARKET = 'below_market';
    case AT_MARKET = 'at_market';
    case ABOVE_MARKET = 'above_market';
}
