<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Http\Response;
use Throwable;

class RecommendationRefreshLimitException extends Exception
{
    /**
     * ProductNotFoundException constructor.
     *
     * @param Throwable|null $previous
     */
    public function __construct(?Throwable $previous = null)
    {
        $this->message = 'Recommendation refresh limit reached. Please try again later.';

        parent::__construct($this->message);
    }

    /**
     * Render the exception as an HTTP response.
     *
     * @return Response
     */
    public function render(): Response
    {
        return response()->error($this->getMessage(), [], 403);
    }
}
