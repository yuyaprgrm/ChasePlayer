<?php

namespace famima65536\chaseplayer;

use famima65536\chaseplayer\command\ChaseCommand;
use pocketmine\plugin\PluginBase;

class Loader extends PluginBase{

    public function onLoad(): void
    {
        ChaseAPI::init($this);
    }

    public function onEnable(): void
    {
        $this->getServer()->getPluginManager()->registerEvents(new EventListener, $this);
        $this->getServer()->getCommandMap()->register("ChasePlayer-Command", new ChaseCommand(
            "chase",
            "chase target player view",
            "/chase [target:string] <chasetime:int> <force:bool>"
        ));
    }
}