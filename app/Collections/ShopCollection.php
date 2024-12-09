<?php

declare(strict_types=1);

namespace App\Collections;

use MongoDB\Laravel\Eloquent\SoftDeletes;
use App\Collections\Schema\ShopSettingSchema;
use MongoDB\Laravel\Relations\EmbedsOne;
use MongoDB\Laravel\Relations\HasMany;

class ShopCollection extends MongoCollection
{
    use SoftDeletes;

    /**
     * The name of the collection.
     *
     * @var string
     */
    protected $table = 'shops';

    /**
     * Disable the timestamps.
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
        'domain',
    ];

    /**
     * Define the relationship with the products.
     *
     * @return HasMany
     */
    public function products(): HasMany
    {
        return $this->hasMany(ProductCollection::class, 'shop_id');
    }

    /**
     * Define the relationship with the events.
     *
     * @return HasMany
     */
    public function events(): HasMany
    {
        return $this->hasMany(EventCollection::class, 'shop_id');
    }

    /**
     * Define settings for visualization recommendation products.
     *
     * @return EmbedsOne
     */
    public function settings(): EmbedsOne
    {
        return $this->embedsOne(ShopSettingSchema::class);
    }

    /**
     * Get the ID of the shop.
     *
     * @return string
     */
    public function getId(): string
    {
        return $this->getAttribute('_id');
    }
}
