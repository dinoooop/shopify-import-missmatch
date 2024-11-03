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

        $this->createTable(true);

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
                    $row['location_gid'] = $inventoryLevel->location->id;
                    $row['location'] = $inventoryLevel->location->name;
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

    public function importFromCSV()
    {
        try {

            $this->createTable(true);

            $file = "./resources/inventory-correction/shopify_products.csv";
            $handle = fopen($file, 'r');

            $header = array_map('trim', fgetcsv($handle));
            while (($data = fgetcsv($handle)) !== false) {
                $row = [];

                $row['variant_gid'] = null;
                $row['barcode'] = $data[array_search("Variant Barcode", $header)];
                $row['sku'] = $data[array_search("sku", $header)];
                $row['option1_name'] = $data[array_search("option1_name", $header)];
                $row['option1_value'] = $data[array_search("option1_value", $header)];
                $row['option2_name'] = $data[array_search("option2_name", $header)];
                $row['option2_value'] = $data[array_search("option2_value", $header)];
                $row['option3_name'] = $data[array_search("option3_name", $header)];
                $row['option3_value'] = $data[array_search("option3_value", $header)];
                $row['product_gid'] = null;
                $row['handle'] = $data[array_search("handle", $header)];
                $row['location_gid'] = null;
                $row['location'] = $data[array_search("Location", $header)];
                $row['on_hand'] = $data[array_search("On Hand", $header)];

                if (!empty(trim($row['barcode']))) {
                    $this->insert($row);
                }
            }

            fclose($handle);
        } catch (\Throwable $th) {
            throw $th;
        }
    }
}
