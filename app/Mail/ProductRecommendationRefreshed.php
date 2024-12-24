<?php

namespace App\Mail;

use App\Objects\Enums\JobRecommendationStatus;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ProductRecommendationRefreshed extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * @var string
     */
    protected string $shopDomain;

    /**
     * @var JobRecommendationStatus
     */
    protected JobRecommendationStatus $status;

    /**
     * @var string
     */
    protected string $email;

    /**
     * Create a new message instance.
     */
    public function __construct(string $shopDomain, JobRecommendationStatus $status, string $email)
    {
        $this->shopDomain = $shopDomain;
        $this->status = $status;
        $this->email = $email;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Shop product recommendation refreshed',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'mails.product_recommendation_refreshed',
            with: [
                'shop_domain' => $this->shopDomain,
                'status' => $this->status,
                'email' => $this->email,
            ],
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
