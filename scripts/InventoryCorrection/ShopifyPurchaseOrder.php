<?php

namespace scripts\InventoryCorrection;


class ShopifyPurchaseOrder extends ShopifyPurchaseOrderBase
{
    private $file;

    function __construct()
    {
        parent::__construct();
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

                $row['barcode'] = $data[array_search("Barcode", $header)];
                $row['s_location_name'] = $data[array_search("Location", $header)];
                $row['qty'] = $data[array_search("Adjustment", $header)];

                $date = $data[array_search("Date", $header)];
                $row['created_at'] = $this->changeDateFormat($date);

                if (!empty($row['barcode'])) {
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

            echo "Miss match update for purchase order: {$page} \n";
            $missMatches = $this->missMatch->getMissMatches($page, $limit);

            foreach ($missMatches as $key => $missMatch) {
                $sum = $this->getSum($missMatch);
                $this->missMatch->update($missMatch['id'], ['purchase_order' => $sum]);
            }

            $page++;
        } while (count($missMatches) == $limit);
    }
}
