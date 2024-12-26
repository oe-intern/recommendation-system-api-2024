<?php

namespace App\DTO\Request;

use Illuminate\Foundation\Http\FormRequest;

abstract class BaseRequestDTO
{
    /**
     * Convert data from request to DTO.
     *
     * @param FormRequest $data
     * @return self
     */
    abstract public static function fromRequest(FormRequest $data): self;

    /**
     *
     * @return array
     */
    public function toArray(): array
    {
        return get_object_vars($this);
    }
}
