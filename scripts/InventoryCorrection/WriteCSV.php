<?php

namespace scripts\InventoryCorrection;

class WriteCSV extends WriteCSVBase
{

    function __construct()
    {
        $date = date("Y-m-d");
        $this->fileImportMissMatch = "./resources/inventory-correction/output/heartland-import-missmatches-{$date}.csv";
        $this->fileQuickRead = "./resources/inventory-correction/output/heartland-quick-read-missmatches-{$date}.csv";
    }

    public function createImportCSV()
    {
        try {

            $file = fopen($this->fileImportMissMatch, 'w');

            if ($file !== false) {
                fputcsv($file, [
                    'Handle',
                    'Option1 Name',
                    'Option1 Value',
                    'Option2 Name',
                    'Option2 Value',
                    'Option3 Name',
                    'Option3 Value',
                    'Location',
                    'On hand',
                ]);
            } else {
                exit("csv file error");
            }

            $page = 1;
            $perPage = 1000;

            do {
                echo "write csv page: {$page} \n";
                $products = $this->getMissMatchesForWriteImportCSV($page, $perPage);
                foreach ($products as $product) {
                    fputcsv($file, [
                        $product['handle'],
                        $product['option1_name'],
                        $product['option1_value'],
                        $product['option2_name'],
                        $product['option2_value'],
                        $product['option3_name'],
                        $product['option3_value'],
                        $product['location'],
                        $product['on_hand'],
                    ]);
                }


                $page++;
            } while (count($products) == $perPage);

            fclose($file);
        } catch (\Exception $e) {
            throw $e;
        }
    }


    // For reading
    public function createQuickReadCSV()
    {
        try {
            $page = 1;
            $perPage = 1000;


            $file = fopen($this->fileQuickRead, 'w');

            if ($file !== false) {
                fputcsv($file, [
                    'Barcode',
                    'Heartland Location',
                    'Shopify Location',
                    'Heartland on hand qty',
                    'Shopify on hand qty',
                ]);
            } else {
                exit("csv file error");
            }

            do {

                echo "write to csv page: {$page} \n";
                $missMatches = $this->getMissMatchesForWriteQuickReadCSV($page, $perPage);

                foreach ($missMatches as $key => $missMatche) {
                    fputcsv($file, [
                        $missMatche['barcode'],
                        $missMatche['h_location_name'],
                        $missMatche['s_location_name'],
                        $missMatche['h_qty'],
                        $missMatche['s_qty'],
                    ]);
                }

                $page++;
            } while (count($missMatches) == $perPage);

            fclose($file);
        } catch (\Throwable $th) {
            throw $th;
        }
    }
}
