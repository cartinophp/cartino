<?php

namespace Cartino\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PaymentGateway extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'provider',
        'config',
        'is_enabled',
        'is_default',
        'supported_currencies',
        'webhook_url',
        'test_mode',
        'sort_order',
    ];

    protected $casts = [
        'config' => 'array',
        'supported_currencies' => 'array',
        'is_enabled' => 'boolean',
        'is_default' => 'boolean',
        'test_mode' => 'boolean',
    ];

    /**
     * Get orders that use this payment gateway
     */
    public function orders()
    {
        return $this->hasMany(Order::class, 'payment_gateway_id');
    }

    /**
     * Scope for enabled gateways
     */
    public function scopeEnabled($query)
    {
        return $query->where('is_enabled', true);
    }

    /**
     * Scope for default gateway
     */
    public function scopeDefault($query)
    {
        return $query->where('is_default', true);
    }

    /**
     * Get config value by key
     */
    public function getConfigValue(string $key, $default = null)
    {
        return data_get($this->config, $key, $default);
    }

    /**
     * Set config value by key
     */
    public function setConfigValue(string $key, $value): void
    {
        $config = $this->config ?? [];
        data_set($config, $key, $value);
        $this->config = $config;
    }

    /**
     * Check if gateway supports currency
     */
    public function supportsCurrency(string $currency): bool
    {
        if (empty($this->supported_currencies)) {
            return true; // If no currencies specified, support all
        }

        return in_array(strtoupper($currency), $this->supported_currencies);
    }
}
