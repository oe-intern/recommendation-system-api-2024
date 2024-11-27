<?php

declare(strict_types=1);

namespace App\Collections;

use MongoDB\Laravel\Relations\BelongsTo;


class OrderCollection extends MongoCollection
{
    /**
     * The name of the collection.
     *
     * @var string
     */
    protected $table = 'orders';

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

    /**
     * Define the relationship with the shop.
     *
     * @return BelongsTo
     */
    public function shop(): BelongsTo
    {
        return $this->belongsTo(ShopCollection::class);
    }
}
