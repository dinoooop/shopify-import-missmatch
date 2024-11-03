<?php

use scripts\InventoryCorrection\InventoryCorrection;

ini_set('display_errors', 1);

spl_autoload_register(function ($class) {
    include str_replace('\\', DIRECTORY_SEPARATOR, $class) . '.php';
});


// Set map location - done
// Fetch shopify product - done
// Fetch heartland products - done
// Fetch heartland inventories - done
// Find Miss match - done
// Create CSV - misss matach quick read, miss matach import

if (isset($argv[1])) {
    $variable = $argv[1];
    
    $icObj = new InventoryCorrection();

    switch ($variable) {
        case 1:
            echo "Test code... \n";
            $icObj->testCode();
            break;

        case 2:
            echo "Set map location \n";
            $icObj->setMapLocation();
            
            break;

        case 3:
            echo "Fetch shopify product \n";
            
            break;

        case 4:
            echo "Fetch heartland products \n";
            
            break;

        case 5:
            echo "Fetch heartland inventories \n";
            
            break;

        case 6:
            echo "Find Miss Matches \n";
            
            break;

        case 7:
            echo "Find Stocky Activities \n";

            $icObj->findStockyActivities();
            
            break;
        
        default:
            # code...
            break;
    }
} else {
    echo "No variable passed.\n";
}