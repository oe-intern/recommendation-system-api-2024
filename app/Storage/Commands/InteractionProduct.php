<?php

namespace App\Storage\Commands;

use App\Collections\Product as ProductCollection;
use App\Collections\Schema\InteractionProduct as InteractionProductSchema;
use App\Contracts\Commands\InteractionProduct as InteractionProductCommand;
use App\Lib\Utils;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class InteractionProduct implements InteractionProductCommand
{
    /**
     * Increment the number of clicks on a product from a shop.
     *
     * @param string $shop_domain
     * @param string $product_id
     * @param int $quantity
     * @return void
     *
     * @throws ModelNotFoundException
     */
    public function incrementClicks(string $shop_domain, string $product_id, int $quantity = 1): void
    {
        $this->incrementInteraction($shop_domain, $product_id, 'quantityClicks', $quantity);
    }

    /**
     * Increase the number of times a product is added to cart.
     *
     * @param string $shop_domain
     * @param string $product_id
     * @param int $quantity
     * @return void
     */
    public function incrementAddToCart(string $shop_domain, string $product_id, int $quantity = 1): void
    {
        $this->incrementInteraction($shop_domain, $product_id, 'quantityAddToCart', $quantity);
    }

    /**
     * Increment interaction (clicks or add to cart) for a product.
     *
     * @param string $shop_domain
     * @param string $product_id
     * @param string $interactionType
     * @param int $quantity
     * @return void
     *
     * @throws ModelNotFoundException
     */
    private function incrementInteraction(
        string $shop_domain,
        string $product_id,
        string $interactionType,
        int $quantity
    ): void {
        $product = $this->getProduct($shop_domain, $product_id);

        $interaction = $this->findOrCreateInteraction($product, $interactionType, $quantity);
        $product->interactions()->save($interaction);
    }

    /**
     * Retrieve the product from the shop.
     *
     * @param string $shop_domain
     * @param string $product_id
     * @return ProductCollection
     *
     * @throws ModelNotFoundException
     */
    private function getProduct(string $shop_domain, string $product_id): ProductCollection
    {
        return ProductCollection::query()->where('id', $product_id)->where('shop_domain', $shop_domain)->firstOrFail();
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
