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

class SignManager{
    private $touchit, $config, $database;
    
    public function __construct(TouchIt $touchit, CNFDataProvider &$config, SQLDataProvider &$database){
        $this->touchit = $touchit;
        $this->config = $config;
        $this->database = $database;
    }
    
    public function onBlockBreak(BlockPlaceEvent $event){
        if($event->getBlock()->getID() === Block::WALL_SIGN or $event->getBlock()->getID === Block::SIGN_POST){
            if(($sign = $this->database->getSign($event->getBlock()->position)) !== false){
                if($event->getPlayer()->isOp()){
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
    
    public function onUpdateEvent(Event $event){
        
    }
    
    public function onUpdate(){
        $contents = $this->database->getContents();
        $server = Server::getInstance();
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
                    ($this->config("showCount", true) ? "[".count($sign->getToLevel()->getPlayers())."/".$this->config("maxPeople")."]" : $this->config("informationLine2", "to teleport"))
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
