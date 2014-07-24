<?php
namespace TouchIt;

use TouchIt\TouchIt;
use TouchIt\DataProvider\Provider;
use pocketmine\Server;
use pocketmine\event\Event;
use pocketmine\block\Block;
use pocketmine\tile\Sign;

class SignManager extends {
    private $touchit, $config, $database, $stop;
    
    private $updates;
    
    public function __construct(){
        $this->touchit = TouchIt::getTouchIt();
        $this->stop = false;
        $this->isChoosing = false;
    }
    
    public function addToUpdate($level){
        $this->updates[] = $level;
    }
    
    public function needUpdates(){
        $return = $this->updates;
        $this->updates = [];
        return $return;
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
                $this->check[] = ["position" => $event->getBlock()->position, "player" => $event->getPlayer(), "check" => time()];//Add to new sign check list. Because new api don't have tile.update.
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
}
?>
