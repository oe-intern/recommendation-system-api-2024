<?php

namespace App\Collections;

use MongoDB\Laravel\Eloquent\Model;

abstract class MongoCollection extends Model
{
    /**
     * The connection name for the model.
     *
     * @var string
     */
    protected $connection = 'mongodb';
}
