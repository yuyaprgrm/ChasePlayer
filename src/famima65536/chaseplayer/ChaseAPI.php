<?php

namespace famima65536\chaseplayer;

use famima65536\chaseplayer\chase\Chase;
use pocketmine\player\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\Server;

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
     * @param bool $force whether it starts chase whenever chaser is in another chase.
     */
    public function start(Chase $chase, bool $force = false): void{
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
            $chase->taskHandler = $this->plugin->getScheduler()->scheduleDelayedTask($task, (int) ($chase->getChaseTime()*Server::getInstance()->getTicksPerSecond()));
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

        $chase->taskHandler?->cancel();
            
        $chase->end();
    }


}