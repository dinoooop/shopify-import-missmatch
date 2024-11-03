<?php

namespace scripts\InventoryCorrection;

use scripts\InventoryCorrection\Base\ShopifyAdjustmentBase;
use scripts\InventoryCorrection\Utilities\Format;

class ShopifyAdjustment extends ShopifyAdjustmentBase
{
    private $file;

    function __construct()
    {
        parent::__construct();
        $this->file = "./resources/inventory-correction/stock_adjustments.csv";
        $this->missMatch = new MissMatch();
    }

    public function import()
    {
        try {

            $this->resetTable();
            $handle = fopen($this->file, 'r');

            $header = array_map('trim', fgetcsv($handle));
            while (($data = fgetcsv($handle)) !== false) {
                $row = [];
                $status = $data[array_search("Status", $header)];
                $row['barcode'] = $data[array_search("Barcode", $header)];
                $date = $data[array_search("Date", $header)];

                if ($status == 'adjusted' && !empty($row['barcode'])) {
                    $row['s_location_name'] = $data[array_search("Location", $header)];
                    $row['qty'] = $data[array_search("Adjustment", $header)];
                    $row['created_at'] = Format::changeDateFormat($date);
                    $this->insert($row);
                }
            }

            fclose($handle);
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    public function updateActivity()
    {
        

        $page = 1;
        $limit = 1000;

        do {

            echo "Miss match update for adjustment: {$page} \n";
            $missMatches = $this->missMatch->getMissMatches($page, $limit);

            foreach ($missMatches as $key => $missMatch) {
                $sum = $this->getAdjustmentSum($missMatch);
                $this->missMatch->update($missMatch['id'], ['adjustment' => $sum]);
            }

            $page++;
        } while (count($missMatches) == $limit);
    }
}
