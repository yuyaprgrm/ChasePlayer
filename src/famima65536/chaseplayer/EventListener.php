<?php

namespace famima65536\chaseplayer;

use famima65536\chaseplayer\chase\Chase;
use pocketmine\entity\Entity;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\event\server\DataPacketReceiveEvent;
use pocketmine\network\mcpe\protocol\ActorEventPacket;
use pocketmine\network\mcpe\protocol\BatchPacket;
use pocketmine\network\mcpe\protocol\DataPacket;
use pocketmine\network\mcpe\protocol\InteractPacket;
use pocketmine\network\mcpe\protocol\PlayerActionPacket;
use pocketmine\network\mcpe\protocol\PlayerInputPacket;
use pocketmine\network\mcpe\protocol\SetActorLinkPacket;
use pocketmine\network\mcpe\protocol\types\EntityLink;
use pocketmine\Player;
use pocketmine\plugin\PluginBase;
use raklib\protocol\Packet;

class EventListener implements Listener{

    private ChaseAPI $chaseapi;

    public function __construct(PluginBase $plugin)
    {
        $this->chaseapi = ChaseAPI::getInstance();
    }

    public function onQuit(PlayerQuitEvent $event){
        $player = $this->chaseapi->terminate($event->getPlayer());
    }

    public function onPacket(DataPacketReceiveEvent $event){
        $pk = $event->getPacket();
        if($pk instanceof InteractPacket && $pk->action === InteractPacket::ACTION_LEAVE_VEHICLE){
            $this->chaseapi->handleGetOff($event->getPlayer());
        }
    }

}