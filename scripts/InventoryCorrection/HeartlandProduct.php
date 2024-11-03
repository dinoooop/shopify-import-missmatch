<?php

namespace scripts\InventoryCorrection;

use lib\heartland\Heartland_main;
use helper\Helper;
use scripts\InventoryCorrection\Base\HeartlandProductBase;

class HeartlandProduct extends HeartlandProductBase
{
    private $HeartlandObj;

    function __construct()
    {
        parent::__construct();
        $this->HeartlandObj = new Heartland_main();
    }

    function init()
    {
        try {

            $this->resetTable();

            Helper::printMe("Write Heartland items");
            $page = 1;
            $perPage = 1000;

            do {
                Helper::printMe("Heartland items page:- {$page}");
                $query = array(
                    'per_page' => $perPage,
                    'page' => $page,
                );

                $query = http_build_query($query);
                $query = preg_replace('/\[\d+\]/', '[]', urldecode($query));
                $path = "items?{$query}";
                $itemData = $this->HeartlandObj->HeartlandClientObj->call($path);
                $itemData = Helper::objectToArray($itemData)['results'];

                if (!empty($itemData)) {
                    if (!isset($itemData[0]['id'])) {
                        return array(
                            "errors" => $itemData,
                        );
                    } else {
                        foreach ($itemData as $data) {
                            $row = [];
                            $row['item_id'] = $data['id'];
                            $row['public_id'] = $data['public_id'];
                            $row['sku'] = (isset($data['custom']) && isset($data['custom']['sku'])) ? $data['custom']['sku'] : null;
                            $row['barcode'] = $data['primary_barcode'];
                            $this->insert($row);
                        }
                    }
                } else {
                    return array(
                        "errors" => "No itemData in store",
                    );
                }

                $page++;
            } while (count($itemData) == $perPage);

        } catch (\Exception $e) {
            return array("errors" => $e->getMessage());
        }
    }

}