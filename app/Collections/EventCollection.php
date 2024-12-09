<?php


declare(strict_types=1);

namespace App\Collections;

use App\Objects\Enums\EventType;

class EventCollection extends MongoCollection
{
    /**
     * The name of the collection.
     *
     * @var string
     */
    protected $table = 'events';

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
        'type',
        'created_at',
        'quantity',
        'product_id',
        'shop_id',
        'data',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var string[]
     */
    protected $casts = [
        'type' => EventType::class,
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
