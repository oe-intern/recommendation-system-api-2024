<?php

namespace App\Objects\Enums;

enum InteractionType: string
{
    // User click on a product recommendation.
    case CLICK = 'click';
    // User add a product to cart.
    case ADD_TO_CART = 'add_to_cart';
}
