<?php

namespace scripts\InventoryCorrection;


class ShopifyTransfer extends ShopifyTransferBase
{
    private $file;

    function __construct()
    {
        parent::__construct();
        $this->missMatch = new MissMatch();
        $this->file = "./resources/inventory-correction/stock_transfers.csv";
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
                $row['from_s_location_name'] = $data[array_search("From Location", $header)];
                $row['to_s_location_name'] = $data[array_search("To Location", $header)];
                $row['sent_at'] = $data[array_search("Sent At", $header)];
                $row['received_at'] = $data[array_search("Received At", $header)];
                $row['qty'] = $data[array_search("Quantity", $header)];

                $date = $data[array_search("Received At", $header)];
                $row['created_at'] = $this->changeDateFormat($date);

                if (
                    ($status == 'received' || $status == 'sent')
                    && !empty($row['barcode'])
                ) {
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

            echo "Miss match update for transfer: {$page} \n";
            $missMatches = $this->missMatch->getMissMatches($page, $limit);

            foreach ($missMatches as $key => $missMatch) {
                $sumIn = $this->getSumIn($missMatch);
                $sumOut = $this->getSumOut($missMatch);
                $sum = $sumIn + $sumOut;
                $this->missMatch->update($missMatch['id'], ['purchase_order' => $sum]);
            }

            $page++;
        } while (count($missMatches) == $limit);
    }
}
