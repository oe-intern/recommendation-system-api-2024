<?php

declare(strict_types=1);

namespace App\Collections;

use App\Collections\Schema\InteractionProduct;
use App\Collections\Schema\RelationshipScore;
use App\Objects\Enums\RecommendationType;
use MongoDB\Laravel\Relations\EmbedsMany;

class Product extends MongoCollection
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        '_id',
        'title',
        'handle',
        'categoryId',
        'vendor',
        'variantIds',
        'totalInventory',
        'tags',
        'status',
        'productType',
        'optionIds',
        'description',
        'recommendationType',
        'interactions',
        'relationshipScore',
    ];

    /**
     * The primary key.
     *
     * @var string
     */
    protected $primaryKey = '_id';

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
        'optionIds' => [],
        'recommendationType' => RecommendationType::DEFAULT,
        'interactions' => [],
        'relationshipScore' => [],
    ];

    /**
     * Define the relationship with the interactions.
     *
     * @return EmbedsMany
     */
    public function interactions(): EmbedsMany
    {
        return $this->embedsMany(InteractionProduct::class);
    }

    /**
     * Define the relationship with the relationship score.
     *
     * @return EmbedsMany
     */
    public function relationshipScore(): EmbedsMany
    {
        return $this->embedsMany(RelationshipScore::class);
    }
}
