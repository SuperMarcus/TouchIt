<?php
namespace touchit\listener;

use pocketmine\event\block\SignChangeEvent;
use pocketmine\event\Listener;
use pocketmine\nbt\tag\Compound;
use pocketmine\nbt\tag\Int;
use pocketmine\nbt\tag\String;
use pocketmine\utils\TextFormat;
use touchit\sign\CommandSign;
use touchit\sign\PortalSign;
use touchit\sign\WorldTeleportSign;
use touchit\SignManager;

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
                $event->getPlayer()->sendTip($this->manager->getTranslator()->translateString("touchit.build.no_permission.message"));
                $event->setLine(0, $this->manager->getTranslator()->translateString("touchit.build.failed"));
                $event->setLine(1, "----------");
                $event->setLine(2, $this->manager->getTranslator()->translateString("touchit.build.no_permission"));
                $event->setLine(3, "");
                return;
            }
            $typeParam = array_shift($params);
            switch(SignManager::getType($typeParam)){
                case SignManager::SIGN_WORLD_TELEPORT:
                    if(trim($event->getLine(1)) == ""){
                        $event->setLine(0, $this->manager->getTranslator()->translateString("touchit.build.failed"));
                        $event->setLine(1, "----------");
                        $event->setLine(2, $this->manager->getTranslator()->translateString("touchit.build.miss_args"));
                        $event->setLine(3, "");
                        $event->getPlayer()->sendTip($this->manager->getTranslator()->translateString("touchit.build.miss_args.message"));
                        break;
                    }
                    if($this->manager->getConfig()->get("check-settings")){
                        if(!$this->manager->getServer()->isLevelLoaded(trim($event->getLine(1)))){//Check if level is not loaded
                            $event->setLine(0, $this->manager->getTranslator()->translateString("touchit.build.failed"));
                            $event->setLine(1, "----------");
                            $event->setLine(2, $this->manager->getTranslator()->translateString("touchit.build.invalid"));
                            $event->setLine(3, "");
                            $event->getPlayer()->sendTip($this->manager->getTranslator()->translateString("touchit.build.invalid.message"));
                            break;
                        }
                    }
                    $description = "To: ".trim($event->getLine(1));
                    $target = trim($event->getLine(1));

                    if(trim($event->getLine(2)) != "" or trim($event->getLine(3)) != ""){
                        $description = ltrim($event->getLine(2)).rtrim($event->getLine(3));
                    }

                    $this->manager->createTile([
                            ["setDescription", [$description]],
                            ["setTargetLevel", [$target]],
                        ],
                        WorldTeleportSign::ID,
                        $event->getBlock()->getLevel()->getChunk($event->getBlock()->getX() >> 4, $event->getBlock()->getZ() >> 4),
                        new Compound("", [
                            "id" => new String("id", WorldTeleportSign::ID),
                            "x" => new Int("x", $event->getBlock()->getX()),
                            "y" => new Int("y", $event->getBlock()->getY()),
                            "z" => new Int("z", $event->getBlock()->getZ()),
                            "Text1" => new String("Text1", ""),
                            "Text2" => new String("Text2", ""),
                            "Text3" => new String("Text3", ""),
                            "Text4" => new String("Text4", "")
                        ]));

                    $event->getPlayer()->sendTip($this->manager->getTranslator()->translateString("touchit.build", [$this->manager->getTranslator()->translateString("touchit.type.world_teleport")]));
                    break;
                case SignManager::SIGN_PORTAL:
                    $portal = trim($event->getLine(1));
                    $pair = null;
                    $arrivalOnly = isset($params[0]) ? (trim($params[0]) === 'a') : false;
                    $departureOnly = isset($params[0]) ? (trim($params[0]) === 'd') : false;
                    $description = (trim($event->getLine(2)) != "" or trim($event->getLine(3)) != "") ? ltrim($event->getLine(2)).rtrim($event->getLine(3)) : "Portal: ".$portal;

                    if($portal === ""){
                        $event->setLine(0, $this->manager->getTranslator()->translateString("touchit.build.failed"));
                        $event->setLine(1, "----------");
                        $event->setLine(2, $this->manager->getTranslator()->translateString("touchit.build.miss_args"));
                        $event->setLine(3, "");
                        $event->getPlayer()->sendTip($this->manager->getTranslator()->translateString("touchit.build.miss_args.message"));
                    }else{
                        foreach($this->manager->getServer()->getLevels() as $level){//Search for pair
                            foreach($level->getTiles() as $tile){
                                if($tile instanceof PortalSign){
                                    if($tile->getPortalName() === $portal){
                                        if($tile->isPaired() or ($departureOnly and $tile->isDepartureOnly()) or ($arrivalOnly and $tile->isArrivalOnly())){
                                            $event->setLine(0, $this->manager->getTranslator()->translateString("touchit.build.failed"));
                                            $event->setLine(1, "----------");
                                            $event->setLine(2, $this->manager->getTranslator()->translateString("touchit.build.invalid"));
                                            $event->setLine(3, "");
                                            $event->getPlayer()->sendTip($this->manager->getTranslator()->translateString("touchit.build.invalid.message"));
                                            return;
                                        }else if($pair !== null){//What happened?!?!
                                            $event->getPlayer()->sendTip(TextFormat::RED."The sign could not be created because more than 1 pair was found");
                                            $event->getPlayer()->sendPopup(TextFormat::YELLOW."Please provide as much as you can on the plugin page of TouchIt");
                                            return;
                                        }else {
                                            $pair = $tile;
                                        }
                                    }
                                }
                            }
                        }

                        switch(true){//To show the portal type (one-way or two-way)
                            case $arrivalOnly:
                                $event->getPlayer()->sendPopup($this->manager->getTranslator()->translateString("touchit.build.arrival"));
                                break;
                            case $departureOnly:
                                $event->getPlayer()->sendPopup($this->manager->getTranslator()->translateString("touchit.build.departure"));
                                break;
                            default:
                                $event->getPlayer()->sendPopup($this->manager->getTranslator()->translateString("touchit.build.two-way"));
                        }

                        $this->manager->createTile([
                                ["setDescription", [$description]],
                                ["setPortalName", [$portal]],
                                ["setArrivalOnly", [$arrivalOnly]],
                                ["setDepartureOnly", [$departureOnly]]
                            ],
                            PortalSign::ID,
                            $event->getBlock()->getLevel()->getChunk($event->getBlock()->getX() >> 4, $event->getBlock()->getZ() >> 4),
                            new Compound("", [
                                "id" => new String("id", PortalSign::ID),
                                "x" => new Int("x", $event->getBlock()->getX()),
                                "y" => new Int("y", $event->getBlock()->getY()),
                                "z" => new Int("z", $event->getBlock()->getZ()),
                                "Text1" => new String("Text1", ""),
                                "Text2" => new String("Text2", ""),
                                "Text3" => new String("Text3", ""),
                                "Text4" => new String("Text4", "")
                            ]));

                        $event->getPlayer()->sendTip($this->manager->getTranslator()->translateString("touchit.build", [$this->manager->getTranslator()->translateString("touchit.type.portal")]));
                    }
                    break;
                case SignManager::SIGN_COMMAND:
                    $opts = ["operator" => false, "preloaded" => false];
                    if(trim($event->getLine(1)) == "" and trim($event->getLine(2))){
                        $event->setLine(0, $this->manager->getTranslator()->translateString("touchit.build.failed"));
                        $event->setLine(1, "----------");
                        $event->setLine(2, $this->manager->getTranslator()->translateString("touchit.build.miss_args"));
                        $event->setLine(3, "");
                        $event->getPlayer()->sendTip($this->manager->getTranslator()->translateString("touchit.build.miss_args.message"));
                        break;
                    }
                    foreach($params as $param){
                        switch(trim($param)){
                            case "o":
                            case "a":
                                $opts['operator'] = true;
                                $event->getPlayer()->sendMessage($this->manager->getTranslator()->translateString("touchit.build.run_as_op"));
                                break;
                            case "p":
                                $opts['preloaded'] = true;
                                $event->getPlayer()->sendMessage($this->manager->getTranslator()->translateString("touchit.build.preloaded"));
                        }
                    }
                    $cmd = str_replace("/", "", ltrim($event->getLine(1)).rtrim($event->getLine(2)));
                    $description = (trim($event->getLine(3)) === "") ? "Run: /".$cmd : trim($event->getLine(3));
                    if(!$opts['preloaded'] and $this->manager->getConfig()->get("check-settings")){
                        if($this->manager->getServer()->getCommandMap()->getCommand(explode(" ", $cmd)[0]) === null){
                            $event->setLine(0, $this->manager->getTranslator()->translateString("touchit.build.failed"));
                            $event->setLine(1, "----------");
                            $event->setLine(2, $this->manager->getTranslator()->translateString("touchit.build.invalid"));
                            $event->setLine(3, "");
                            $event->getPlayer()->sendTip($this->manager->getTranslator()->translateString("touchit.build.invalid.message"));
                            break;
                        }
                    }
                    if($opts['preloaded']){
                        $this->manager->saveDefaultPreloadedFile($cmd);
                    }
                    $event->getPlayer()->sendTip($this->manager->getTranslator()->translateString("touchit.build", [$this->manager->getTranslator()->translateString("type.command")]));
                    $this->manager->createTile([
                            ["setDescription", [$description]],
                            [$opts['preloaded'] ? "setCommandStore" : "setCommand", [$cmd]],
                            ["setRunAsOperator", [$opts['operator']]],
                            ["setPreloaded", [$opts['preloaded']]]
                        ],
                        CommandSign::ID,
                        $event->getBlock()->getLevel()->getChunk($event->getBlock()->getX() >> 4, $event->getBlock()->getZ() >> 4),
                        new Compound("", [
                            "id" => new String("id", CommandSign::ID),
                            "x" => new Int("x", $event->getBlock()->getX()),
                            "y" => new Int("y", $event->getBlock()->getY()),
                            "z" => new Int("z", $event->getBlock()->getZ()),
                            "Text1" => new String("Text1", ""),
                            "Text2" => new String("Text2", ""),
                            "Text3" => new String("Text3", ""),
                            "Text4" => new String("Text4", "")
                        ]));
                    break;
                case SignManager::SIGN_UNKNOWN:
                    $event->setLine(0, $this->manager->getTranslator()->translateString("touchit.build.failed"));
                    $event->setLine(1, "----------");
                    $event->setLine(2, $this->manager->getTranslator()->translateString("touchit.build.unknown"));
                    $event->setLine(3, "");
                    $event->getPlayer()->sendMessage($this->manager->getTranslator()->translateString("touchit.build.unknown.message", [$typeParam]));
            }
        }
    }
}