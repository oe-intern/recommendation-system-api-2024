<?php

declare(strict_types=1);

namespace App\Collections;

class ProductAddToCartCollection extends MongoCollection
{
    /**
     * The name of the collection.
     *
     * @var string
     */
    protected $table = 'product_add_to_cart';

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
        'created_at',
        'quantity',
        'product_id',
        'shop_id',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var string[]
     */
    protected $casts = [
        'quantity' => 'int',
        'created_at' => 'datetime',
    ];

    /**
     * The default values for the attributes.
     *
     * @var int[]
     */
    protected $attributes = [
        'quantity' => 1,
    ];
}
