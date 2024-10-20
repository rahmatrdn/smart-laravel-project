<?php

namespace App\Entities;

class TourPackageEntity
{
    public static function getPackageTypeName(int $typeInt): string
    {
        switch ($typeInt) {
            case 1:
                $type = "Open Trip";
                break;
            case 2:
                $type = "Private Trip";
                break;
            case 3:
                $type = "Mice & Outbound";
                break;

            default:
                $type = "";
                break;
        }

        return $type;
    }
}
