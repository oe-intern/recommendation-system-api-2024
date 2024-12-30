<?php

namespace App\DTO\Response;

use Illuminate\Contracts\Support\Arrayable;

readonly class GetEventDataResponse implements Arrayable
{
    /**
     * GetEventDataResponse constructor.
     *
     * @param array $eventData
     */
    public function __construct(
        private array $eventData,
    ) {}


    /**
     * Convert data to array.
     *
     * @return array
     */
    public function toArray(): array
    {
        return $this->eventData;
    }
}
