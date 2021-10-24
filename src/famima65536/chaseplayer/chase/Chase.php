<?php

namespace famima65536\chaseplayer\chase;

use pocketmine\entity\Entity;
use pocketmine\level\Location;
use pocketmine\math\Vector3;
use pocketmine\network\mcpe\protocol\DataPacket;
use pocketmine\network\mcpe\protocol\RiderJumpPacket;
use pocketmine\network\mcpe\protocol\SetActorLinkPacket;
use pocketmine\network\mcpe\protocol\types\EntityLink;
use pocketmine\network\mcpe\protocol\types\GameMode;
use pocketmine\Player;
use raklib\protocol\Packet;

class Chase{

    private bool $onGoing = false;
    public int $taskId;
    
    private int $previousGamemode;
    private Location $previousPosition;

    public function __construct(private Player $target, private Player $chaser, private ?int $chasetime = null, private ?Vector3 $offset = null){
    }

    public function getTarget(): Player
    {
        return $this->target;
    }

    public function getChaser(): Player
    {
        return $this->chaser;
    }

    public function getChaseTime(): ?int{
        return $this->chasetime;
    }

    public function start(): void{
        $this->previousGamemode = $this->chaser->getGamemode();
        $this->previousPosition = $this->chaser->asLocation();
        $this->chaser->setGamemode(GameMode::SURVIVAL_VIEWER);
        $this->chaser->setInvisible(true);
        $this->link();
        $this->chaser->sendSubTitle("famima65536に倒された！");
        $this->onGoing = true;
    }

    public function isOnGoing(): bool{
        return $this->onGoing;
    }

    public function handleGetOff(){
        $this->link();
    }

    public function link(){
        $this->chaser->getDataPropertyManager()->setVector3(Entity::DATA_RIDER_SEAT_POSITION, $this->offset ?? new Vector3(-1, 0.5, -1));
        $this->chaser->getDataPropertyManager()->setByte(Entity::DATA_RIDER_ROTATION_LOCKED, 1);
        $this->chaser->getDataPropertyManager()->setFloat(Entity::DATA_RIDER_MIN_ROTATION, -90);
        $this->chaser->getDataPropertyManager()->setFloat(Entity::DATA_RIDER_MAX_ROTATION, 90);
        $pk = new SetActorLinkPacket();
        $pk->link = new EntityLink(
            $this->target->getId(),
            $this->chaser->getId(), 
            EntityLink::TYPE_RIDER,
            false,
            false
        );
        $this->chaser->sendDataPacket($pk);
    }
    
    public function unlink(){
        $pk = new SetActorLinkPacket();
        $pk->link = new EntityLink(
            $this->target->getId(),
            $this->chaser->getId(), 
            EntityLink::TYPE_REMOVE,
            false,
            false
        );
        $this->chaser->sendDataPacket($pk);
    }

    public function end(){
        $this->chaser->setGamemode($this->previousGamemode);
        $this->chaser->setInvisible(false);
        $this->chaser->teleport($this->previousPosition);
        $this->unlink();
        $this->onGoing = false;
    }
}