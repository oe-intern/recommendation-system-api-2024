<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Http\Response;
use Throwable;

class ShopNotFoundException extends Exception
{
    /**
     * @var string
     */
    protected string $shopDomain;

    /**
     * ShopNotFoundException constructor.
     *
     * @param string $shopDomain
     * @param Throwable|null $previous
     */
    public function __construct(string $shopDomain, ?Throwable $previous = null)
    {
        $this->shopDomain = $shopDomain;
        $this->message = 'Shop domain: ' . $shopDomain . ' not found';

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
