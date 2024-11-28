<?php

declare(strict_types=1);

namespace App\Collections;

use App\Collections\Schema\InteractionProductSchema;
use App\Collections\Schema\RelationshipScoreSchema;
use App\Objects\Enums\RecommendationType;
use MongoDB\Laravel\Relations\EmbedsMany;
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
     * Indicates if the IDs are auto-incrementing.
     *
     * @var bool
     */
    public $incrementing = false;

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
        'id',
        'title',
        'handle',
        'categoryId',
        'vendor',
        'variantIds',
        'totalInventory',
        'tags',
        'status',
        'productType',
        'description',
        'recommendationType',
        'interactions',
        'relationshipScore',
        'referencedIds',
        'manualIds',
        'recommendationIds',
    ];
    /**
     * The primary key.
     *
     * @var string
     */
    protected $primaryKey = 'id';
    /**
     * The "type" of the primary key ID.
     *
     * @var string
     */
    protected $keyType = 'string';

    /**
     * The attributes that should be cast.
     *
     * @var string[]
     */
    protected $casts = [
        'recommendationType' => RecommendationType::class,
    ];

    /**
     * The default values for the attributes.
     *
     * @var array[]
     */
    protected $attributes = [
        'variantIds' => [],
        'tags' => [],
        'manualIds' => [],
        'recommendationType' => RecommendationType::DEFAULT,
        'interactions' => [],
        'relationshipScore' => [],
        'referencedIds' => [],
        'recommendationIds' => [],
    ];

    /**
     * Define the relationship with the interactions.
     *
     * @return EmbedsMany
     */
    public function interactions(): EmbedsMany
    {
        return $this->embedsMany(InteractionProductSchema::class);
    }

    /**
     * Define the relationship with the relationship score.
     *
     * @return EmbedsMany
     */
    public function relationshipScore(): EmbedsMany
    {
        return $this->embedsMany(RelationshipScoreSchema::class);
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

    public function getType(): RecommendationType
    {
        return $this->recommendationType;
    }
}
