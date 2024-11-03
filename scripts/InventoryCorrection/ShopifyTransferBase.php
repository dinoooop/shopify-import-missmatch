<?php

namespace scripts\InventoryCorrection;


class ShopifyTransferBase extends ManageDB
{
    private $table;

    function __construct()
    {
        parent::__construct();
        $this->table = "shopify_transfers";
    }

    public function resetTable()
    {

        $this->pdo->exec("DROP TABLE IF EXISTS {$this->table};");
        $this->pdo->exec("
                CREATE TABLE IF NOT EXISTS {$this->table} (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    barcode VARCHAR(255),
                    from_s_location_name VARCHAR(255),
                    to_s_location_name VARCHAR(255),
                    sent_at TEXT,
                    received_at TEXT,
                    qty INTEGER
                )
            ");
    }

    public function insert($row)
    {
        try {
            $stmt = $this->pdo->prepare("
                INSERT INTO {$this->table} (
                    barcode, 
                    from_s_location_name, 
                    to_s_location_name,
                    sent_at,
                    received_at,
                    qty
                ) VALUES (
                    :barcode, 
                    :from_s_location_name, 
                    :to_s_location_name, 
                    :sent_at,
                    :received_at,
                    :qty
                )
            ");
            $stmt->bindParam(':barcode', $row['barcode']);
            $stmt->bindParam(':from_s_location_name', $row['from_s_location_name']);
            $stmt->bindParam(':to_s_location_name', $row['to_s_location_name']);
            $stmt->bindParam(':sent_at', $row['sent_at']);
            $stmt->bindParam(':received_at', $row['received_at']);
            $stmt->bindParam(':qty', $row['qty']);
            $stmt->execute();
        } catch (\PDOException $e) {
            echo "Error: " . $e->getMessage();
        }
    }

    public function getSumIn($missMatch)
    {
        try {
            $stmt = $this->pdo->prepare("
                SELECT SUM(qty) FROM {$this->table} 
                WHERE 
                    barcode = :barcode AND 
                    to_s_location_name = :to_s_location_name AND 
                    received_at > :received_at
            ");

            $stmt->bindParam(':barcode', $missMatch['barcode']);
            $stmt->bindParam(':to_s_location_name', $missMatch['s_location_name']);
            $stmt->bindParam(':received_at', $this->lastImportDate);
            $stmt->execute();
            $record = $stmt->fetch(\PDO::FETCH_ASSOC);
            return $record['SUM(qty)'] ?? 0;
        } catch (\PDOException $e) {
            echo "Error: " . $e->getMessage();
        }
    }

    public function getSumOut($missMatch)
    {
        try {
            $stmt = $this->pdo->prepare("
                SELECT SUM(qty) FROM {$this->table} 
                WHERE 
                    barcode = :barcode AND 
                    from_s_location_name = :from_s_location_name AND 
                    sent_at > :sent_at
            ");

            $stmt->bindParam(':barcode', $missMatch['barcode']);
            $stmt->bindParam(':from_s_location_name', $missMatch['s_location_name']);
            $stmt->bindParam(':sent_at', $this->lastImportDate);
            $stmt->execute();
            $record = $stmt->fetch(\PDO::FETCH_ASSOC);
            return $record['SUM(qty)'] ?? 0;
        } catch (\PDOException $e) {
            echo "Error: " . $e->getMessage();
        }
    }

   
}
