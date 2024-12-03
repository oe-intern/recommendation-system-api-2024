<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Http\Response;
use Throwable;

class MissingProductIdException extends Exception
{
    /**
     * MissingProductIdException constructor.
     *
     * @param Throwable|null $previous
     */
    public function __construct(?Throwable $previous = null)
    {
        $this->message = 'Product ID is missing.';

        parent::__construct($this->message);
    }

    /**
     * Render the exception as an HTTP response.
     *
     * @return Response
     */
    public function render(): Response
    {
        return response()->error($this->getMessage(), [], 400);
    }
}
