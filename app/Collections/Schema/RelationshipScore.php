<?php

namespace App\Collections\Schema;

use App\Collections\MongoCollection;

class RelationshipScore extends MongoCollection
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
        'productId',
        'score',
    ];

    /**
     * The primary key.
     *
     * @var string
     */
    protected $primaryKey = 'productId';

    /**
     * The type of the primary key ID.
     *
     * @var string
     */
    protected $keyType = 'string';

    /**
     * The default values for the attributes.
     *
     * @var float[]
     */
    protected $attributes = [
        'score' => 0.0,
    ];

    /**
     * The attributes that should be cast.
     *
     * @var string[]
     */
    protected $casts = [
        'score' => 'float',
    ];
}
