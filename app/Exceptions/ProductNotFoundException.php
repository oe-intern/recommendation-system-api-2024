<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Http\JsonResponse;
use Throwable;

class ProductNotFoundException extends Exception
{
    /**
     * @var string
     */
    protected string $shop_domain;

    /**
     * @var mixed
     */
    protected mixed $product_id;

    /**
     * ProductNotFoundException constructor.
     *
     * @param string $shop_domain
     * @param mixed $product_id
     * @param Throwable|null $previous
     */
    public function __construct(string $shop_domain, mixed $product_id, ?Throwable $previous = null)
    {
        $this->shop_domain = $shop_domain;
        $this->product_id = $product_id;
        $this->message = 'Product ID: ' . (is_array($product_id) ? implode(', ', $product_id) : $product_id)
            . ' not found for shop_domain: ' . $shop_domain;

        parent::__construct($this->message);
    }

    /**
     * Render the exception as an HTTP response.
     *
     * @return JsonResponse
     */
    public function render(): JsonResponse
    {
        return response()->json([
            'message' => $this->getMessage(),
        ], 404);
    }
}
