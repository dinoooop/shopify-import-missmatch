<?php

namespace scripts\InventoryCorrection;

use scripts\InventoryCorrection\Base\ShopifyAdjustmentBase;

class ShopifyAdjustment extends ShopifyAdjustmentBase
{
    private $file;

    function __construct()
    {
        parent::__construct();
        $this->file = "./resources/inventory-correction/shopify_adjustments.csv";
    }

    public function import()
    {
        try {
            $handle = fopen($this->file, 'r');

            $header = array_map('trim', fgetcsv($handle));
            while (($data = fgetcsv($handle)) !== false) {
                $row = [];
                $row['barcode'] = $data[array_search("Barcode", $header)];
                $row['location_gid'] = $this->getShopifyLocationIdByName($data[array_search("Location Name", $header)]);
                $row['qty'] = $data[array_search("Quantity", $header)];
                $row['created_at'] = $data[array_search("Date", $header)];
                $this->insert($row);
            }

            fclose($handle);
        } catch (\Throwable $th) {
            throw $th;
        }
    }
}
