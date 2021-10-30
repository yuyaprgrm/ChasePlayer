<?php

namespace famima65536\chaseplayer\command;

use famima65536\chaseplayer\chase\Chase;
use famima65536\chaseplayer\ChaseAPI;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\command\utils\InvalidCommandSyntaxException;
use pocketmine\player\Player;

class ChaseCommand extends Command{

    public function execute(CommandSender $sender, string $commandLabel, array $args)
    {
        if(!$sender instanceof Player){
            $sender->sendMessage("Cannot use chase command from console");
            return true;
        }

        if(!$this->testPermission($sender)){
			return true;
		}

        if(count($args) >= 4){
            throw new InvalidCommandSyntaxException();
        }
        if(count($args) === 0){
            throw new InvalidCommandSyntaxException();
        }
        $targetName = $args[0];
        $target = $sender->getServer()->getPlayerByPrefix($targetName);
        if($target === null){
            $sender->sendMessage("Target is not found in server");
            return;
        }
        $chasetime = isset($args[1]) ? intval($args[1]) : null;
        $force = isset($args[2]) ? boolval($args[2]) : false;
        $chase = new Chase($target, $sender,  $chasetime);
        ChaseAPI::getInstance()->start($chase, $force);

    }

}