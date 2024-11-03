<?php

namespace scripts\InventoryCorrection;

class MapLocation extends MapLocationBase
{
    
    private $file;

    function __construct()
    {
        parent::__construct();
        $this->file = "./resources/inventory-correction/map_locations.csv";
    }

    public function init()
    {

        try {
            $handle = fopen($this->file, 'r');

            $header = array_map('trim', fgetcsv($handle));

            while (($data = fgetcsv($handle)) !== false) {
                $row = [];
                $row['h_location_id'] = $data[array_search("Hearland Location Id", $header)];
                $row['h_location_name'] = $data[array_search("Heartland Location Name", $header)];
                $row['s_location_id'] = $data[array_search("Shopify Location Id", $header)];
                $row['s_location_name'] = $data[array_search("Shopify Location Name", $header)];
                $this->insert($row);
            }

            fclose($handle);
        } catch (\Throwable $th) {
            throw $th;
        }
    }
}
