<?php

namespace scripts\InventoryCorrection\Base;

class HeartlandInventoryBase extends ManageDB
{
    private $table;

    function __construct()
    {
        parent::__construct();
        $this->table = "heartland_inventories";
    }

    public function resetTable()
    {

        $this->pdo->exec("DROP TABLE IF EXISTS {$this->table};");
        $this->pdo->exec("
                CREATE TABLE IF NOT EXISTS {$this->table} (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    item_id INTEGER NOT NULL,
                    h_location_id INTEGER,
                    on_hand INTEGER
                )
            ");
    }

    public function insert($row)
    {
        try {
            $stmt = $this->pdo->prepare("
	        	INSERT INTO {$this->table} (
	            	item_id, 
                    h_location_id, 
                    on_hand
                ) VALUES (
                    :item_id, 
                    :h_location_id, 
                    :on_hand
		        )
	        ");
            $stmt->bindParam(':item_id', $row['item_id']);
            $stmt->bindParam(':h_location_id', $row['h_location_id']);
            $stmt->bindParam(':on_hand', $row['on_hand']);
            $stmt->execute();
        } catch (\PDOException $e) {
            echo "Error: " . $e->getMessage();
        }
    }
}
