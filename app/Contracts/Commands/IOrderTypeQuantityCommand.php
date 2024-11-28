<?php

namespace App\Contracts\Commands;

use App\Collections\ShopCollection;
use Illuminate\Database\Eloquent\ModelNotFoundException;

interface IOrderTypeQuantityCommand
{
	/**
	 * Increment the number of product types from a shop.
	 *
	 * @param ShopCollection $shop
	 * @param string $order_type
	 * @param int $quantity
	 * @return void
	 *
	 * @throws ModelNotFoundException
	 */
	public function increment(ShopCollection $shop, string $order_type, int $quantity = 1): void;
}
