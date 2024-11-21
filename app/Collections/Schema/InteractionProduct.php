<?php

namespace App\Collections\Schema;

use App\Collections\MongoCollection;

class InteractionProduct extends MongoCollection
{
    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = [
        'date',
        'quantityClicks',
        'quantityAddToCart',
    ];

    /**
     * The primary key.
     *
     * @var string
     */
    protected $primaryKey = 'date';

    /**
     * The type of the primary key ID.
     *
     * @var string
     */
    protected $keyType = 'string';

    /**
     * The default values for the attributes.
     *
     * @var int[]
     */
    protected $attributes = [
        'quantityClicks' => 0,
        'quantityAddToCart' => 0,
    ];

    /**
     * The attributes that should be cast.
     *
     * @var string[]
     */
    protected $casts = [
        'quantityClicks' => 'int',
        'quantityAddToCart' => 'int',
    ];
}
