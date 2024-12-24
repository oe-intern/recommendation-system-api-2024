<?php

namespace App\Http\Middleware;

use App\Lib\Utils;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class VerifyHmac
{
    /**
     * Handle an incoming request.
     *
     * @param Request $request
     * @param \Closure(Request): (Response) $next
     * @return Response
     */
    public function handle(Request $request, Closure $next): Response
    {
        $input = $request->all();
        $hmac = Arr::get($input, 'hmac');

        if (!$hmac) {
            throw new BadRequestHttpException('HMAC is not correct');
        }

        unset($input['hmac']);
        $secret = Utils::getShopifyConfig('api_secret');

        $messageSegments = [];

        foreach ($input as $key => $value) {
            $messageSegments[] = "{$key}={$value}";
        }

        $message = implode('&', $messageSegments);
        $generatedHmac = hash_hmac('sha256', $message, $secret);

        if ($generatedHmac !== $hmac) {
            throw new BadRequestHttpException('HMAC is not correct');
        }

        return $next($request);
    }
}
