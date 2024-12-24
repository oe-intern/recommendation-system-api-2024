<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Support\Arr;

class ShopifyGraphqlException extends Exception
{
    /**
     * @var
     */
    protected $errors;

    /**
     * ShopifyGraphqlException constructor.
     *
     * @param $errors
     */
    public function __construct($errors)
    {
        parent::__construct(self::summarize($errors));

        foreach ($errors as $error) {
            $code = Arr::get($error, 'extensions.code', 'OTHER');
            $this->errors[$code][] = Arr::get($error, 'message', 'Unknown error');
        }
    }

    /**
     * Summarize the errors.
     *
     * @param $errors
     * @return string
     */
    protected static function summarize($errors)
    {
        $firstError = Arr::first($errors);
        $message = Arr::get($firstError, 'message', 'Unknown error');
        $totalErrors = count($errors);

        if ($totalErrors > 1) {
            $totalErrorsLeft = $totalErrors - 1;
            $pluralized = $totalErrorsLeft === 1 ? 'error' : 'errors';
            $message .= " (and $totalErrorsLeft more $pluralized)";
        }

        return "Shopify GraphQL Error: {$message}";
    }

    /**
     * Render the exception as an HTTP response.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function render()
    {
        return response()->json([
            'message' => $this->getMessage(),
            'errors' => $this->errors,
        ], 500);
    }
}
