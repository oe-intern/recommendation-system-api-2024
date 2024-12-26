<?php

namespace App\DTO\Request;

use Illuminate\Foundation\Http\FormRequest;

class UpdateNotificationSettingsRequestDTO extends BaseRequestDTO
{
    /**
     * UpdateNotificationSettingsRequestDTO constructor.
     *
     * @param bool $emailNotification
     * @param string|null $email
     */
    public function __construct(
        public bool $emailNotification,
        public ?string $email,
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
            $data['email_notification'],
            $data['email'],
        );
    }
}
