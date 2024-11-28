<?php

namespace App\Storage\Commands;

use App\Collections\ProductCollection;
use App\Collections\ShopCollection;
use App\Contracts\Queries\IProductQuery;
use App\Collections\Schema\InteractionProductSchema;
use App\Contracts\Commands\IInteractionProductCommand;
use App\Lib\Utils;
use App\Objects\Enums\InteractionType;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class InteractionProductCommand implements IInteractionProductCommand
{
    /**
     * @var IProductQuery
     */
    protected IProductQuery $product_query;

    /**
     * InteractionProductCommand constructor.
     *
     * @param IProductQuery $product_query
     */
    public function __construct(IProductQuery $product_query)
    {
        $this->product_query = $product_query;
    }

	/**
	 * Increment any interaction of a product from a shop.
	 *
	 * @param string $shop_domain
	 * @param string $product_id
	 * @param InteractionType $interaction_type
	 * @param int $quantity
	 * @return void
	 */
	public function increment(
		string $shop_domain,
		string $product_id,
		InteractionType $interaction_type,
		int $quantity
	): void {
		match ($interaction_type) {
			InteractionType::CLICK => $this->incrementClicks($shop_domain, $product_id, $quantity),
			InteractionType::ADD_TO_CART => $this->incrementAddToCart($shop_domain, $product_id, $quantity),
		};
	}

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
		$product = $this->product_query->checkProductExist($shop_domain, $product_id);

		$interaction = $this->findOrCreateInteraction($product, $interactionType, $quantity);
		$product->interactions()->save($interaction);
        $this->findOrCreateInteractionForShop($shop_domain, $interactionType, $quantity);
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

        return $this->getInteraction($interaction, $interactionType, $quantity);
	}

    /**
     * Find the existing interaction for today, or create a new one if none exists.
     *
     * @param string $shop_domain
     * @param string $interactionType
     * @param int $quantity
     * @return void
     */
    private function findOrCreateInteractionForShop(
        string $shop_domain,
        string $interactionType,
        int $quantity
    ): void {
        $shop = ShopCollection::query()
            ->where('domain', $shop_domain)
            ->first();
        $interaction = $shop->interactions()->where('date', Utils::getToday())->first();

        $shop->interactions()->save($this->getInteraction($interaction, $interactionType, $quantity));
    }

    /**
     * Get or create interaction.
     *
     * @param InteractionProductSchema|null $interaction
     * @param string $interactionType
     * @param int $quantity
     * @return InteractionProductSchema
     */
    private function getInteraction(?InteractionProductSchema $interaction, string $interactionType, int $quantity): InteractionProductSchema
    {
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
}
