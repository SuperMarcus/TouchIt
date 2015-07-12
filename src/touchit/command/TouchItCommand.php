<?php
namespace touchit\command;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use touchit\SignManager;

class TouchItCommand extends Command{
    /** @var SignManager */
    private $manager;

    public function __construct(SignManager $manager){
        $this->manager = $manager;
        $this->setPermission("touchit.command");
        parent::__construct("touchit", "TouchIt commands", "/touchit <update|portal>", ["touch-it"]);
    }

    public function execute(CommandSender $sender, $commandLabel, array $args){
        if(isset($args[0])){
            switch(strtolower(trim($args[0]))){
                case "update":
                    $time = microtime(true);
                    $sender->sendMessage($this->manager->getLang("command.update.start"));
                    $this->manager->manuallyUpdate();
                    $sender->sendMessage(str_replace("{time}", round(microtime(true) - $time, 4), $this->manager->getLang("command.update.stop")));
                    break;
                case "portal":
                    $this->sendPortals($sender, (isset($args[1]) ? $args[1] : null));
                    break;
            }
        }
    }

    public function sendPortals(CommandSender $sender, $name = null){
        if($name){
            $info = [
                [],//Departures
                null//Arrival
            ];
            foreach($this->manager->getProvider()->getAll() as $sign){
                if($sign['data']['type'] === SignManager::SIGN_PORTAL and $sign['data']['data']['name'] === $name){
                    if($sign['data']['data']['id'] === 0)$info[1] = $sign['position']['x']." ".$sign['position']['y']." ".$sign['position']['z']." ".$sign['position']['level'];
                    else $info[0][] = $sign['position']['x']." ".$sign['position']['y']." ".$sign['position']['z']." ".$sign['position']['level'];
                }
            }
            if($info[1] === null){
                $sender->sendMessage(str_replace("{name}", $name, $this->manager->getLang("command.portal.search.no")));
            }else{
                $sender->sendMessage("-".$this->manager->getLang("type.portal").": \"".$name."\"-");
                $sender->sendMessage("Arrival: ".$info[1]);
                $sender->sendMessage("Departures:");
                foreach($info[0] as $t => $sign){
                    $sender->sendMessage("[".++$t."] ".$sign);
                }
            }
        }else{
            $portals = [];
            foreach($this->manager->getProvider()->getAll() as $sign){
                if($sign['data']['type'] === SignManager::SIGN_PORTAL and $sign['data']['data']['id'] === 0){
                    $portals[] = $sign['data']['data']['name'];
                }
            }
            if(count($portals) > 0){
                $sender->sendMessage($this->manager->getLang("command.portal.all"));
                foreach($portals as $c => $p){
                    $sender->sendMessage("[".++$c."] ".$p);
                }
            }else{
                $sender->sendMessage($this->manager->getLang("command.portal.none"));
            }
        }
    }
}