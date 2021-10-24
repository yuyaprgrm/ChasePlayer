<?php

namespace famima65536\chaseplayer;

use famima65536\chaseplayer\chase\Chase;
use pocketmine\network\mcpe\protocol\DataPacket;
use pocketmine\network\mcpe\protocol\RiderJumpPacket;
use pocketmine\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\scheduler\ClosureTask;
use pocketmine\Server;
use raklib\protocol\Packet;

class ChaseAPI{

    private static ChaseAPI $instance;

    /**
     * @internal
     */
    public static function init(PluginBase $plugin): void{
        self::$instance = new ChaseAPI($plugin);
    }

    public static function getInstance(): ChaseAPI{
        return self::$instance;
    }

    /** @var Chase[] $chasers_to_chase */
    private array $chasers_to_chase = [];

    /** @var Chase[] $targets_to_chase */
    private array $targets_to_chase = [];

    private function __construct(private PluginBase $plugin)
    {
    }

    /**
     * start Chase
     * @param $force whether it starts chase whenever chaser is in another chase.
     */
    public function start(Chase $chase, bool $force = false){
        $previousChase = $this->chasers_to_chase[$chase->getChaser()->getId()] ?? null;
        if($previousChase !== null && $previousChase->isOnGoing()){
            if($force){
                $this->terminate($chase->getChaser());
            }else{
                return;
            }
        }
        $this->chasers_to_chase[$chase->getChaser()->getId()] = $this->targets_to_chase[$chase->getTarget()->getId()] = $chase;
        $chase->start();
        if($chase->getChaseTime() !== null){
            $task = new ChaseCompletionTask($chase);
            $taskHandler = $this->plugin->getScheduler()->scheduleDelayedTask($task, $chase->getChaseTime()*Server::getInstance()->getTicksPerSecond());
            $chase->taskId = $taskHandler->getTaskId();
        }

    }

    public function handleGetOff(Player $player):void
    {
        $chase = $this->chasers_to_chase[$player->getId()] ?? null;
        if($chase === null) return;
        $chase->handleGetOff();
    }

    public function terminate(Player $player):void {
        $chase = $this->chasers_to_chase[$player->getId()] ?? null;
        if($chase === null || !$chase->isOnGoing())return;

        if($chase->getChaseTime() !== null)
            $this->plugin->getScheduler()->cancelTask($chase->taskId);
            
        $chase->end();
    }


}