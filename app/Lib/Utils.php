<?php

namespace App\Lib;

use App\Objects\Enums\ShopifyType;
use App\Objects\Values\Hmac;
use App\Services\Shopify\UserContext;
use App\Storage\Queries\User;
use Carbon\Carbon;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Config;

final class Utils
{
    /**
     * Get id from gid
     *
     * @param string $gid
     * @return mixed|string
     */
    public static function getIdFromGid(string $gid)
    {
        $last_slash_pos = strrpos($gid, '/');

        if ($last_slash_pos === false) {
            return $gid;
        }

        return substr($gid, $last_slash_pos + 1);
    }

    /**
     * Add prefix Graph id
     *
     * @param string $id
     * @param ShopifyType $type
     * @return string
     */
    public static function addPrefixGraphId(string $id, ShopifyType $type): string
    {
        if (str_contains($id, 'gid')) {
            return $id;
        }

        return 'gid://shopify/' . $type->value . '/' . $id;
    }

    /**
     * Get the config value for a key.
     * Used as a helper function so it is accessible in Blade.
     * The second param of `shop` is important for `config_api_callback`.
     *
     * @param string $key  The key to lookup.
     * @param mixed  $shop The shop domain (string, ShopDomain, etc).
     *
     * @return mixed
     */
    public static function getShopifyConfig(string $key, $shop = null)
    {
        $config = Config::get('shopify-app', []);

        return Arr::get($config, $key);
    }

    /**
     * URL-safe Base64 encoding.
     *
     * Replaces `+` with `-` and `/` with `_` and trims padding `=`.
     *
     * @param string $data The data to be encoded.
     *
     * @return string
     */
    public static function base64UrlEncode($data)
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    /**
     * URL-safe Base64 decoding.
     *
     * Replaces `-` with `+` and `_` with `/`.
     *
     * Adds padding `=` if needed.
     *
     * @param string $data The data to be decoded.
     *
     * @return string
     */
    public static function base64UrlDecode($data)
    {
        return base64_decode(str_pad(strtr($data, '-_', '+/'), strlen($data) % 4, '=', STR_PAD_RIGHT));
    }

    /**
     * HMAC creation helper.
     *
     * @param array  $opts   The options for building the HMAC.
     * @param string $secret The app secret key.
     *
     * @return Hmac
     */
    public static function createHmac(array $opts, string $secret): Hmac
    {
        // Setup defaults
        $data = $opts['data'];
        $raw = $opts['raw'] ?? false;
        $buildQuery = $opts['buildQuery'] ?? false;
        $buildQueryWithJoin = $opts['buildQueryWithJoin'] ?? false;
        $encode = $opts['encode'] ?? false;

        if ($buildQuery) {
            //Query params must be sorted and compiled
            ksort($data);
            $queryCompiled = [];
            foreach ($data as $key => $value) {
                $queryCompiled[] = "{$key}=".(is_array($value) ? implode(',', $value) : $value);
            }
            $data = implode(
                $buildQueryWithJoin ? '&' : '',
                $queryCompiled
            );
        }

        // Create the hmac all based on the secret
        $hmac = hash_hmac('sha256', $data, $secret, $raw);

        // Return based on options
        $result = $encode ? base64_encode($hmac) : $hmac;

        return Hmac::fromNative($result);
    }

    /**
     * Get the user from the current session.
     */
    public static function getUserByContext()
    {
        $user_context = app(UserContext::class);
        $user_query = app(User::class);

        return $user_query->getByDomain($user_context->getDomain());
    }

    /**
     * Date time now.
     */
    public static function getToday(): Carbon
    {
        return Carbon::today();
    }
}
