<?php

namespace famima65536\chaseplayer\command;

use famima65536\chaseplayer\chase\Chase;
use famima65536\chaseplayer\chase\ChaseDetail;
use famima65536\chaseplayer\chase\TerminateCondition;
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

        if(count($args) >= 8){
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
        $distance = isset($args[2]) ? intval($args[2]) : null;
        $rotation = isset($args[3]) ? intval($args[3]) : null;
        $yaw = isset($args[4]) ? intval($args[4]) : null;

        $smoothChase = isset($args[5]) ? boolval($args[5]) : true;
        $force = isset($args[6]) ? boolval($args[6]) : false;
        $condition = new TerminateCondition(
            whenGetOff: $chasetime === null,
            chaseTime: $chasetime
        );
        $chaseDetail = new ChaseDetail(
            distance: $distance,
            rotationOffset: $rotation,
            yawOffset: $yaw,
            smoothChase: $smoothChase
        );
        $chase = new Chase($target, $sender,  $condition, $chaseDetail);
        ChaseAPI::getInstance()->start($chase, $force);

    }

}