<?php

namespace App\DTO\Request;

use Illuminate\Foundation\Http\FormRequest;

class AddToCartEventRequestDTO extends BaseRequestDTO
{
    /**
     * AddToCartEventRequestDTO constructor.
     *
     * @param int $numberOfItems
     * @param string $productId
     * @param mixed|null $data
     */
    public function __construct(
        public int $numberOfItems,
        public string $productId,
        public mixed $data,
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
            $data['number_of_items'] ?? 1,
            $data['product_id'],
            $data['data'] ?? null,
        );
    }
}
