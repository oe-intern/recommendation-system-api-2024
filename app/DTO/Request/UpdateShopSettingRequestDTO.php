<?php

namespace App\DTO\Request;

use Illuminate\Foundation\Http\FormRequest;

class UpdateShopSettingRequestDTO extends BaseRequestDTO
{
    /**
     * UpdateShopSettingRequestDTO constructor.
     *
     * @param int $numberOfItems
     * @param string $layout
     * @param string $backgroundColor
     * @param string $textColor
     */
    public function __construct(
        public int $numberOfItems,
        public string $layout,
        public string $backgroundColor,
        public string $textColor,
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
            $data['number_of_items'],
            $data['layout'],
            $data['background_color'],
            $data['text_color'],
        );
    }
}
