<?php

namespace App\Services\Shopify\Graphql;

use App\Contracts\Shopify\Graphql\ShopLocale;

class ShopLocaleService extends BaseGraphqlService implements ShopLocale
{
    /**
     * @inheritdoc
     */
    public function primary()
    {
        $query = <<<'GRAPHQL'
query ShopLocales($published: Boolean = false) {
  shopLocales(published: $published) {
    locale
    name
    primary
    published
  }
}
GRAPHQL;

        $response = $this->graphql([
            'query' => $query,
            'variables' => [
                'published' => true,
            ],
        ]);

        $primaryLocale = collect(data_get($response, 'data.shopLocales'))
            ->where('primary', true)
            ->first();

        return data_get($primaryLocale, 'locale');
    }
}
