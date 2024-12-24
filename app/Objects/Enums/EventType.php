<?php

namespace App\Objects\Enums;

enum EventType: string
{
    // User click on a product recommendation.
    case CLICK = 'CLICK';
    // User add a product to cart.
    case ADD_TO_CART = 'ADD_TO_CART';
}
