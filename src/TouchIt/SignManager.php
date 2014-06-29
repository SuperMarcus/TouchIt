<?php
namespace TouchIt;

use TouchIt\TouchIt;
use TouchIt\Exchange\signInfo;
use TouchIt\DataProvider\CNFDataProvider;
use TouchIt\DataProvider\SQLDataProvider;
use TouchIt\Event\UpdateSignEvent;
use pocketmine\Server;
use pocketmine\event\Event;
use pocketmine\block\Block;

class SignManager extends \Thread{
    private $touchit, $config, $database, $stop;
    
    public function __construct(TouchIt $touchit, CNFDataProvider &$config, SQLDataProvider &$database){
        $this->touchit = $touchit;
        $this->config = $config;
        $this->database = $database;
        $this->stop = false;
    }
    
    public function onCommand(CommandSender $sender, Command $command, $label, array $args){
        switch($args[0]){
            case "update":
                $this->update();
                $sender->sendMessage("[TouchIt] Updating...");
                return true;
        }
        return false
    }
    
    public function run(){
        if($this->isRunning())return;
        while(!$this->stop){
            $this->checkNewSign();
            $this->onUpdate();
            $this->wait(((int) $this->config("ticks", 10)) * 100);
        }
    }
    
    public function checkNewSign(){
        if(count($this->check) >== 0){
            foreach($this->check as $key => $data){
                $position = $data['position'];
                if(($tile = $position->getLevel()->getTile($position)) !== false and $tile instanceof Sign){
                    $text = $tile->getText();
                    if(trim(strtolower($text[0])) !== "touchit"){
                        if($text[0] == "" and $text[1] == "" and $text[2] == "" and $text[3] == ""){
                            continue;
                        }
                        unset($this->check[$key]);
                        continue;
                    }
                    if(!Server::getInstance()->isLevelLoaded(trim($text[2])) and $this->config("checkLevel", true)){//To level not loaded
                        if($data['player']->isOnline()){
                            $data['player']->sendMessage("[Touchit] Level \"".trim($text[2])."\" is not loaded.");
                            $tile->setText("[WARNING]", "----------", "Level ".trim($text[2]), "not loaded");
                        }
                        unset($this->check[$key]);
                        continue;
                    }
                    if($data['player']->isOnline()){
                        $data['player']->sendMessage("[Touchit] Done!");
                    }
                    $tile->setText("* * *", "* * *", "* * *", "* * *");
                    $this->database->addSign($tile);
                    unset($this->check[$key]);
                }
            }
        }
    }
    
    public function stop(){
        $this->stop = true;
        $this->update();
    }
    
    public function onBlockPlace(BlockPlaceEvent $event){
        if($event->getBlock()->getID() === Block::WALL_SIGN or $event->getBlock()->getID === Block::SIGN_POST){
            if(($sign = $this->database->getSign($event->getBlock()->position)) !== false){
                if(!$sign->isToLevelLoaded()){
                    $event->getPlayer()->sendMessage("[TouchIt] This world is not open.");
                }elseif(!$event->getPlayer()->isOp() and (count($sign->getToLevel()->getPlayers()) >== (int) $this->config("maxPeople", 20))){
                    $event->getPlayer()->sendMessage("[TouchIt] Level is full!");
                }else{
                    $event->getPlayer()->sendMessage("[TouchIt] Teleporting to ".$sign->getToLevel(true));
                    $event->getPlayer()->teleport($sign->getToLevel()->getSpawnLocation());
                }
                $event->setCancelled();
            }elseif($event->getPlayer()->isOp() or $this->config("allowPlayerBuild", false)){
                $this->check[] = ["position" => $event->getBlock()->position, "player" => $event->getPlayer()];//Add to new sign check list. Because new api don't have tile.update.
            }
        }
    }
    
    public function onBlockBreak(BlockBreakEvent $event){
        if($event->getBlock()->getID() === Block::WALL_SIGN or $event->getBlock()->getID === Block::SIGN_POST){
            if(($sign = $this->database->getSign($event->getBlock()->position)) !== false){
                if($event->getPlayer()->isOp() or $this->config("allowPlayerBreak", false)){
                    $event->getPlayer()->sendMessage("[TouchIt] This sign has been delete.");
                    $this->touchit->getLogger()->debug("[TouchIt] A teleport sign has been delete. (ID: ".$sign->getId().")");
                    $this->database->removeSign($event->getBlock()->position);
                }else{
                    $event->getPlayer()->sendMessage("[TouchIt] You can not break this teleport sign.");
                    $event->setCancelled();
                }
            }
        }
    }
    
    public function update(){
        if($this->isWaiting())$this->notify();
    }
    
    public function onUpdateEvent(Event $event){
        $this->update();
    }
    
    public function onUpdate(){
        $contents = $this->database->getContents();
        while($sign = $contents->getNext()){
            if(!$sign->isFromLevelLoaded()){
                $this->touchit->getLogger()->debug("[TouchIt] Teleport sign: ".$sign->getId()." Has not been update. (Level: ".$sign->getFromLevel(true)." Not Loaded)");
                continue;
            }
            if(!$sign->isToLevelLoaded()){
                $this->touchit->getLogger()->debug("[TouchIt] Teleport sign: ".$sign->getId()." Updated with an error. (Target level: ".$sign->getToLevel(true)." Not Loaded)");
                $tile = $sign->getTile();
                if($tile instanceof Sign){
                    $tile->setText("[".$this->config->get("name", "Teleport")."]", "NOT OPEN", ($this->config->get("showCount", false) ? "* * *" : $this->config->get("informationLine1", "Choose")), ($this->config->get("showCount", false) ? "* * *" : $this->config->get("informationLine2", "onther level")));
                }
                continue;
            }
            $tile = $sign->getTile();
            if($tile instanceof Sign){
                Server::getInstance()->getPluginManager()->callEvent($event = new UpdateSignEvent($this->touchit, $sign, array(
                    "[".$this->config("name")."]",
                    $sign->getDescription(),
                    ($this->config("showCount", true) ? "Players count" : $this->config("informationLine1", "Tap sign")),
                    ($this->config("showCount", true) ? "[".min(count($sign->getToLevel()->getPlayers()), (int) $this->config("maxPeople"))."/".$this->config("maxPeople", 20)."]" : $this->config("informationLine2", "to teleport"))
                )));
                if($event->isCancelled()){
                    $this->touchit->getLogger()->debug("[TouchIt] An update has been cancelled by event.");
                    continue;
                }
                $text = $event->getText();
                $tile->setText($event[0], $event[1], $event[2], $event[3]);
            }else{
                $this->touchit->getLogger()->debug("[TouchIt] An non-existent sign has been found in database. (ID: ".$sign->getId().")");
                if($this->config("autoDeleteSign", true)){
                    $this->database->exec("DELETE FROM sign WHERE id = ".$sign->getId());
                    if($sign->hasDescription()){
                        $this->database->exec("DELETE FROM description WHERE id = ".$sign->getId());
                    }
                    $contents->delete($sign->getId());
                }
            }
        }
    }
}
?>
