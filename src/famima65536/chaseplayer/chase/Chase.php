<?php

namespace famima65536\chaseplayer\chase;

use pocketmine\entity\Entity;
use pocketmine\entity\Location;
use pocketmine\math\Vector3;
use pocketmine\network\mcpe\protocol\SetActorLinkPacket;
use pocketmine\network\mcpe\protocol\types\entity\EntityLink;
use pocketmine\network\mcpe\protocol\types\entity\EntityMetadataFlags;
use pocketmine\network\mcpe\protocol\types\entity\EntityMetadataProperties;
use pocketmine\player\GameMode;
use pocketmine\player\Player;
use pocketmine\scheduler\TaskHandler;

class Chase{

    private bool $onGoing = false;
    public ?TaskHandler $taskHandler = null;
    
    private GameMode $previousGamemode;
    private Location $previousPosition;

    public function __construct(private Player $target, private Player $chaser, private TerminateCondition $terminateCondition, private ?Vector3 $offset = null){
    }

    public function getTarget(): Player
    {
        return $this->target;
    }

    public function getChaser(): Player
    {
        return $this->chaser;
    }

    public function getTerminateCondition(): TerminateCondition
    {
        return $this->terminateCondition;
    }

    public function start(): void{
        $this->previousGamemode = $this->chaser->getGamemode();
        $this->previousPosition = $this->chaser->getLocation();
        $this->chaser->setGamemode(GameMode::SPECTATOR());
        $this->chaser->setInvisible(true);
        $this->link();
        $this->onGoing = true;
    }

    public function isOnGoing(): bool{
        return $this->onGoing;
    }

    public function handleGetOff(): void{
        if(!$this->isOnGoing())
            return;
        if($this->terminateCondition->whenGetOff()){
            $this->end();
            return;
        }
        $this->link();
    }

    public function handleTargetDie(): void{
        if(!$this->isOnGoing())
            return;
        if($this->terminateCondition->whenTargetDie()){
            $this->end();
        }
    }

    public function link(): void{
        $this->chaser->getNetworkProperties()->setVector3(EntityMetadataProperties::RIDER_SEAT_POSITION, $this->offset ?? new Vector3(1, 0.2, 1));
        $this->chaser->getNetworkProperties()->setByte(EntityMetadataProperties::RIDER_ROTATION_LOCKED, 1, true);
        $this->chaser->getNetworkProperties()->setFloat(EntityMetadataProperties::RIDER_SEAT_ROTATION_OFFSET, 150, true);
        $this->chaser->getNetworkProperties()->setFloat(EntityMetadataProperties::RIDER_MIN_ROTATION, -20);
        $this->chaser->getNetworkProperties()->setFloat(EntityMetadataProperties::RIDER_MAX_ROTATION, 20);
        $pk = new SetActorLinkPacket();
        $pk->link = new EntityLink(
            $this->target->getId(),
            $this->chaser->getId(), 
            EntityLink::TYPE_RIDER,
            false,
            false
        );
        $this->chaser->getNetworkSession()->sendDataPacket($pk);
    }
    
    public function unlink(): void{
        $pk = new SetActorLinkPacket();
        $pk->link = new EntityLink(
            $this->target->getId(),
            $this->chaser->getId(), 
            EntityLink::TYPE_REMOVE,
            false,
            false
        );
        $this->chaser->getNetworkSession()->sendDataPacket($pk);
    }

    public function end(): void{
        $this->chaser->setGamemode($this->previousGamemode);
        $this->chaser->setInvisible(false);
        $this->chaser->teleport($this->previousPosition);
        $this->onGoing = false;
        $this->unlink();
        $this->taskHandler?->cancel();
    }
}