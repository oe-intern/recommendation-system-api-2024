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
    protected string $shop_domain;

    /**
     * ShopNotFoundException constructor.
     *
     * @param string $shop_domain
     * @param Throwable|null $previous
     */
    public function __construct(string $shop_domain, ?Throwable $previous = null)
    {
        $this->shop_domain = $shop_domain;
        $this->message = 'Shop domain: ' . $shop_domain . ' not found';

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
