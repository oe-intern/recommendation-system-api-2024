<?php

declare(strict_types=1);

namespace App\Collections;

class Order extends MongoCollection
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        '_id',
        'email',
        'amount',
        'currencyCodeMoney',
        'createdAt',
        'items',
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
}
