<?php

namespace famima65536\chaseplayer;

use pocketmine\plugin\PluginBase;

class Loader extends PluginBase{

    public function onLoad()
    {
        ChaseAPI::init($this);
    }

    public function onEnable()
    {
        $this->getServer()->getPluginManager()->registerEvents(new EventListener($this), $this);
    }
}