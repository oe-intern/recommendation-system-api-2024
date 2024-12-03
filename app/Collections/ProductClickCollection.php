<?php

declare(strict_types=1);

namespace App\Collections;

class ProductClickCollection extends MongoCollection
{
    /**
     * The name of the collection.
     *
     * @var string
     */
    protected $table = 'product_clicks';

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
        'product_id',
        'shop_id',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var string[]
     */
    protected $casts = [
        'created_at' => 'datetime',
    ];
}
