<?php

namespace App\DTO\Request;

use Illuminate\Foundation\Http\FormRequest;

class ClickEventRequestDTO extends BaseRequestDTO
{
    /**
     * ClickEventRequestDTO constructor.
     *
     * @param string $productId
     * @param mixed|null $data
     */
    public function __construct(
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
            $data['product_id'],
            $data['data'] ?? null,
        );
    }
}
