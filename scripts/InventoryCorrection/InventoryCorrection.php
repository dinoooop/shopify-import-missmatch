<?php

namespace scripts\InventoryCorrection;


class InventoryCorrection
{

    function __construct() {}

    public function testCode()
    {
        $obj = new TestCode();
        $obj->test();
    }
    public function setMapLocation()
    {
        $obj = new MapLocation();
        $obj->init();
    }
    public function setShopifyProducts()
    {
        $obj = new ShopifyProduct();
        $obj->importFromCSV();
    }
    public function setHeartlandProducts()
    {
        $obj = new HeartlandProduct();
        $obj->init();
    }
    public function setHeartlandInventories()
    {
        $obj = new HeartlandInventory();
        $obj->init();
    }

    public function findMissMatches()
    {
        $obj = new MissMatch();
        $obj->init();
    }

    public function findStockyActivities()
    {
        $obj = new ShopifyAdjustment();
        $obj->import();
        $obj->updateActivity();
    }
    // Final task
    public function generateCSV()
    {
        $obj = new WriteCSV();
        $obj->createImportCSV();
        $obj->createQuickReadCSV();
    }
}
