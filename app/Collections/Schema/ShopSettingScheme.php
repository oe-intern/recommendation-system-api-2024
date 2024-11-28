<?php

namespace App\Collections\Schema;

use App\Collections\MongoCollection;

class ShopSettingScheme extends MongoCollection
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
        'layoutId',
        'backgroundColor',
        'textColor',
        'numberOfProducts',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var string[]
     */
    protected $casts = [
        'numberOfProducts' => 'int',
    ];
}
