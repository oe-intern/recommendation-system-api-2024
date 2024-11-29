<?php

namespace App\Jobs;

use App\Contracts\Recommendation\IProductInteraction;
use App\Exceptions\ProductNotFoundException;
use App\Lib\Utils;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProcessInteractionEvent implements ShouldQueue
{
	use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

	/**
	 * @var string
	 */
	protected string $shop_domain;

	/**
	 * @var array
	 */
	protected array $data;

	/**
	 * @param string $shop_domain
	 * @param array $data
	 */
	public function __construct(string $shop_domain, array $data)
	{
		$this->shop_domain = $shop_domain;
		$this->data = $data;
	}

	/**
	 * Execute the job
	 *
	 * @param IProductInteraction $product_interaction_service
	 * @return void
	 *
	 * @throws ProductNotFoundException
	 */
	public function handle(IProductInteraction $product_interaction_service): void
	{
		$product_id = Utils::getIdFromGid(data_get($this->data, 'product_id'));
		$number_of_interactions = data_get($this->data, 'number_of_interactions');
		$interaction_type = data_get($this->data, 'interaction_type');

		$product_interaction_service->increment(
			$this->shop_domain,
			$product_id,
			$number_of_interactions,
			$interaction_type
		);
	}
}
