<?php

declare(strict_types=1);

namespace Cartino\Enums;

use Cartino\Traits\TranslatableEnum;

enum TenantPlan: string
{
    use TranslatableEnum;

    case STARTER = 'starter';
    case PRO = 'pro';
    case ENTERPRISE = 'enterprise';
    case CUSTOM = 'custom';

    public function monthlyPrice(): float
    {
        return match ($this) {
            self::STARTER => 29.99,
            self::PRO => 99.99,
            self::ENTERPRISE => 299.99,
            self::CUSTOM => 0, // Custom pricing
        };
    }
}

enum TenantStatus: string
{
    use TranslatableEnum;

    case ACTIVE = 'active';
    case SUSPENDED = 'suspended';
    case CANCELLED = 'cancelled';
    case TRIAL = 'trial';

    public function isActive(): bool
    {
        return $this === self::ACTIVE || $this === self::TRIAL;
    }
}

enum SslStatus: string
{
    use TranslatableEnum;

    case PENDING = 'pending';
    case ACTIVE = 'active';
    case FAILED = 'failed';
    case EXPIRED = 'expired';
}

enum ShardStatus: string
{
    use TranslatableEnum;

    case ACTIVE = 'active';
    case READONLY = 'readonly';
    case MAINTENANCE = 'maintenance';
    case DEGRADED = 'degraded';
    case OFFLINE = 'offline';

    public function isOperational(): bool
    {
        return in_array($this, [self::ACTIVE, self::READONLY]);
    }
}

enum BackupStatus: string
{
    use TranslatableEnum;

    case SUCCESS = 'success';
    case FAILED = 'failed';
    case IN_PROGRESS = 'in_progress';
}

enum MigrationStatus: string
{
    use TranslatableEnum;

    case ACTIVE = 'active';
    case MIGRATING = 'migrating';
    case ROLLBACK = 'rollback';
    case FAILED = 'failed';
}

enum MetricType: string
{
    use TranslatableEnum;

    case ORDERS = 'orders';
    case PRODUCTS = 'products';
    case CUSTOMERS = 'customers';
    case STORAGE = 'storage';
    case BANDWIDTH = 'bandwidth';
    case API_CALLS = 'api_calls';
    case EMAILS_SENT = 'emails_sent';
    case SMS_SENT = 'sms_sent';
    case TRANSACTIONS = 'transactions';
}

enum PeriodType: string
{
    use TranslatableEnum;

    case HOUR = 'hour';
    case DAY = 'day';
    case WEEK = 'week';
    case MONTH = 'month';
    case QUARTER = 'quarter';
    case YEAR = 'year';
}

enum HealthStatus: string
{
    use TranslatableEnum;

    case HEALTHY = 'healthy';
    case WARNING = 'warning';
    case CRITICAL = 'critical';

    public function color(): string
    {
        return match ($this) {
            self::HEALTHY => 'green',
            self::WARNING => 'yellow',
            self::CRITICAL => 'red',
        };
    }
}

enum RolloutStage: string
{
    use TranslatableEnum;

    case DEV = 'dev';
    case BETA = 'beta';
    case STABLE = 'stable';
    case DEPRECATED = 'deprecated';
}
