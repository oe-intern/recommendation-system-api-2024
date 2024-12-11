<?php

declare(strict_types=1);

namespace App\Collections;

use App\Objects\Enums\RecommendationType;
use MongoDB\Laravel\Relations\HasMany;
use MongoDB\Laravel\Relations\BelongsTo;

class ProductCollection extends MongoCollection
{
    /**
     * The name of the collection.
     *
     * @var string
     */
    protected $table = 'products';

    /**
     * Disable the timestamps.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'gid',
        'status',
        'type',
        'handle',
        'recommendation_type',
        'manual_ids',
        'referenced_ids',
        'default_recommendation_ids',
        'recommendation_ids',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var string[]
     */
    protected $casts = [
        'recommendation_type' => RecommendationType::class,
    ];

    /**
     * The default values for the attributes.
     *
     * @var array[]
     */
    protected $attributes = [
        'recommendation_type' => RecommendationType::DEFAULT,
        'manual_ids' => [],
        'referenced_ids' => [],
        'default_recommendation_ids' => [],
        'recommendation_ids' => [],
    ];

    /**
     * Define the relationship with the events.
     *
     * @return HasMany
     */
    public function events(): HasMany
    {
        return $this->hasMany(EventCollection::class, 'product_id');
    }

    /**
     * Define the relationship with the shop.
     *
     * @return BelongsTo
     */
    public function shop(): BelongsTo
    {
        return $this->belongsTo(ShopCollection::class);
    }

    /**
     * Get id of ProductCollection
     *
     * @return string
     */
    public function getId(): string
    {
        return $this->getAttribute('_id');
    }

    /**
     * Get gid of ProductCollection
     *
     * @return string
     */
    public function getGid(): string
    {
        return $this->getAttribute('gid');
    }

    /**
     * Get recommendation type of ProductCollection
     *
     * @return RecommendationType
     */
    public function getRecommendationType(): RecommendationType
    {
        return $this->getAttribute('recommendation_type');
    }

    /**
     * Get manual ids of ProductCollection
     *
     * @return array
     */
    public function getManualIds(): array
    {
        return $this->getAttribute('manual_ids');
    }

    /**
     * Get default recommendation ids of ProductCollection
     *
     * @return array
     */
    public function getDefaultRecommendationIds(): array
    {
        return $this->getAttribute('default_recommendation_ids');
    }

}
