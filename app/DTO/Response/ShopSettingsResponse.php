<?php

namespace App\DTO\Response;

use Illuminate\Contracts\Support\Arrayable;

readonly class ShopSettingsResponse implements Arrayable
{
    /**
     * ShopSettingsResponse constructor.
     *
     * @param array $settings
     */
    public function __construct(
        private array $settings,
    ) {}

    /**
     * Convert data to array.
     *
     * @return array
     */
    public function toArray(): array
    {
        return $this->settings;
    }
}
