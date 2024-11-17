<?php

declare(strict_types=1);

namespace App\Collections;

use MongoDB\Laravel\Eloquent\SoftDeletes;
use MongoDB\Laravel\Relations\EmbedsMany;

class Shop extends MongoCollection
{
    use SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = [
        'domain',
        'orders',
        'products',
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
        'orders' => 'array',
        'products' => 'array',
    ];

    /**
     * The default values for the attributes.
     *
     * @var array[]
     */
    protected $attributes = [
        'orders' => [],
        'products' => [],
    ];

    /**
     * Define the relationship with the orders.
     *
     * @return EmbedsMany
     */
    public function orders(): EmbedsMany
    {
        return $this->embedsMany(Order::class);
    }

    /**
     * Define the relationship with the products.
     *
     * @return EmbedsMany
     */
    public function products(): EmbedsMany
    {
        return $this->embedsMany(Product::class);
    }
}
