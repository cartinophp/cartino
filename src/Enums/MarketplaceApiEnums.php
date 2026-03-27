<?php

declare(strict_types=1);

namespace Cartino\Enums;

use Cartino\Traits\TranslatableEnum;

enum BusinessType: string
{
    use TranslatableEnum;

    case INDIVIDUAL = 'individual';
    case SOLE_PROPRIETORSHIP = 'sole_proprietorship';
    case PARTNERSHIP = 'partnership';
    case CORPORATION = 'corporation';
    case LLC = 'llc';
    case COOPERATIVE = 'cooperative';
}

enum BusinessCategory: string
{
    use TranslatableEnum;

    case MANUFACTURER = 'manufacturer';
    case WHOLESALER = 'wholesaler';
    case RETAILER = 'retailer';
    case DROPSHIPPER = 'dropshipper';
    case SERVICE_PROVIDER = 'service_provider';
    case DIGITAL_GOODS = 'digital_goods';
}

enum VerificationStatus: string
{
    use TranslatableEnum;

    case UNVERIFIED = 'unverified';
    case PENDING = 'pending';
    case VERIFIED = 'verified';
    case REJECTED = 'rejected';
    case SUSPENDED = 'suspended';

    public function canOperate(): bool
    {
        return $this === self::VERIFIED;
    }
}

enum KycStatus: string
{
    use TranslatableEnum;

    case NOT_REQUIRED = 'not_required';
    case PENDING = 'pending';
    case VERIFIED = 'verified';
    case REJECTED = 'rejected';
}

enum CommissionType: string
{
    use TranslatableEnum;

    case PERCENTAGE = 'percentage';
    case FIXED = 'fixed';
    case TIERED = 'tiered';
    case CATEGORY_BASED = 'category_based';
}

enum PayoutSchedule: string
{
    use TranslatableEnum;

    case DAILY = 'daily';
    case WEEKLY = 'weekly';
    case MONTHLY = 'monthly';
    case ON_DEMAND = 'on_demand';

    public function daysBetweenPayouts(): int
    {
        return match ($this) {
            self::DAILY => 1,
            self::WEEKLY => 7,
            self::MONTHLY => 30,
            self::ON_DEMAND => 0,
        };
    }
}

enum VendorStatus: string
{
    use TranslatableEnum;

    case ACTIVE = 'active';
    case INACTIVE = 'inactive';
    case SUSPENDED = 'suspended';
    case BANNED = 'banned';
    case PENDING_APPROVAL = 'pending_approval';

    public function canSell(): bool
    {
        return $this === self::ACTIVE;
    }
}

enum ProductApprovalStatus: string
{
    use TranslatableEnum;

    case DRAFT = 'draft';
    case PENDING = 'pending';
    case APPROVED = 'approved';
    case REJECTED = 'rejected';
    case NEEDS_REVISION = 'needs_revision';

    public function isPublic(): bool
    {
        return $this === self::APPROVED;
    }
}

enum CommissionStatus: string
{
    use TranslatableEnum;

    case PENDING = 'pending';
    case APPROVED = 'approved';
    case PAID = 'paid';
    case DISPUTED = 'disputed';
    case REFUNDED = 'refunded';
    case CANCELLED = 'cancelled';

    public function isFinal(): bool
    {
        return in_array($this, [self::PAID, self::REFUNDED, self::CANCELLED]);
    }
}

enum PayoutMethod: string
{
    use TranslatableEnum;

    case BANK_TRANSFER = 'bank_transfer';
    case PAYPAL = 'paypal';
    case STRIPE = 'stripe';
    case WISE = 'wise';
    case CRYPTO = 'crypto';
    case CHECK = 'check';

    public function isInstant(): bool
    {
        return in_array($this, [self::PAYPAL, self::STRIPE, self::CRYPTO]);
    }
}

enum PayoutStatus: string
{
    use TranslatableEnum;

    case PENDING = 'pending';
    case PROCESSING = 'processing';
    case COMPLETED = 'completed';
    case FAILED = 'failed';
    case CANCELLED = 'cancelled';

    public function isFinal(): bool
    {
        return in_array($this, [self::COMPLETED, self::FAILED, self::CANCELLED]);
    }
}

enum ApiApplicationType: string
{
    use TranslatableEnum;

    case INTERNAL = 'internal';
    case PARTNER = 'partner';
    case PUBLIC = 'public';
    case WEBHOOK_ONLY = 'webhook_only';
}

enum ApiEnvironment: string
{
    use TranslatableEnum;

    case DEVELOPMENT = 'development';
    case STAGING = 'staging';
    case PRODUCTION = 'production';

    public function rateLimitMultiplier(): float
    {
        return match ($this) {
            self::DEVELOPMENT => 10.0,
            self::STAGING => 2.0,
            self::PRODUCTION => 1.0,
        };
    }
}

enum ApiStatus: string
{
    use TranslatableEnum;

    case ACTIVE = 'active';
    case INACTIVE = 'inactive';
    case SUSPENDED = 'suspended';
    case REVOKED = 'revoked';

    public function canMakeRequests(): bool
    {
        return $this === self::ACTIVE;
    }
}

enum WebhookFormat: string
{
    use TranslatableEnum;

    case JSON = 'json';
    case FORM = 'form';
    case XML = 'xml';

    public function contentType(): string
    {
        return match ($this) {
            self::JSON => 'application/json',
            self::FORM => 'application/x-www-form-urlencoded',
            self::XML => 'application/xml',
        };
    }
}

enum WebhookStatus: string
{
    use TranslatableEnum;

    case ACTIVE = 'active';
    case PAUSED = 'paused';
    case DISABLED = 'disabled';
    case FAILED = 'failed';

    public function canSend(): bool
    {
        return $this === self::ACTIVE;
    }
}

enum WebhookFailureReason: string
{
    use TranslatableEnum;

    case TIMEOUT = 'timeout';
    case CONNECTION_ERROR = 'connection_error';
    case SSL_ERROR = 'ssl_error';
    case INVALID_RESPONSE = 'invalid_response';
    case RATE_LIMITED = 'rate_limited';
    case AUTHENTICATION_FAILED = 'authentication_failed';
    case SERVER_ERROR = 'server_error';

    public function isRetryable(): bool
    {
        return in_array($this, [
            self::TIMEOUT,
            self::CONNECTION_ERROR,
            self::RATE_LIMITED,
            self::SERVER_ERROR,
        ]);
    }
}
