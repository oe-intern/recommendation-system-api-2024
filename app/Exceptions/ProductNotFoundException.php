<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Http\Response;
use Throwable;

class ProductNotFoundException extends Exception
{
    /**
     * @var mixed
     */
    protected mixed $product_id;

    /**
     * ProductNotFoundException constructor.
     *
     * @param mixed $product_id
     * @param Throwable|null $previous
     */
    public function __construct(mixed $product_id, ?Throwable $previous = null)
    {
        $this->product_id = $product_id;
        $this->message = 'Product ID: ' . (is_array($product_id) ? implode(', ', $product_id) : $product_id)
            . ' not found.';

        parent::__construct($this->message);
    }

    /**
     * Render the exception as an HTTP response.
     *
     * @return Response
     */
    public function render(): Response
    {
        return response()->error($this->getMessage(), [], 404);
    }
}
