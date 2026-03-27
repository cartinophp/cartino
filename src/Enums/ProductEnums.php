<?php

declare(strict_types=1);

namespace Cartino\Enums;

use Cartino\Traits\TranslatableEnum;

enum PricingRuleType: string
{
    use TranslatableEnum;

    case PERCENTAGE = 'percentage';
    case FIXED = 'fixed';
    case BULK = 'bulk';
    case TIERED = 'tiered';
    case BOGO = 'bogo';
    case CONDITIONAL = 'conditional';
}

enum ReturnStatus: string
{
    use TranslatableEnum;

    case REQUESTED = 'requested';
    case APPROVED = 'approved';
    case REJECTED = 'rejected';
    case IN_TRANSIT = 'in_transit';
    case RECEIVED = 'received';
    case PROCESSED = 'processed';
    case REFUNDED = 'refunded';

    public function isFinal(): bool
    {
        return in_array($this, [self::REJECTED, self::REFUNDED]);
    }
}

enum ReturnReason: string
{
    use TranslatableEnum;

    case DEFECTIVE = 'defective';
    case WRONG_ITEM = 'wrong_item';
    case NOT_AS_DESCRIBED = 'not_as_described';
    case CHANGED_MIND = 'changed_mind';
    case DAMAGED = 'damaged';
    case OTHER = 'other';

    public function isVendorFault(): bool
    {
        return in_array($this, [self::DEFECTIVE, self::WRONG_ITEM, self::NOT_AS_DESCRIBED, self::DAMAGED]);
    }
}

enum ProductCondition: string
{
    use TranslatableEnum;

    case NEW = 'new';
    case OPENED = 'opened';
    case DAMAGED = 'damaged';
    case DEFECTIVE = 'defective';

    public function refundPercentage(): int
    {
        return match ($this) {
            self::NEW => 100,
            self::OPENED => 90,
            self::DAMAGED => 50,
            self::DEFECTIVE => 0,
        };
    }
}

enum ShippingCalculationType: string
{
    use TranslatableEnum;

    case FLAT_RATE = 'flat_rate';
    case WEIGHT_BASED = 'weight_based';
    case PRICE_BASED = 'price_based';
    case ITEM_COUNT = 'item_count';
    case CALCULATED = 'calculated';
}

enum CustomerSegmentType: string
{
    use TranslatableEnum;

    case STATIC = 'static';
    case DYNAMIC = 'dynamic';
    case SMART = 'smart';
}

enum ReviewMediaType: string
{
    use TranslatableEnum;

    case IMAGE = 'image';
    case VIDEO = 'video';
}

enum CampaignType: string
{
    use TranslatableEnum;

    case SALE = 'sale';
    case FLASH_SALE = 'flash_sale';
    case CLEARANCE = 'clearance';
    case SEASONAL = 'seasonal';
    case NEW_PRODUCT = 'new_product';
    case ABANDONED_CART = 'abandoned_cart';
}

enum CampaignStatus: string
{
    use TranslatableEnum;

    case DRAFT = 'draft';
    case SCHEDULED = 'scheduled';
    case ACTIVE = 'active';
    case PAUSED = 'paused';
    case COMPLETED = 'completed';
    case CANCELLED = 'cancelled';

    public function isActive(): bool
    {
        return $this === self::ACTIVE;
    }
}

enum GiftCardTransactionType: string
{
    use TranslatableEnum;

    case PURCHASE = 'purchase';
    case REDEEM = 'redeem';
    case REFUND = 'refund';
    case EXPIRE = 'expire';
    case TRANSFER = 'transfer';

    public function affectsBalance(): bool
    {
        return in_array($this, [self::PURCHASE, self::REDEEM, self::REFUND]);
    }
}

// Enums per existing tables
enum CartStatus: string
{
    use TranslatableEnum;

    case ACTIVE = 'active';
    case ABANDONED = 'abandoned';
    case CONVERTED = 'converted';
    case EXPIRED = 'expired';
}

enum TaxType: string
{
    use TranslatableEnum;

    case PERCENTAGE = 'percentage';
    case FIXED = 'fixed';
}

enum EntryStatus: string
{
    use TranslatableEnum;

    case DRAFT = 'draft';
    case PUBLISHED = 'published';
    case ARCHIVED = 'archived';

    public function isPublic(): bool
    {
        return $this === self::PUBLISHED;
    }
}

enum Gender: string
{
    use TranslatableEnum;

    case MALE = 'male';
    case FEMALE = 'female';
    case OTHER = 'other';
}

enum ProductStatus: string
{
    use TranslatableEnum;

    case ACTIVE = 'active';
    case DRAFT = 'draft';
    case ARCHIVED = 'archived';

    public function isPublic(): bool
    {
        return $this === self::ACTIVE;
    }
}

enum CustomerStatus: string
{
    use TranslatableEnum;

    case ACTIVE = 'active';
    case INACTIVE = 'inactive';
    case PENDING = 'pending';
    case SUSPENDED = 'suspended';

    public function isActive(): bool
    {
        return $this === self::ACTIVE;
    }
}
