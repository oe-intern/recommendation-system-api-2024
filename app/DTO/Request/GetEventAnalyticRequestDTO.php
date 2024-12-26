<?php

namespace App\DTO\Request;

use App\Objects\Enums\AnalyticGroupBy;
use Illuminate\Foundation\Http\FormRequest;

class GetEventAnalyticRequestDTO extends BaseRequestDTO
{
    /**
     * EventAnalyticRequestDTO constructor.
     *
     * @param AnalyticGroupBy $groupBy
     * @param string|null $productId
     * @param string $startDate
     * @param string $endDate
     */
    public function __construct(
        public AnalyticGroupBy $groupBy,
        public ?string $productId,
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
            isset($data['group_by']) ? AnalyticGroupBy::from($data['group_by']) : AnalyticGroupBy::DAY,
            $data['product_id'] ?? null,
            $data['start_date'],
            $data['end_date'],
        );
    }
}
