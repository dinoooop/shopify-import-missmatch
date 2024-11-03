function getHlocationIdOfS($shopifyLocation_gid)
    {
        $sid = str_replace('gid://shopify/Location/', '', $shopifyLocation_gid);
        $sidInt = intval($sid);
        //Maping from shopify to heartland
        $locationMap = [
            64334200931 => 100005,
            66510585955 => 100006,
            66510717027 => 100007,
            66510913635 => 100027,
            66510782563 => 100047,
            66014380131 => 100066,
            66510684259 => 100069,
            66510749795 => 100070,
            66510848099 => 100071,
            66510880867 => 100067,
        ];

        return $locationMap[$sidInt] ?? null;

    }

    function getHearthLocationName($shopifyLocation_gid)
    {
        $sid = str_replace('gid://shopify/Location/', '', $shopifyLocation_gid);
        $sidInt = intval($sid);
        // shopify location id map to hearthland location name
        $locationMap = [
            64334200931 => 'Hearth and Soul - Tallahassee',
            66510585955 => 'AMWAT- Warehouse (Tallahassee)',
            66510717027 => 'The Barn - Warehouse (Tallahassee)',
            66510913635 => 'CubeSmart',
            66510782563 => 'Off-Site Tallahassee',
            66014380131 => 'Hearth and Soul - St. Louis',
            66510684259 => 'Dains Delivery - Warehouse (St. Louis)',
            66510749795 => 'Midpark - Warehouse (St. Louis)',
            66510848099 => 'Off-Site St. Louis',
            66510880867 => 'TALLAHASSEE HOLDING'
        ];

        return $locationMap[$sidInt] ?? null;

    }

    
    function getShopifyLocationName($shopifyLocation_gid)
    {
        $sid = str_replace('gid://shopify/Location/', '', $shopifyLocation_gid);
        $sidInt = intval($sid);
        // shopify location id map to shopify location name
        $locationMap = [
            64334200931 => 'TLH',
            66510585955 => 'AMWAT - Warehouse',
            66510717027 => 'TLH Warehouse',
            66510913635 => 'ATX Holding',
            66510782563 => 'TLH Off-Site',
            66014380131 => 'STL',
            66510684259 => 'Dains - Warehouse',
            66510749795 => 'STL Warehouse',
            66510848099 => 'STL Off-Site',
            66510880867 => 'TLH Holding',
        ];

        return $locationMap[$sidInt] ?? null;

    }


    public function createProductsFromCSVTable($truncate = false)
    {
        try {
            $this->pdo->exec("
                CREATE TABLE IF NOT EXISTS products_from_csv (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    handle VARCHAR(255),
                    option1_name VARCHAR(255),
                    option1_value VARCHAR(255),
                    option2_name VARCHAR(255),
                    option2_value VARCHAR(255),
                    option3_name VARCHAR(255),
                    option3_value VARCHAR(255),
                    location VARCHAR(255),
                    on_hand INTEGER,
                    variant_barcode VARCHAR(255)
                )
            ");

            if ($truncate) {
                $this->pdo->exec("DELETE FROM products_from_csv");
                // reset the AUTOINCREMENT 
                $this->pdo->exec("DELETE FROM sqlite_sequence WHERE name='products_from_csv'");
            }

        } catch (PDOException $e) {
            echo "Error: " . $e->getMessage();
        }
    }


    public function updateLocationName($s_locaion_id, $locationName)
    {
        try {
            $updateSQL = "
                UPDATE miss_match_products
                SET location_name = :location_name
                WHERE s_locaion_id = :s_locaion_id;
            ";

            $stmt = $this->pdo->prepare($updateSQL);

            $stmt->bindValue(':location_name', $locationName);
            $stmt->bindValue(':s_locaion_id', $s_locaion_id);
            $stmt->execute();

        } catch (PDOException $e) {
            echo "Error updating record: " . $e->getMessage();
        }
    }


    public function getFirstMisMatchLocationNull()
    {
        try {
            // SQL statement to get the first record where location_name is NULL
            $selectSQL = "
                SELECT * FROM miss_match_products
                WHERE location_name IS NULL
                LIMIT 1;
            ";

            $stmt = $this->pdo->prepare($selectSQL);
            $stmt->execute();

            $record = $stmt->fetch(PDO::FETCH_ASSOC);

            return $record;

        } catch (PDOException $e) {
            echo "Error fetching record: " . $e->getMessage();
        }
    }

    public function insertProductFromCSVTable($row)
    {
        try {
            $stmt = $this->pdo->prepare("
                INSERT INTO products_from_csv (
                    handle,
                    option1_name,
                    option1_value,
                    option2_name,
                    option2_value,
                    option3_name,
                    option3_value,
                    location,
                    on_hand,
                    variant_barcode
                ) VALUES (
                    :handle,
                    :option1_name,
                    :option1_value,
                    :option2_name,
                    :option2_value,
                    :option3_name,
                    :option3_value,
                    :location,
                    :on_hand,
                    :variant_barcode
                )
            ");
            $stmt->bindParam(':handle', $row['handle']);
            $stmt->bindParam(':option1_name', $row['option1_name']);
            $stmt->bindParam(':option1_value', $row['option1_value']);
            $stmt->bindParam(':option2_name', $row['option2_name']);
            $stmt->bindParam(':option2_value', $row['option2_value']);
            $stmt->bindParam(':option3_name', $row['option3_name']);
            $stmt->bindParam(':option3_value', $row['option3_value']);
            $stmt->bindParam(':location', $row['location']);
            $stmt->bindParam(':on_hand', $row['on_hand']);
            $stmt->bindParam(':variant_barcode', $row['variant_barcode']);
            $stmt->execute();
        } catch (PDOException $e) {
            echo "Error: " . $e->getMessage();
        }
    }