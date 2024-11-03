<?php

namespace scripts\InventoryCorrection\Base;

class HeartlandProductBase extends ManageDB
{
    private $table;

    function __construct()
    {
        parent::__construct();
        $this->table = "heartland_products";
    }

    public function createTable($truncate = false)
    {

        $this->pdo->exec("
                CREATE TABLE IF NOT EXISTS {$this->table} (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    item_id INTEGER NOT NULL,
                    public_id INTEGER NOT NULL,
                    sku VARCHAR(255),
                    barcode VARCHAR(255)
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
	        	INSERT INTO hearth_rows (
	            	item_id, 
	            	public_id, 
	            	sku,
	            	barcode
	            ) VALUES (
		            :item_id,
		            :public_id,
		            :sku,
		            :barcode
		        )
	        ");
            $stmt->bindParam(':item_id', $row['item_id']);
            $stmt->bindParam(':public_id', $row['public_id']);
            $stmt->bindParam(':sku', $row['sku']);
            $stmt->bindParam(':barcode', $row['barcode']);
            $stmt->execute();
        } catch (\PDOException $e) {
            echo "Error: " . $e->getMessage();
        }
    }

    public function getByBarcode($barcode)
    {
        $stmt = $this->pdo->prepare("SELECT * FROM {$this->table} WHERE barcode = :barcode LIMIT 1");
        $stmt->execute([':barcode' => $barcode]);
        $record = $stmt->fetch(\PDO::FETCH_ASSOC);
        return $record;
    }

    public function paginate($page, $limit)
    {
        $offset = ($page - 1) * $limit;
        $stmt = $this->pdo->prepare("SELECT * FROM {$this->table} LIMIT :limit OFFSET :offset");
        $stmt->bindParam(':limit', $limit, \PDO::PARAM_INT);
        $stmt->bindParam(':offset', $offset, \PDO::PARAM_INT);
        $stmt->execute();
        $products = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        return $products;
    }
}
