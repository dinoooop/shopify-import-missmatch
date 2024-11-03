<?php

namespace scripts\InventoryCorrection;

use lib\shopify\Shopify;

class ShopifyOrder extends ShopifyOrderBase
{

    private $shopifyObj;
    function __construct()
    {
        parent::__construct();
        $this->shopifyObj = new Shopify();

    }

    function init()
    {

        $this->resetTable();

        $query = $this->getFulfillmentOrdersGQL();
        $variables = [
            "after" => null
        ];

        
        $importDateReached = false;

        $page = 1;
        do {
            echo "shopify orders page:: {$page} \n";

            $payload = json_encode([
                "query" => $query,
                "variables" => $variables
            ]);


            $response = $this->shopifyObj->shopifyClientObj->callByGraphQl($payload);
            $orders = $response->data->fulfillmentOrders->nodes ?? [];


            foreach ($orders as $key => $order) {
                $row = [];
                $row['created_at'] = $order->fulfillAt;
                $row['s_location_name'] = $order->assignedLocation->location->name;

                // if ($row['fulfill_at'] >= $this->lastImportDateISOformat) {

                    $lineItems = $order->lineItems->nodes;
                    foreach ($lineItems as $key => $lineItem) {
                        $row['qty'] = $lineItem->totalQuantity;
                        $row['barcode'] = isset($lineItem->variant) ? $lineItem->variant->barcode : null;

                        // if (!is_null($row['barcode']) && $row['barcode'] != '') {
                            $this->insert($row);
                        // }
                    }
                // } else {
                //     $importDateReached = true;
                // }

            }

            $hasNextPage = $response->data->fulfillmentOrders->pageInfo->hasNextPage ?? false;

            if ($hasNextPage) {
                $variables['after'] = $response->data->fulfillmentOrders->pageInfo->endCursor;
            }

        
            $page++;

        } while ($hasNextPage && !$importDateReached);
    }

}