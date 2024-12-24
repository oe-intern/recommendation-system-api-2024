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
    protected mixed $productId;

    /**
     * ProductNotFoundException constructor.
     *
     * @param mixed $productId
     * @param Throwable|null $previous
     */
    public function __construct(mixed $productId, ?Throwable $previous = null)
    {
        $this->productId = $productId;
        $this->message = 'Product ID: ' . (is_array($productId) ? implode(', ', $productId) : $productId)
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
