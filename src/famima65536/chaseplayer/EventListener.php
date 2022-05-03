<?php

namespace famima65536\chaseplayer;

use famima65536\chaseplayer\chase\Chase;
use famima65536\chaseplayer\chase\ChaseDetail;
use famima65536\chaseplayer\chase\TerminateCondition;
use pocketmine\block\VanillaBlocks;
use pocketmine\entity\Entity;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\event\server\DataPacketReceiveEvent;
use pocketmine\item\VanillaItems;
use pocketmine\network\mcpe\protocol\InteractPacket;
use pocketmine\player\Player;
use pocketmine\plugin\PluginBase;

class EventListener implements Listener{

    private ChaseAPI $chaseapi;

    public function __construct()
    {
        $this->chaseapi = ChaseAPI::getInstance();
    }

    public function onQuit(PlayerQuitEvent $event): void{
        $this->chaseapi->terminate($event->getPlayer());
    }

    public function onPacket(DataPacketReceiveEvent $event): void{
        $pk = $event->getPacket();
        if($pk instanceof InteractPacket && $pk->action === InteractPacket::ACTION_LEAVE_VEHICLE){
            $player = $event->getOrigin()->getPlayer();
            if($player !== null)
                $this->chaseapi->handleGetOff($player);
        }
    }

    public function onDamage(EntityDamageByEntityEvent $event): void{
        $ridee = $event->getEntity();
        $rider = $event->getDamager();
        if($ridee instanceof Player && $rider instanceof Player && $rider->getInventory()->getItemInHand()->equals(VanillaBlocks::HOPPER()->asItem())){
            $chase = new Chase($ridee, $rider, new TerminateCondition(whenGetOff: true, whenTargetDie: true), new ChaseDetail(smoothChase: true));
            $this->chaseapi->start($chase);
        }

    }

}