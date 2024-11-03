<?php

namespace scripts\InventoryCorrection;

use lib\heartland\Heartland_main;

class HeartlandInventory extends HeartlandInventoryBase
{
    private $table;

    function __construct()
    {
        parent::__construct();
        $this->HeartlandObj = new Heartland_main();
    }

    function init()
    {
        try {

            
            $this->resetTable();

            $page = 1;
            $perPage = 1000;

            do {

                Helper::printMe("Heartland inventories page - {$page}");
                $query = array(
                    'per_page' => $perPage,
                    'page' => $page,
                    'group' => ['item_id', 'location_id']
                );
                $query = http_build_query($query);
                $query = preg_replace('/\[\d+\]/', '[]', urldecode($query));
                $path = "inventory/values?{$query}";
                $inventoryData = $this->HeartlandObj->HeartlandClientObj->call($path);
                $inventoryData = Helper::objectToArray($inventoryData)['results'];


                if (!empty($inventoryData)) {
                    if (!isset($inventoryData[0]['item_id'])) {
                        return array(
                            "errors" => $inventoryData,
                        );
                    } else {
                        foreach ($inventoryData as $data) {
                            $row = [];
                            $row['item_id'] = $data['item_id'];
                            $row['h_location_id'] = $data['location_id'];
                            $row['on_hand'] = $data['qty_on_hand'];
                            $this->insert($row);
                        }
                    }
                } else {
                    return array(
                        "errors" => "No inventoryData in store",
                    );
                }

                $page++;
            } while (count($inventoryData) == $perPage);

        } catch (\Exception $e) {
            return array("errors" => $e->getMessage());
        }

    }
}
