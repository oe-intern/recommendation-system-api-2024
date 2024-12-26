<?php

namespace App\DTO\Request;

use Illuminate\Foundation\Http\FormRequest;

class GetProductPerformanceRequestDTO extends BaseRequestDTO
{
    /**
     * ProductPerformanceRequestDTO constructor.
     *
     * @param string $startDate
     * @param string $endDate
     */
    public function __construct(
        public string $startDate,
        public string $endDate,
    ) {}

    /**
     * Convert data from request to DTO.
     *
     * @param FormRequest $data
     * @return self
     */
    public static function fromRequest(FormRequest $data): self
    {
        return new self(
            $data['start_date'],
            $data['end_date'],
        );
    }
}
