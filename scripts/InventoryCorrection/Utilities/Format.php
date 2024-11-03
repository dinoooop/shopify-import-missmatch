<?php

namespace scripts\InventoryCorrection\Utilities;


class Format
{

    // d-m-Y to Y-m-d
    public static function changeDateFormat($date)
    {
        $dateTime = \DateTime::createFromFormat('d-m-Y', $date);
        return $dateTime ? $dateTime->format('Y-m-d') : false;
    }
}
