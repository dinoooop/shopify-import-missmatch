<?php

namespace scripts\InventoryCorrection\Base;

class ShopifyAdjustmentBase extends ManageDB
{
    private $table;

    function __construct()
    {
        parent::__construct();
        $this->table = "stocky_adjustments";
    }

    public function createTable($truncate = false)
    {

        $this->pdo->exec("
                CREATE TABLE IF NOT EXISTS {$this->table} (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    barcode VARCHAR(255),
                    location_gid VARCHAR(255),
                    qty INTEGER,
                    created_at TEXT
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
                    barcode, 
                    location_gid, 
                    qty,
                    created_at
                ) VALUES (
                    :barcode, 
                    :location_gid, 
                    :qty,
                    :created_at
                )
            ");
            $stmt->bindParam(':barcode', $row['barcode']);
            $stmt->bindParam(':location_gid', $row['location_gid']);
            $stmt->bindParam(':qty', $row['qty']);
            $stmt->bindParam(':created_at', $row['created_at']);
            $stmt->execute();
        } catch (\PDOException $e) {
            echo "Error: " . $e->getMessage();
        }
    }

    

    public function getAdjustmentSum($barcode, $location, $date)
    {
        try {
            $stmt = $this->pdo->prepare("
                SELECT SUM(qty) FROM {$this->table} 
                WHERE 
                    barcode = :barcode AND 
                    location_gid = :location_gid AND 
                    created_at > :created_at
            ");

            $stmt->bindParam(':barcode', $barcode);
            $stmt->bindParam(':location_gid', $location);
            $stmt->bindParam(':created_at', $date);
            $stmt->execute();
            $record = $stmt->fetch(\PDO::FETCH_ASSOC);
            return $record['SUM(qty)'] ?? 0;
        } catch (\PDOException $e) {
            echo "Error: " . $e->getMessage();
        }
    }
}
