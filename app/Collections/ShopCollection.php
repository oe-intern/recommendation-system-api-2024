<?php

declare(strict_types=1);

namespace App\Collections;

use App\Collections\Schema\OrderTypeQuantitySchema;
use MongoDB\Laravel\Eloquent\SoftDeletes;
use MongoDB\Laravel\Relations\EmbedsMany;
use App\Collections\Schema\InteractionProductSchema;
use App\Collections\Schema\ShopSettingScheme;
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
     * @var string[]
     */
    protected $fillable = [
        'domain',
        'orderTypeQuantities',
    ];

    /**
     * The primary key.
     *
     * @var string
     */
    protected $primaryKey = 'domain';

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
        'orderTypeQuantities' => 'array',
    ];

    /**
     * Define the relationship with the orders.
     *
     * @return HasMany
     */
    public function orders(): HasMany
    {
        return $this->hasMany(OrderCollection::class);
    }

    /**
     * Define the relationship with the products.
     *
     * @return HasMany
     */
    public function products(): HasMany
    {
        return $this->hasMany(ProductCollection::class);
    }

    /**
     * Define the relationship with the type orders.
     *
     * @return EmbedsMany
     */
    public function orderTypeQuantities(): EmbedsMany
    {
        return $this->embedsMany(OrderTypeQuantitySchema::class);
    }

    /**
     * Define settings for visualization recommendation products.
     *
     * @return EmbedsOne
     */
    public function settings(): EmbedsOne
    {
        return $this->embedsOne(ShopSettingScheme::class);
    }

    /**
     * Define the relationship with the interactions for all products.
     *
     * @return EmbedsMany
     */
    public function interactions(): EmbedsMany
    {
        return $this->embedsMany(InteractionProductSchema::class);
    }
}
