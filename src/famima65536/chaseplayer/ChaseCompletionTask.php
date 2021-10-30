<?php

namespace famima65536\chaseplayer;

use famima65536\chaseplayer\chase\Chase;
use pocketmine\Player;
use pocketmine\scheduler\Task;

class ChaseCompletionTask extends Task{

    public function __construct(private Chase $chase)
    {
    }

    public function onRun(): void{
        $this->chase->end();
    }

}