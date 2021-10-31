<?php

namespace famima65536\chaseplayer\chase;

use pocketmine\math\Vector3;

use M_PI;

class ChaseDetail{

    public static int $defaultRotationOffset = 150;
    public static int $defaultRotationAngle = 20;
    public static int $defaultDistance = 2;
    public static int $defaultYaw = 0;
    
    private Vector3 $positionOffset;
    private int $rotationOffset;
    private int $rotationAngle;
    private bool $smoothChase;

    /**
     * Chase Detail can designate relative chase location in two way
     *  way 1. $positionOffset + $rotationOffset + $rotationAngle
     *  way 2. $distance + $yawOffset + $rotationOffset + $rotationAngle
     * If no option are given, way 2 will be chosen to calculate default Chase Detail.
     */
    public function __construct(?Vector3 $positionOffset = null, ?int $rotationOffset = null, ?int $rotationAngle = null, ?int $distance=null, ?int $yawOffset = null, bool $smoothChase=false)
    {
        $this->rotationOffset = $rotationOffset ?? self::$defaultRotationOffset;
        $this->rotationAngle = $rotationAngle ?? self::$defaultRotationAngle;
        $this->smoothChase = $smoothChase;

        if($positionOffset === null){
            $distance ??= self::$defaultDistance;
            $yawOffset ??= self::$defaultYaw;

            $xz = cos($yawOffset*M_PI/180);
            $y  = sin($yawOffset*M_PI/180);
            $z  = -$xz * cos($rotationOffset*M_PI/180);
            $x  = $xz * sin($rotationOffset*M_PI/180);
            $this->positionOffset = (new Vector3($x, $y, $z))->multiply($distance);
        }else{
            $this->positionOffset = $positionOffset;
        }
    }

    public function positionOffset(): Vector3{
        return $this->positionOffset;
    }

    public function rotationOffset(): int{
        return $this->rotationOffset;
    }

    public function rotationAngle(): int{
        return $this->rotationAngle;
    }

    public function smoothChase(): bool{
        return $this->smoothChase;
    }

}