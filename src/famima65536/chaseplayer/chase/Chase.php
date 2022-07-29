<?php

namespace famima65536\chaseplayer\chase;

use pocketmine\entity\Entity;
use pocketmine\entity\Location;
use pocketmine\math\Vector3;
use pocketmine\network\mcpe\protocol\AddActorPacket;
use pocketmine\network\mcpe\protocol\RemoveActorPacket;
use pocketmine\network\mcpe\protocol\SetActorLinkPacket;
use pocketmine\network\mcpe\protocol\types\entity\ByteMetadataProperty;
use pocketmine\network\mcpe\protocol\types\entity\EntityIds;
use pocketmine\network\mcpe\protocol\types\entity\EntityLink;
use pocketmine\network\mcpe\protocol\types\entity\EntityMetadataFlags;
use pocketmine\network\mcpe\protocol\types\entity\EntityMetadataProperties;
use pocketmine\network\mcpe\protocol\types\entity\IntMetadataProperty;
use pocketmine\network\mcpe\protocol\types\entity\LongMetadataProperty;
use pocketmine\network\mcpe\protocol\types\entity\MetadataProperty;
use pocketmine\player\GameMode;
use pocketmine\player\Player;
use pocketmine\scheduler\TaskHandler;
use pocketmine\utils\TextFormat;

class Chase{

    private bool $onGoing = false;
    public ?TaskHandler $taskHandler = null;
    
    private GameMode $previousGamemode;
    private Location $previousPosition;

    private int $dummyTargetId;

    public function __construct(private Player $target, private Player $chaser, private TerminateCondition $terminateCondition, private ChaseDetail $chaseDetail){
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
        $this->chaser->getNetworkProperties()->setVector3(EntityMetadataProperties::RIDER_SEAT_POSITION, $this->chaseDetail->positionOffset());
        $this->chaser->getNetworkProperties()->setByte(EntityMetadataProperties::RIDER_ROTATION_LOCKED, 1, true);
        $this->chaser->getNetworkProperties()->setFloat(EntityMetadataProperties::RIDER_SEAT_ROTATION_OFFSET, $this->chaseDetail->rotationOffset(), true);
        $this->chaser->getNetworkProperties()->setFloat(EntityMetadataProperties::RIDER_MIN_ROTATION, -$this->chaseDetail->rotationAngle());
        $this->chaser->getNetworkProperties()->setFloat(EntityMetadataProperties::RIDER_MAX_ROTATION, $this->chaseDetail->rotationAngle());
        
        if($this->chaseDetail->smoothChase()){
            $this->dummyTargetId = Entity::nextRuntimeId();
            $pk = AddActorPacket::create(
                $this->dummyTargetId,
                $this->dummyTargetId,
                EntityIds::HORSE,
                new Vector3(0,0,0),
                new Vector3(0,0,0),
                0,
                0,
                0,
                0,
                [
                ],
                [
                    EntityMetadataProperties::FLAGS => new LongMetadataProperty(1 << EntityMetadataFlags::INVISIBLE)
                ],
                [
                    new EntityLink($this->target->getId(), $this->dummyTargetId, EntityLink::TYPE_PASSENGER, true, false)
                ]
            );

            $this->chaser->getNetworkSession()->sendDataPacket($pk);
        }else{
            $this->dummyTargetId = $this->target->getId();
        }

        $pk = SetActorLinkPacket::create(new EntityLink(
            $this->dummyTargetId,
            $this->chaser->getId(), 
            EntityLink::TYPE_RIDER,
            false,
            false
        ));
        $this->chaser->getNetworkSession()->sendDataPacket($pk);
    }
    
    public function unlink(): void{
        $pk = SetActorLinkPacket::create(new EntityLink(
            $this->dummyTargetId,
            $this->chaser->getId(), 
            EntityLink::TYPE_REMOVE,
            false,
            false
        ));
        $this->chaser->getNetworkSession()->sendDataPacket($pk);

        if($this->chaseDetail->smoothChase()){
            $pk = RemoveActorPacket::create($this->dummyTargetId);
            $this->chaser->getNetworkSession()->sendDataPacket($pk);
        }
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