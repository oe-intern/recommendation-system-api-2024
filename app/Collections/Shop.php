<?php

declare(strict_types=1);

namespace App\Collections;

use App\Collections\Schema\OrderTypeQuantity;
use MongoDB\Laravel\Eloquent\SoftDeletes;
use MongoDB\Laravel\Relations\EmbedsMany;
use MongoDB\Laravel\Relations\HasMany;

class Shop extends MongoCollection
{
    use SoftDeletes;

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
        return $this->hasMany(Order::class);
    }

    /**
     * Define the relationship with the products.
     *
     * @return HasMany
     */
    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    /**
     * Define the relationship with the type orders.
     *
     * @return EmbedsMany
     */
    public function orderTypeQuantities(): EmbedsMany
    {
        return $this->embedsMany(OrderTypeQuantity::class);
    }
}
