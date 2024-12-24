<?php

namespace App\Objects\Values;

use App\Lib\Utils;
use Assert\Assert;
use Assert\AssertionFailedException;
use Funeralzone\ValueObjects\Scalars\StringTrait;
use Funeralzone\ValueObjects\ValueObject;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

/**
 * Value object for a session token (JWT).
 */
final class SessionToken implements ValueObject
{
    use StringTrait;

    /**
     * The regex for the format of the JWT.
     *
     * @var string
     */
    public const TOKEN_FORMAT = '/^eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9\.[A-Za-z0-9\-\_=]+\.[A-Za-z0-9\-\_\=]*$/';

    /**
     * Message for malformed token.
     *
     * @var string
     */
    public const EXCEPTION_MALFORMED = 'Session token is malformed.';

    /**
     * Message for invalid token.
     *
     * @var string
     */
    public const EXCEPTION_INVALID = 'Session token is invalid.';

    /**
     * Message for expired token.
     *
     * @var string
     */
    public const EXCEPTION_EXPIRED = 'Session token has expired.';

    /**
     * Time added to the expiration time, extends the validity period of a session token
     *
     * @var int
     */
    public const LEEWAY_SECONDS = 10;

    /**
     * Token parts.
     *
     * @var array
     */
    protected $parts;

    /**
     * Issuer.
     *
     * @var string
     */
    protected $iss;

    /**
     * Destination identity.
     *
     * @var string
     */
    protected $dest;

    /**
     * Audience.
     *
     * @var string
     */
    protected $aud;

    /**
     * Subject.
     *
     * @var string
     */
    protected $sub;

    /**
     * Expiration.
     *
     * @var Carbon
     */
    protected $exp;

    /**
     * Not before.
     *
     * @var Carbon
     */
    protected $nbf;

    /**
     * Issued at.
     *
     * @var Carbon
     */
    protected $iat;

    /**
     * JWT identity.
     *
     * @var string
     */
    protected $jti;

    /**
     * Session identity.
     *
     * @var SessionId
     */
    protected $sid;

    /**
     * The shop domain parsed from destination.
     *
     * @var UserDomain
     */
    protected $userDomain;

    /**
     * Constructor.
     *
     * @param string $token The JWT.
     * @param bool $verifyToken Should the token be verified? Use false to only decode the token.
     *
     * @throws AssertionFailedException
     */
    public function __construct(string $token, bool $verifyToken = true)
    {
        // Confirm token formatting and decode the token
        $this->string = $token;
        $this->decodeToken();

        if ($verifyToken) {
            // Confirm token signature, validity, and expiration
            $this->verifySignature();
            $this->verifyValidity();
            $this->verifyExpiration();
        }
    }

    /**
     * Decode and validate the formatting of the token.
     *
     * @throws AssertionFailedException If token is malformed.
     *
     * @return void
     */
    protected function decodeToken(): void
    {
        // Confirm token formatting
        Assert::that($this->string)->regex(self::TOKEN_FORMAT, self::EXCEPTION_MALFORMED);

        // Decode the token
        $this->parts = explode('.', $this->string);
        $body = json_decode(Utils::base64UrlDecode($this->parts[1]), true);

        // Confirm token is not malformed
        Assert::thatAll([
            $body['iss'],
            $body['dest'],
            $body['aud'],
            $body['sub'],
            $body['exp'],
            $body['nbf'],
            $body['iat'],
            $body['jti'],
            $body['sid'],
        ])->notNull(self::EXCEPTION_MALFORMED);

        // Format the values
        $this->iss = $body['iss'] ?? '';
        $this->dest = $body['dest'];
        $this->aud = $body['aud'];
        $this->sub = $body['sub'] ?? '';
        $this->jti = $body['jti'];
        $this->sid = SessionId::fromNative($body['sid'] ?? '');
        $this->exp = new Carbon($body['exp']);
        $this->nbf = new Carbon($body['nbf']);
        $this->iat = new Carbon($body['iat']);

        // Parse the shop domain from the destination
        $host = $this->findHost($body['dest']);
        $this->userDomain = UserDomain::fromNative($host);
    }

