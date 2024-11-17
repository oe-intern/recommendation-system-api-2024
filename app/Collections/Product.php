<?php

declare(strict_types=1);

namespace App\Collections;

class Product extends MongoCollection
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        '_id',
        'title',
        'handle',
        'categoryId',
        'vendor',
        'variantIds',
        'totalInventory',
        'tags',
        'status',
        'productType',
        'optionIds',
        'description',
    ];

    /**
     * The primary key.
     *
     * @var string
     */
    protected $primaryKey = '_id';

    /**
     * The "type" of the primary key ID.
     *
     * @var string
     */
    protected $keyType = 'string';

    /**
     * The default values for the attributes.
     *
     * @var array[]
     */
    protected $attributes = [
        'variantIds' => [],
        'tags' => [],
        'optionIds' => [],
    ];
}
