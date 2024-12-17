<?php

namespace App\Collections\Schema;

use App\Collections\MongoCollection;

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
        'last_job_recommendation_id',
        'refresh_count',
        'expires_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var string[]
     */
    protected $casts = [
        'expires_at' => 'datetime',
        'refresh_count' => 'integer',
    ];

    /**
     * The default values for the attributes.
     *
     * @var int[]
     */
    protected $attributes = [
        'refresh_count' => 5,
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
        return $this->getAttribute('last_job_recommendation_id');
    }
}