    /**
     * Get the shop domain.
     *
     * @return UserDomain
     */
    public function getShopDomain(): UserDomain
    {
        return $this->userDomain;
    }

    /**
     * Get the session ID.
     *
     * @return SessionId
     */
    public function getSessionId(): SessionId
    {
        return $this->sid;
    }

    /**
     * Get the expiration time of the token.
     *
     * @return Carbon
     */
    public function getExpiration(): Carbon
    {
        return $this->exp;
    }

    /**
     * Get the time before which the token must not be accepted for processing.
     *
     * @return \Illuminate\Support\Carbon
     */
    public function getNotBefore(): Carbon
    {
        return $this->nbf;
    }

    /**
     * Get the time at which the token was issued.
     *
     * @return \Illuminate\Support\Carbon
     */
    public function getIssuedAt(): Carbon
    {
        return $this->iat;
    }

    /**
     * Get the issuer of the token.
     *
     * @return string
     */
    public function getIssuer(): string
    {
        return $this->iss;
    }

    /**
     * Get the destination identity string of the token.
     *
     * @return string
     */
    public function getDestination(): string
    {
        return $this->dest;
    }

    /**
     * Get the audience of the token.
     *
     * @return string
     */
    public function getAudience(): string
    {
        return $this->aud;
    }

    /**
     * Get the subject of the token.
     *
     * @return string
     */
    public function getSubject(): string
    {
        return $this->sub;
    }

    /**
     * Get the JWT id of the token.
     *
     * @return string
     */
    public function getTokenId(): string
    {
        return $this->jti;
    }

    /**
     * Get the extended expiration time with leeway of the token.
     *
     * @return Carbon
     */
    public function getLeewayExpiration(): Carbon
    {
        return (new Carbon($this->exp))->addSeconds(self::LEEWAY_SECONDS);
    }

    /**
     * Get the extended not before time with leeway of the token.
     *
     * @return Carbon
     */
    public function getLeewayNotBefore(): Carbon
    {
        return (new Carbon($this->nbf))->subSeconds(self::LEEWAY_SECONDS);
    }

    /**
     * Get the extended issued at time with leeway of the token.
     *
     * @return Carbon
     */
    public function getLeewayIssuedAt(): Carbon
    {
        return (new Carbon($this->iat))->subSeconds(self::LEEWAY_SECONDS);
    }

    /**
     * Checks the validity of the signature sent with the token.
     *
     * @throws AssertionFailedException If signature does not match.
     *
     * @return void
     */
    protected function verifySignature(): void
    {
        // Get the token without the signature present
        $partsCopy = $this->parts;
        $signature = Hmac::fromNative(array_pop($partsCopy));
        $tokenWithoutSignature = implode('.', $partsCopy);

        // Create a local HMAC
        $secret = Utils::getShopifyConfig('api_secret');
        $hmac = Utils::createHmac(['data' => $tokenWithoutSignature, 'raw' => true], $secret);
        $encodedHmac = Hmac::fromNative(Utils::base64UrlEncode($hmac->toNative()));

        Assert::that($signature->isSame($encodedHmac))->true();
    }

    /**
     * Checks the token to ensure the issuer and audience matches.
     *
     * @return void
     */
    protected function verifyValidity(): void
    {
        Assert::that($this->iss)->contains($this->dest, self::EXCEPTION_INVALID);
        Assert::that($this->aud)->eq(Utils::getShopifyConfig('api_key'), self::EXCEPTION_INVALID);
    }

    /**
     * Checks the token to ensure its not expired.
     *
     * @throws AssertionFailedException If token is expired.
     *
     * @return void
     */
    protected function verifyExpiration(): void
    {
        $now = Carbon::now();
        Assert::thatAll([
            $now->greaterThan($this->getLeewayExpiration()),
            $now->lessThan($this->getLeewayNotBefore()),
            $now->lessThan($this->getLeewayIssuedAt()),
        ])->false(self::EXCEPTION_EXPIRED);
    }

    /**
     * Find the host from a destination.
     *
     * @param string $destination
     * @return string|null
     */
    protected function findHost(string $destination): ?string
    {
        return Str::startsWith($destination, 'https')
            ? parse_url($destination, PHP_URL_HOST)
            : $destination;
    }
}
