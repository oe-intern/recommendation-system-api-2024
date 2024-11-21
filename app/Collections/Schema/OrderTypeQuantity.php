<?php

namespace App\Collections\Schema;

use App\Collections\MongoCollection;

class OrderTypeQuantity extends MongoCollection
{
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
        'quantity',
        'type',
    ];

    /**
     * The primary key.
     *
     * @var string
     */
    protected $primaryKey = 'type';

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
        'quantity' => 0,
    ];

    /**
     * The attributes that should be cast.
     *
     * @var string[]
     */
    protected $casts = [
        'quantity' => 'int',
    ];
}
