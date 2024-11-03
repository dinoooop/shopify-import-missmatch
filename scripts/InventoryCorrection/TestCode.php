<?php

namespace scripts\InventoryCorrection;

use scripts\InventoryCorrection\Base\ManageDB;

class TestCode extends ManageDB
{

    function __construct()
    {
        parent::__construct();
    }

    public function dbChanges()
    {
        $this->pdo->exec("DROP TABLE IF EXISTS hearth_inventories;");
        $this->pdo->exec("ALTER TABLE hearth_products RENAME TO heartland_products;");
        $this->pdo->exec("ALTER TABLE miss_match_products RENAME TO miss_matches;");
        $this->pdo->exec("ALTER TABLE miss_matches RENAME COLUMN h_locaion_id TO h_location_id;");
        $this->pdo->exec("ALTER TABLE miss_matches RENAME COLUMN s_locaion_id TO s_location_id;");

        // Add columns
        $columnsToAdd = [
            'order INTEGER',
            'purchase_order INTEGER',
            'transfer INTEGER',
            'adjustment INTEGER',
            'other INTEGER',
        ];

        foreach ($columnsToAdd as $columnDefinition) {
            $this->pdo->exec("ALTER TABLE miss_matches ADD COLUMN {$columnDefinition}");
        }

        
    }

    public function dropTables()
    {

        // $this->pdo->exec("DROP TABLE products_from_csv;");
        // $this->pdo->exec("DROP TABLE products;");
        $this->pdo->exec("DROP TABLE miss_match_products;");
    }

    public function test()
    {



        $obj = new MapLocation();
        echo $obj->getSLocationIdByName("TLHL");
    }
}
