<?php

namespace famima65536\chaseplayer\chase;

use pocketmine\math\Vector3;


class ChaseDetail{

    public static Vector3 $defaultPositionOffset;
    public static int $defaultRotationOffset = 150;
    public static int $defaultRotationAngle = 20;

    public static function init(): void{
        self::$defaultPositionOffset = new Vector3(1, 0.2, 1);
    }

    
    private Vector3 $positionOffset;
    private int $rotationOffset;
    private int $rotationAngle;

    public function __construct(?Vector3 $positionOffset = null, ?int $rotationOffset = null, ?int $rotationAngle = null)
    {
        $this->positionOffset = $positionOffset ?? self::$defaultPositionOffset;
        $this->rotationOffset = $rotationOffset ?? self::$defaultRotationOffset;
        $this->rotationAngle = $rotationAngle ?? self::$defaultRotationAngle;
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

}