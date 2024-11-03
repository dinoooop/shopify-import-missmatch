<?php
namespace scripts\product;

use lib\heartland\Heartland_main;
use helper\Helper;
use scripts\base\Import as ExecutionScript;
use PDO;
use PDOException;

class ManageDB extends ExecutionScript
{
    private $pdo;

    function __construct()
    {
        parent::__construct();

        // table: hearth_inventories = all inventory location (api request by item_id)
        // table: heartland_inventories = some inventory location

        // $database = 'database-1003'; // shopify products (2822) + all mismatch [final]
        // $database = 'database-1004'; // shopify - completed, heartland_inventories - completed, miss match - completed, hearth_inventories - working (taking all by item id) | working...
        // $database = 'database-1005'; // copy of database-1004 | finding miss match again -done
        // $database = 'database-1006'; // copy of database-1004, for testing csv
        // $database = 'database-1007'; // H items: 88, S items: 302, H Inventories: 164, missmatch 188
        $database = 'database-1008'; // Copy of 1007, for testing stocky
        $this->pdo = new PDO("sqlite:/home/digitalmesh/projects/hearth-2/{$database}.sqlite");
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }


    
    public function createMapLocation($truncate = false)
    {
        try {
            $this->pdo->exec("
                CREATE TABLE IF NOT EXISTS  (
                    
                )
            ");

            if ($truncate) {
                $this->pdo->exec("DELETE FROM map_locations");
                $this->pdo->exec("DELETE FROM sqlite_sequence WHERE name='map_locations'");
            }


            $csvFilePath = './resources/input/location_map.csv'; // @todo

            



        } catch (PDOException $e) {
            echo "Error: " . $e->getMessage();
        }
    }

    



    


    

    public function dropTables()
    {

        // $this->pdo->exec("DROP TABLE products_from_csv;");
        // $this->pdo->exec("DROP TABLE products;");
        $this->pdo->exec("DROP TABLE miss_match_products;");
    }

    

    
    

    

    public function getHProductInventory($itemId, $locationId)
    {
        $stmt = $this->pdo->prepare("SELECT * FROM heartland_inventories WHERE item_id = :item_id AND location_id = :location_id");
        $stmt->execute([':item_id' => $itemId]);
        $stmt->execute([':location_id' => $locationId]);
        $item = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $item[0] ?? [];
    }

    
    



    public function getSProducts($page, $limit)
    {
        $offset = ($page - 1) * $limit;
        $stmt = $this->pdo->prepare("
                    SELECT * FROM shopify_products 
                    WHERE barcode IS NOT NULL AND barcode != '' 
                    LIMIT :limit OFFSET :offset
                ");
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);

        $stmt->execute();
        $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $products;
    }
    public function getTest()
    {
        $test = '';
        // $stmt = $this->pdo->prepare("SELECT product_gid, count(*) AS dupe_count 
        //     FROM shopify_products
        //     GROUP BY product_gid
        //     ORDER BY dupe_count DESC LIMIT 10");
        $stmt = $this->pdo->prepare("SELECT count(*) AS total_count 
        FROM shopify_products
        GROUP BY product_gid");

        $stmt->execute();
        $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
        print_r($products);
        exit();
    }

    public function getProductsCount()
    {

        $stmt = $this->pdo->prepare("
            SELECT count(*) AS total_count FROM shopify_products 
            WHERE barcode IS NOT NULL AND barcode != '' 
        ");
        $stmt->execute();
        $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
        print_r($products);
        exit();
    }


    


    

   

    
    public function getMissMatchByBarcode($barcode)
    {
        try {
            $selectSQL = "
                SELECT * FROM miss_match_products
                WHERE barcode = :barcode
                LIMIT 1;
            ";

            $stmt = $this->pdo->prepare($selectSQL);

            $stmt->bindValue(':barcode', $barcode);
            $stmt->execute();

            $record = $stmt->fetch(PDO::FETCH_ASSOC);

            return $record;

        } catch (PDOException $e) {
            echo "Error fetching record: " . $e->getMessage();
        }
    }
    public function alterTable()
    {
        // try {
        //     // SQL statement to alter the table and add a new column
        //     $alterTableSQL = "
        //         ALTER TABLE miss_match_products
        //         ADD COLUMN location_name VARCHAR(255);
        //     ";

        //     // Execute the query
        //     $this->pdo->exec($alterTableSQL);

        // } catch (PDOException $e) {
        //     echo "Error altering table: " . $e->getMessage();
        // }
    }

    public function getMissMatchProducts($page, $limit)
    {
        $offset = ($page - 1) * $limit;
        $stmt = $this->pdo->prepare("SELECT * FROM products_from_csv LIMIT :limit OFFSET :offset");
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);

        $stmt->execute();
        $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $products;
    }

    


    

    

   


    public function createOrdersTable($truncate = false)
    {
        try {
            $this->pdo->exec("
                CREATE TABLE IF NOT EXISTS orders (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    fulfill_at VARCHAR(255),
                    location_gid VARCHAR(255),
                    qty INTEGER DEFAULT 0,
                    barcode VARCHAR(255)
                )
            ");

            if ($truncate) {
                $this->pdo->exec("DELETE FROM orders");
                $this->pdo->exec("DELETE FROM sqlite_sequence WHERE name='orders'");
            }

        } catch (PDOException $e) {
            echo "Error: " . $e->getMessage();
        }
    }


    public function insertOrder($row)
    {
        try {
            $stmt = $this->pdo->prepare("
            INSERT INTO orders (
                fulfill_at, 
                location_gid, 
                qty, 
                barcode
            ) 
            VALUES (
                :fulfill_at, 
                :location_gid, 
                :qty, 
                :barcode
            )
        ");

            $stmt->bindParam(':fulfill_at', $row['fulfill_at']);
            $stmt->bindParam(':location_gid', $row['location_gid']);
            $stmt->bindParam(':qty', $row['qty'], PDO::PARAM_INT);
            $stmt->bindParam(':barcode', $row['barcode']);

            $stmt->execute();

            return $this->pdo->lastInsertId();
        } catch (PDOException $e) {
            echo "Error: " . $e->getMessage();
            return false;
        }
    }


}