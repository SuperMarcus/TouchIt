<?php
namespace TouchIt\Listener;

use pocketmine\event\block\SignChangeEvent;
use pocketmine\event\Listener;
use TouchIt\SignManager;

class SignCreateListener implements Listener{
    /** @var SignManager */
    private $manager;

    public function __construct(SignManager $manager){
        $this->manager = $manager;
    }

    /**
     * @param SignChangeEvent $event
     */
    public function onSignChange(SignChangeEvent $event){
        $params = explode("&", trim($event->getLine(0)));
        if(count($params) >= 2 and strtolower(trim($params[0])) === "touchit"){
            array_shift($params);
            if(!$event->getPlayer()->hasPermission("touchit.sign.create")){//Check permissions
                $event->getPlayer()->sendMessage($this->manager->getLang("create.warning.permission.message"));
                if($this->manager->getConfig()->get("ShowSuggest")){
                    $event->getPlayer()->sendMessage($this->manager->getLang("create.warning.permission.suggest"));
                }
                $event->setLine(0, $this->manager->getLang("create.warning"));
                $event->setLine(1, "----------");
                $event->setLine(2, $this->manager->getLang("create.warning.permission"));
                $event->setLine(3, "");
                return;
            }
            switch(SignManager::getType($params[0])){
                case SignManager::SIGN_WORLD_TELEPORT:
                    if(trim($event->getLine(1)) == ""){
                        $event->setLine(0, $this->manager->getLang("create.warning"));
                        $event->setLine(1, "----------");
                        $event->setLine(2, $this->manager->getLang("create.warning.level.empty"));
                        $event->setLine(3, "");
                        $event->getPlayer()->sendMessage($this->manager->getLang("create.warning.level.empty.message"));
                        if($this->manager->getConfig()->get("ShowSuggest")){
                            $event->getPlayer()->sendMessage($this->manager->getLang("create.warning.level.empty.suggest"));
                        }
                        break;
                    }
                    if($this->manager->getConfig()->get("CreateCheck")){
                        if(!$this->manager->getServer()->isLevelLoaded(trim($event->getLine(1)))){//Check if level is not loaded
                            $event->setLine(0, $this->manager->getLang("create.warning"));
                            $event->setLine(1, "----------");
                            $event->setLine(2, $this->manager->getLang("create.warning.level.invalid"));
                            $event->setLine(3, "");
                            $event->getPlayer()->sendMessage($this->manager->getLang("create.warning.level.invalid.message"));
                            if($this->manager->getConfig()->get("ShowSuggest")){
                                $event->getPlayer()->sendMessage($this->manager->getLang("create.warning.level.invalid.suggest"));
                            }
                            break;
                        }
                    }
                    $description = "To: ".trim($event->getLine(1));
                    $target = trim($event->getLine(1));
                    if(trim($event->getLine(2)) != "" or trim($event->getLine(3)) != ""){
                        $description = ltrim($event->getLine(2)).rtrim($event->getLine(3));
                    }
                    $this->manager->getProvider()->create([
                        "type" => SignManager::SIGN_WORLD_TELEPORT,
                        "data" => [
                            "description" => $description,
                            "target" => $target,
                        ]
                    ], $event->getBlock()->getFloorX(), $event->getBlock()->getFloorY(), $event->getBlock()->getFloorZ(), $event->getBlock()->getLevel()->getName());
                    $event->setLine(0, "[TouchIt]");
                    $event->setLine(1, "----------");
                    $event->setLine(2, $this->manager->getLang("create.process"));
                    $event->setLine(3, "");
                    $event->getPlayer()->sendMessage(str_replace("{type}", $this->manager->getLang("type.world_teleport"), $this->manager->getLang("create.process.message")));
                    break;
                case SignManager::SIGN_PORTAL:
                    if(count($params) < 2){
                        $event->setLine(0, $this->manager->getLang("create.warning"));
                        $event->setLine(1, "----------");
                        $event->setLine(2, $this->manager->getLang("create.warning.portal-type.empty"));
                        $event->setLine(3, "");
                        $event->getPlayer()->sendMessage($this->manager->getLang("create.warning.portal-type.empty.message"));
                        if($this->manager->getConfig()->get("ShowSuggest")){
                            $event->getPlayer()->sendMessage($this->manager->getLang("create.warning.portal-type.empty.suggest"));
                        }
                    }
                    if(trim($event->getLine(1)) == ""){
                        $event->setLine(0, $this->manager->getLang("create.warning"));
                        $event->setLine(1, "----------");
                        $event->setLine(2, $this->manager->getLang("create.warning.name.empty"));
                        $event->setLine(3, "");
                        $event->getPlayer()->sendMessage($this->manager->getLang("create.warning.name.empty.message"));
                        if($this->manager->getConfig()->get("ShowSuggest")){
                            $event->getPlayer()->sendMessage($this->manager->getLang("create.warning.name.empty.suggest"));
                        }
                        break;
                    }
                    $name = trim($event->getLine(1));
                    $description = (trim($event->getLine(2)) != "" or trim($event->getLine(3)) != "") ? ltrim($event->getLine(2)).rtrim($event->getLine(3)) : "Portal: ".trim($event->getLine(1));
                    switch(trim($params[1])){
                        case "a":
                            $used = 0;
                            foreach($this->manager->getProvider()->getAll() as $sign){
                                if($sign['data']['type'] === SignManager::SIGN_PORTAL and $sign['data']['data']['id'] === 0 and $sign['data']['data']['name'] === $name){
                                    ++$used;
                                }
                            }
                            if($used > 0){
                                $event->setLine(0, $this->manager->getLang("create.warning"));
                                $event->setLine(1, "----------");
                                $event->setLine(2, $this->manager->getLang("create.warning.name.used"));
                                $event->setLine(3, "");
                                $event->getPlayer()->sendMessage($this->manager->getLang("create.warning.name.used.message"));
                                if($this->manager->getConfig()->get("ShowSuggest")){
                                    $event->getPlayer()->sendMessage($this->manager->getLang("create.warning.name.used.suggest"));
                                }
                                break;
                            }
                            $this->manager->getProvider()->create([
                                "type" => SignManager::SIGN_PORTAL,
                                "data" => [
                                    "id" => 0,
                                    "name" => $name,
                                    "description" => $description
                                ]
                            ], $event->getBlock()->getFloorX(), $event->getBlock()->getFloorY(), $event->getBlock()->getFloorZ(), $event->getBlock()->getLevel()->getName());
                            $event->setLine(0, "[TouchIt]");
                            $event->setLine(1, "----------");
                            $event->setLine(2, $this->manager->getLang("create.process"));
                            $event->setLine(3, "");
                            $event->getPlayer()->sendMessage(str_replace("{type}", $this->manager->getLang("type.portal"), $this->manager->getLang("create.process.message")));
                            break;
                        case "d":
                            $arrival = null;
                            foreach($this->manager->getProvider()->getAll() as $sign){
                                if($sign['data']['type'] === SignManager::SIGN_PORTAL and $sign['data']['data']['id'] === 0 and $sign['data']['data']['name'] === $name){
                                    $arrival = $sign['position'];
                                }
                            }
                            if($arrival === null){
                                $event->setLine(0, $this->manager->getLang("create.warning"));
                                $event->setLine(1, "----------");
                                $event->setLine(2, $this->manager->getLang("create.warning.name.unset"));
                                $event->setLine(3, "");
                                $event->getPlayer()->sendMessage(str_replace("{name}", $name, $this->manager->getLang("create.warning.name.unset.message")));
                                if($this->manager->getConfig()->get("ShowSuggest")){
                                    $event->getPlayer()->sendMessage($this->manager->getLang("create.warning.name.unset.suggest"));
                                }
                                break;
                            }
                            $this->manager->getProvider()->create([
                                "type" => SignManager::SIGN_PORTAL,
                                "data" => [
                                    "id" => 1,
                                    "name" => $name,
                                    "target" => $arrival,
                                    "description" => $description
                                ]
                            ], $event->getBlock()->getFloorX(), $event->getBlock()->getFloorY(), $event->getBlock()->getFloorZ(), $event->getBlock()->getLevel()->getName());
                            $event->setLine(0, "[TouchIt]");
                            $event->setLine(1, "----------");
                            $event->setLine(2, $this->manager->getLang("create.process"));
                            $event->setLine(3, "");
                            $event->getPlayer()->sendMessage(str_replace("{type}", $this->manager->getLang("type.portal"), $this->manager->getLang("create.process.message")));
                            break;
                        default:
                            $event->setLine(0, $this->manager->getLang("create.warning"));
                            $event->setLine(1, "----------");
                            $event->setLine(2, $this->manager->getLang("create.warning.portal-type.unknown"));
                            $event->setLine(3, "");
                            $event->getPlayer()->sendMessage($this->manager->getLang("create.warning.portal-type.unknown.message"));
                            if($this->manager->getConfig()->get("ShowSuggest")){
                                $event->getPlayer()->sendMessage($this->manager->getLang("create.warning.portal-type.unknown.suggest"));
                            }
                    }
                    break;
                case SignManager::SIGN_COMMAND:
                    $opts = ["operator" => false, "preloaded" => false];
                    if(trim($event->getLine(1)) == "" and trim($event->getLine(2))){
                        $event->setLine(0, $this->manager->getLang("create.warning"));
                        $event->setLine(1, "----------");
                        $event->setLine(2, $this->manager->getLang("create.warning.command.empty"));
                        $event->setLine(3, "");
                        $event->getPlayer()->sendMessage($this->manager->getLang("create.warning.command.empty.message"));
                        if($this->manager->getConfig()->get("ShowSuggest")){
                            $event->getPlayer()->sendMessage($this->manager->getLang("create.warning.command.empty.suggest"));
                        }
                        break;
                    }
                    foreach($params as $param){
                        switch(trim($param)){
                            case "o":
                            case "a":
                                $opts['operator'] = true;
                                $event->getPlayer()->sendMessage($this->manager->getLang("create.notice.command.operator"));
                                break;
                            case "p":
                                $opts['preloaded'] = true;
                                $event->getPlayer()->sendMessage($this->manager->getLang("create.notice.command.preloaded"));
                        }
                    }
                    $cmd = str_replace("/", "", ltrim($event->getLine(1)).rtrim($event->getLine(2)));
                    $description = (trim($event->getLine(3)) === "") ? "Run: /".$cmd : trim($event->getLine(3));
                    if(!$opts['preloaded'] and $this->manager->getConfig()->get("CreateCheck")){
                        if($this->manager->getServer()->getCommandMap()->getCommand(explode(" ", $cmd)[0]) === null){
                            $event->setLine(0, $this->manager->getLang("create.warning"));
                            $event->setLine(1, "----------");
                            $event->setLine(2, $this->manager->getLang("create.warning.command.unexists"));
                            $event->setLine(3, "");
                            $event->getPlayer()->sendMessage($this->manager->getLang("create.warning.command.unexists.message"));
                            if($this->manager->getConfig()->get("ShowSuggest")){
                                $event->getPlayer()->sendMessage($this->manager->getLang("create.warning.command.unexists.suggest"));
                            }
                            break;
                        }
                    }
                    if($opts['preloaded']){
                        $this->manager->saveDefaultPreloadedFile($cmd);
                    }
                    $event->setLine(0, "[TouchIt]");
                    $event->setLine(1, "----------");
                    $event->setLine(2, $this->manager->getLang("create.process"));
                    $event->setLine(3, "");
                    $event->getPlayer()->sendMessage(str_replace("{type}", $this->manager->getLang("type.command"), $this->manager->getLang("create.process.message")));
                    $this->manager->getProvider()->create([
                        "type" => SignManager::SIGN_COMMAND,
                        "data" => [
                            "cmd" => $cmd,
                            "description" => $description,
                            "option" => $opts
                        ]
                    ], $event->getBlock()->getFloorX(), $event->getBlock()->getFloorY(), $event->getBlock()->getFloorZ(), $event->getBlock()->getLevel()->getName());
                    break;
                case SignManager::SIGN_UNKNOWN:
                    $event->setLine(0, "[TouchIt]");
                    $event->setLine(1, "----------");
                    $event->setLine(2, $this->manager->getLang("create.warning.type.unknown"));
                    $event->setLine(3, "");
                    $event->getPlayer()->sendMessage($this->manager->getLang("create.warning.type.unknown.message"));
                    if($this->manager->getConfig()->get("ShowSuggest")){
                        $event->getPlayer()->sendMessage($this->manager->getLang("create.warning.type.unknown.suggest"));
                    }
            }
        }
    }
}