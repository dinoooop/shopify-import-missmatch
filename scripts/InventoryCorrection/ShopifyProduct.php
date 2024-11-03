<?php

namespace scripts\InventoryCorrection;

use scripts\InventoryCorrection\Base\ShopifyProductBase;
use lib\shopify\Shopify;


class ShopifyProduct extends ShopifyProductBase
{

    private $shopifyObj;

    function __construct()
    {
        parent::__construct();
        $this->shopifyObj = new Shopify();
    }

    public function importFromGQL()
    {

        $this->resetTable();

        $query = $this->getProductsGQL();

        $variables = [
            "after" => null
        ];

        $page = 1;
        do {

            echo "shopify products page:: {$page} \n";

            $payload = json_encode([
                "query" => $query,
                "variables" => $variables
            ]);

            $response = $this->shopifyObj->shopifyClientObj->callByGraphQl($payload);

            $variants = $response->data->productVariants->nodes ?? [];


            foreach ($variants as $key => $variant) {
                $row = [];
                $row['variant_gid'] = $variant->id;
                $row['barcode'] = $variant->barcode;
                $row['sku'] = $variant->sku;

                $row['option1_name'] = $this->getOptionName($variant, 1);
                $row['option1_value'] = $this->getOptionValue($variant, 1);
                $row['option2_name'] = $this->getOptionName($variant, 2);
                $row['option2_value'] = $this->getOptionValue($variant, 2);
                $row['option3_name'] = $this->getOptionName($variant, 3);
                $row['option3_value'] = $this->getOptionValue($variant, 3);

                $row['product_gid'] = $variant->product->id;
                $row['handle'] = $variant->product->handle;

                $inventoryLevels = $variant->inventoryItem->inventoryLevels->nodes;
                foreach ($inventoryLevels as $key => $inventoryLevel) {
                    $row['s_location_id'] = $this->intShopifyLocationId($inventoryLevel->location->id);
                    $row['s_location_name'] = $inventoryLevel->location->name;
                    $row['on_hand'] = isset($inventoryLevel->quantities[0]) ? $inventoryLevel->quantities[0]->quantity : null;
                    $this->insert($row);
                }
            }

            $hasNextPage = $response->data->productVariants->pageInfo->hasNextPage ?? false;

            if ($hasNextPage) {
                $variables['after'] = $response->data->productVariants->pageInfo->endCursor;
            }

            $page++;
        } while ($hasNextPage);
    }

}
