<?php

declare(strict_types=1);

namespace App\Collections;

use App\Objects\Enums\JobRecommendationStatus;
use MongoDB\Laravel\Relations\BelongsTo;

class JobRecommendationCollection extends MongoCollection
{
    /**
     * The name of the collection.
     *
     * @var string
     */
    protected $table = 'job_recommendations';

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = [
        'shop_id',
        'status',
        'retry_count',
        'result',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var string[]
     */
    protected $casts = [
        'status' => JobRecommendationStatus::class,
        'retry_count' => 'int',
    ];

    /**
     * The default values for the attributes.
     *
     * @var array[]
     */
    protected $attributes = [
        'status' => JobRecommendationStatus::PENDING,
        'retry_count' => 0,
        'result' => [],
    ];

    /**
     * Define the relationship with the shop.
     *
     * @return BelongsTo
     */
    public function products(): BelongsTo
    {
        return $this->belongsTo(ShopCollection::class, 'shop_id');
    }

    /**
     * Get the job ID.
     *
     * @return string
     */
    public function getId(): string
    {
        return $this->getAttribute('_id');
    }

    /**
     * Get the retry count.
     *
     * @return int
     */
    public function getRetryCount(): int
    {
        return $this->getAttribute('retry_count');
    }

    /**
     * Get the result.
     *
     * @return array
     */
    public function getResult(): array
    {
        return $this->getAttribute('result');
    }

    /**
     * Check if job succeeded.
     */
    public function isSucceeded(): bool
    {
        return $this->getStatus() == JobRecommendationStatus::SUCCESS;
    }

    /**
     * Get the status of the job.
     *
     * @return string
     */
    public function getStatus(): string
    {
        return $this->getAttribute('status');
    }
}
