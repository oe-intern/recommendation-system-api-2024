<?php

namespace App\Services\Shopify\Graphql;

use App\Contracts\Shopify\Graphql\Shop as IShop;
use Shopify\Exception\MissingArgumentException;

class ShopService extends BaseGraphqlService implements IShop
{
    /**
     * @inheritdoc
     * @throws MissingArgumentException
     */
    public function detail()
    {
        $query = <<<'GRAPHQL'
            query GetShop {
              shop {
                id
                name
                email
                myshopifyDomain
                setupRequired
                taxesIncluded
                taxShipping
                timezoneAbbreviation
                transactionalSmsDisabled
                marketingSmsConsentEnabledAtCheckout
                updatedAt
                weightUnit
                checkoutApiSupported
                shopOwnerName
                plan {
                  displayName
                  partnerDevelopment
                  shopifyPlus
                }
                billingAddress {
                  address1
                  address2
                  phone
                  city
                  province
                  provinceCode
                  country
                  company
                  latitude
                  countryCodeV2
                  longitude
                  zip
                }
                createdAt
                contactEmail
                currencyCode
                primaryDomain {
                  host
                  id
                  sslEnabled
                  url
                }
                enabledPresentmentCurrencies
                currencyFormats {
                  moneyFormat
                  moneyInEmailsFormat
                  moneyWithCurrencyFormat
                  moneyWithCurrencyInEmailsFormat
                }
              }
            }
            GRAPHQL;

        $response = $this->graphql($query);

        return data_get($response, 'data.shop', []);
    }
}
