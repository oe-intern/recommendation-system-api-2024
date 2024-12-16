<?php

namespace App\Collections\Schema;

use App\Collections\MongoCollection;
use App\Objects\Enums\RecommendationState;

class ShopSettingSchema extends MongoCollection
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
        'layout',
        'background_color',
        'text_color',
        'number_of_items',
        'auto_recommendation',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var string[]
     */
    protected $casts = [
        'number_of_items' => 'int',
        'auto_recommendation' => RecommendationState::class,
    ];

    /**
     * The default values for the attributes.
     *
     * @var int[]
     */
    protected $attributes = [
        'number_of_items' => 4,
        'background_color' => '',
        'text_color' => '',
        'layout' => '',
        'auto_recommendation' => RecommendationState::INACTIVE,
    ];

    /**
     * Get number of items.
     *
     * @return int
     */
    public function getNumberOfItems(): int
    {
        return $this->getAttribute('number_of_items');
    }
}
