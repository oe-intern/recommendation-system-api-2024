<?php

namespace App\Storage\Commands;

use App\Collections\Product as ProductCollection;
use App\Collections\Schema\InteractionProduct as InteractionProductSchema;
use App\Collections\Shop as ShopCollection;
use App\Contracts\Commands\InteractionProduct as InteractionProductCommand;
use App\Lib\Utils;

class InteractionProduct implements InteractionProductCommand
{
    /**
     * Increment the number of clicks on a product from a shop.
     *
     * @param ShopCollection $shop
     * @param string $product_id
     * @param int $quantity
     * @return void
     */
    public function incrementClicks(ShopCollection $shop, string $product_id, int $quantity = 1): void
    {
        $this->incrementInteraction($shop, $product_id, 'quantityClicks', $quantity);
    }

    /**
     * Increase the number of times a product is added to cart.
     *
     * @param ShopCollection $shop
     * @param string $product_id
     * @param int $quantity
     * @return void
     */
    public function incrementAddToCart(ShopCollection $shop, string $product_id, int $quantity = 1): void
    {
        $this->incrementInteraction($shop, $product_id, 'quantityAddToCart', $quantity);
    }

    /**
     * Increment interaction (clicks or add to cart) for a product.
     *
     * @param ShopCollection $shop
     * @param string $product_id
     * @param string $interactionType
     * @param int $quantity
     * @return void
     */
    private function incrementInteraction(
        ShopCollection $shop,
        string $product_id,
        string $interactionType,
        int $quantity
    ): void {
        $product = $this->getProduct($shop, $product_id);

        if ($product) {
            $interaction = $this->findOrCreateInteraction($product, $interactionType, $quantity);
            $product->interactions()->save($interaction);
        }
    }

    /**
     * Retrieve the product from the shop.
     *
     * @param ShopCollection $shop
     * @param string $product_id
     * @return ProductCollection|null
     */
    private function getProduct(ShopCollection $shop, string $product_id): ?ProductCollection
    {
        return $shop->products()->where('id', $product_id)->first();
    }

    /**
     * Find the existing interaction for today, or create a new one if none exists.
     *
     * @param ProductCollection $product
     * @param string $interactionType
     * @param int $quantity
     * @return InteractionProductSchema
     */
    private function findOrCreateInteraction(
        ProductCollection $product,
        string $interactionType,
        int $quantity
    ): InteractionProductSchema {
        $interaction = $product->interactions()->where('date', Utils::getToday())->first();

        if (!$interaction) {
            $interaction = new InteractionProductSchema([
                'date' => Utils::getToday(),
                $interactionType => $quantity,
            ]);
        } else {
            $interaction->{$interactionType} += $quantity;
        }

        return $interaction;
    }
}
