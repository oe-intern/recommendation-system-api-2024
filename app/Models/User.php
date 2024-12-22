<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Contracts\Objects\Values\AccessToken as IAccessToken;
use App\Objects\Values\AccessToken;
use App\Objects\Values\UserDomain;
use App\Objects\Values\UserId;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'password_updated_at',
        'shop_id',
        'shop_name',
        'shop_email',
        'domain',
        'province',
        'country',
        'address1',
        'zip',
        'city',
        'phone',
        'latitude',
        'longitude',
        'primary_locale',
        'address2',
        'shop_created_at',
        'shop_updated_at',
        'country_code',
        'country_name',
        'currency',
        'customer_email',
        'timezone',
        'iana_timezone',
        'shop_owner',
        'money_format',
        'money_with_currency_format',
        'weight_unit',
        'province_code',
        'taxes_included',
        'tax_shipping',
        'plan_display_name',
        'plan_name',
        'myshopify_domain',
        'money_in_emails_format',
        'money_with_currency_in_emails_format',
        'password_enabled',
        'has_storefront',
        'cookie_consent_level',
        'visitor_tracking_consent_preference',
        'checkout_api_supported',
        'setup_required',
        'enabled_presentment_currencies',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'taxes_included' => 'boolean',
        'password_updated_at' => 'datetime',
        'tax_shipping' => 'boolean',
        'password_enabled' => 'boolean',
        'has_storefront' => 'boolean',
        'checkout_api_supported' => 'boolean',
        'setup_required' => 'boolean',
        'enabled_presentment_currencies' => 'array',
        'plan_name' => 'string',
        'deleted_at' => 'datetime',
    ];

    /**
     * Get the shop ID.
     *
     * @return UserId
     */
    public function getId(): UserId
    {
        return UserId::fromNative($this->id);
    }

    /**
     * Get the access token.
     */
    public function getAccessToken(): IAccessToken
    {
        return AccessToken::fromNative($this->password);
    }

    /**
     * Get the myshopify domain.
     *
     * @return UserDomain
     */
    public function getMyShopifyDomain(): UserDomain
    {
        return UserDomain::fromNative($this->myshopify_domain);
    }

    /**
     * Get the email.
     *
     * @return string
     */
    public function getShopEmail(): string
    {
        return $this->shop_email;
    }
}
