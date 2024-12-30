<?php

namespace App\DTO\Response;

use Illuminate\Contracts\Support\Arrayable;

readonly class GetProductPerformanceResponse implements Arrayable
{
    /**
     * GetProductPerformanceResponse constructor.
     *
     * @param array $topPerformance
     * @param array $lowPerformance
     */
    public function __construct(
        private array $topPerformance,
        private array $lowPerformance,
    ) {}

    /**
     * Convert data to array.
     *
     * @return array
     */
    public function toArray(): array
    {
        return [
            'top' => $this->topPerformance,
            'low' => $this->lowPerformance,
        ];
    }
}
