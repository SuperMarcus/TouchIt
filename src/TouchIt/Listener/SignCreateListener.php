<?php
namespace TouchIt\Listener;

use pocketmine\event\block\SignChangeEvent;
use pocketmine\event\Listener;
use pocketmine\tile\Sign;
use TouchIt\SignManager;

class SignCreateListener implements Listener{
    /** @var SignManager */
    private $manager;

    private $portal_cash;

    public function __construct(SignManager $manager){
        $this->manager = $manager;
    }

    /**
     * @param SignChangeEvent $event
     */
    public function onSignChange(SignChangeEvent $event){
        if(strpos(strtolower($event->getLine(0)), "touchit") !== false and strpos(strtolower($event->getLine(0)), "touchit") <= 0){
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
            if(strlen(trim($event->getLine(0))) > 6 and strpos($event->getLine(0), "&") !== false){
                switch(SignManager::getType(explode("&", trim($event->getLine(0)))[1])){
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
                        if(isset($this->portal_cash[$name])){
                            $departure = $this->portal_cash[$name];
                            unset($this->portal_cash[$name]);
                            if($departure[0] instanceof \WeakRef and $departure[0]->valid() and $departure[0]->get() instanceof Sign){
                                /** @var Sign $tile */
                                $tile = $departure[0]->get();
                                $event->setLine(0, "[TouchIt]");
                                $event->setLine(1, "----------");
                                $event->setLine(2, $this->manager->getLang("create.process"));
                                $event->setLine(3, "");
                                $tile->setText("[TouchIt]", "----------", $this->manager->getLang("create.process"), "");
                                $this->manager->getProvider()->create([
                                    "type" => SignManager::SIGN_PORTAL,
                                    "data" => [
                                        "id" => 0,//arrival
                                        "name" => $name,
                                        "description" => $description
                                    ]
                                ], $event->getBlock()->getFloorX(), $event->getBlock()->getFloorY(), $event->getBlock()->getFloorZ(), $event->getBlock()->getLevel()->getName());
                                $this->manager->getProvider()->create([
                                    "type" => SignManager::SIGN_PORTAL,
                                    "data" => [
                                        "id" => 1,//departure
                                        "name" => $name,
                                        "target" => [
                                            $event->getBlock()->getFloorX(),
                                            $event->getBlock()->getFloorY(),
                                            $event->getBlock()->getFloorZ(),
                                            $event->getBlock()->getLevel()->getName()
                                        ],
                                        "description" => $departure[1]
                                    ]
                                ], $tile->getFloorX(), $tile->getFloorY(), $tile->getFloorZ(), $tile->getLevel()->getName());
                                $event->getPlayer()->sendMessage(str_replace("{type}", $this->manager->getLang("type.portal"), $this->manager->getLang("create.process.message")));
                                break;
                            }
                        }
                        $this->portal_cash[trim($event->getLine(1))] = [
                            new \WeakRef($event->getBlock()->getLevel()->getTile($event->getBlock())),
                            $description
                        ];
                        $event->setLine(0, "[TouchIt]");
                        $event->setLine(1, "----------");
                        $event->setLine(2, $this->manager->getLang("create.notice.portal"));
                        $event->setLine(3, "");
                        $event->getPlayer()->sendMessage($this->manager->getLang("create.notice.portal.message"));
                        if($this->manager->getConfig()->get("ShowSuggest")){
                            $event->getPlayer()->sendMessage($this->manager->getLang("create.notice.portal.suggest"));
                        }
                        break;
                    case SignManager::SIGN_COMMAND:
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
                        $cmd = str_replace("/", "", ltrim($event->getLine(1)).rtrim($event->getLine(2)));
                        $description = (trim($event->getLine(3)) === "") ? "Run: /".$cmd : trim($event->getLine(3));
                        if($this->manager->getConfig()->get("CreateCheck")){
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
                        $event->setLine(0, "[TouchIt]");
                        $event->setLine(1, "----------");
                        $event->setLine(2, $this->manager->getLang("create.process"));
                        $event->setLine(3, "");
                        $event->getPlayer()->sendMessage(str_replace("{type}", $this->manager->getLang("type.command"), $this->manager->getLang("create.process.message")));
                        $this->manager->getProvider()->create([
                            "type" => SignManager::SIGN_COMMAND,
                            "data" => [
                                "cmd" => $cmd,
                                "description" => $description
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
}