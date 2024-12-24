<?php

namespace App\Collections\Schema;

use App\Collections\MongoCollection;
use Carbon\Carbon;

class ShopRecommendationSchema extends MongoCollection
{
    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = [
        'last_recommendation_job_id',
        'refresh_count',
        'expires_at',
        'email',
        'email_notification',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var string[]
     */
    protected $casts = [
        'expires_at' => 'datetime',
        'refresh_count' => 'integer',
        'email_notification' => 'boolean',
    ];

    /**
     * The default values for the attributes.
     *
     * @var int[]
     */
    protected $attributes = [
        'refresh_count' => 5,
        'email_notification' => true,
        'email' => '',
        'last_recommendation_job_id' => '',
    ];

    /**
     * Get the number of times the recommendation can be refreshed
     *
     * @return int
     */
    public function getRefreshCount(): int
    {
        return $this->getAttribute('refresh_count');
    }

    /**
     * Get the last job recommendation ID
     *
     * @return string
     */
    public function getLastJobRecommendationId(): string
    {
        return $this->getAttribute('last_recommendation_job_id');
    }

    /**
     * Get the expiration date for the recommendation
     *
     * @return Carbon
     */
    public function getExpiresAt(): Carbon
    {
        return $this->getAttribute('expires_at');
    }

    /**
     * Get the email address for the recommendation
     *
     * @return string
     */
    public function getEmail(): string
    {
        return $this->getAttribute('email');
    }

    /**
     * Get the email notification status for the recommendation
     *
     * @return bool
     */
    public function getEmailNotification(): bool
    {
        return $this->getAttribute('email_notification');
    }
}
