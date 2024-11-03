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

    public function createTable($truncate = false)
    {

        $this->pdo->exec("
                CREATE TABLE IF NOT EXISTS {$this->table} (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    item_id INTEGER NOT NULL,
                    location_id INTEGER,
                    on_hand INTEGER
                )
            ");

        if ($truncate) {
            $this->pdo->exec("DELETE FROM {$this->table}");
            $this->pdo->exec("DELETE FROM sqlite_sequence WHERE name='{$this->table}'");
        }
    }

    public function insert($row)
    {
        try {
            $stmt = $this->pdo->prepare("
	        	INSERT INTO {$this->table} (
	            	item_id, 
                    location_id, 
                    on_hand
                ) VALUES (
                    :item_id, 
                    :location_id, 
                    :on_hand
		        )
	        ");
            $stmt->bindParam(':item_id', $row['item_id']);
            $stmt->bindParam(':location_id', $row['location_id']);
            $stmt->bindParam(':on_hand', $row['on_hand']);
            $stmt->execute();
        } catch (\PDOException $e) {
            echo "Error: " . $e->getMessage();
        }
    }
}
