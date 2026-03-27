<?php

declare(strict_types=1);

namespace Cartino\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ApiKeyResource extends JsonResource
{
    private ?string $plainKey = null;

    /**
     * Set the plain (unhashed) key to include in response
     */
    public function withPlainKey(string $key): self
    {
        $this->plainKey = $key;

        return $this;
    }

    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'type' => $this->type,
            'permissions' => $this->when($this->type === 'custom', $this->permissions),
            'last_used_at' => $this->last_used_at?->toIso8601String(),
            'expires_at' => $this->expires_at?->toIso8601String(),
            'is_active' => $this->is_active,
            'is_expired' => $this->expires_at?->isPast() ?? false,
            'created_by' => $this->whenLoaded('creator', fn () => [
                'id' => $this->creator->id,
                'name' => $this->creator->name,
                'email' => $this->creator->email,
            ]),
            'created_at' => $this->created_at->toIso8601String(),
            'updated_at' => $this->updated_at->toIso8601String(),
            // Include la chiave in chiaro solo alla creazione
            'key' => $this->when($this->plainKey !== null, $this->plainKey),
        ];
    }
}
