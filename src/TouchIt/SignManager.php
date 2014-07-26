<?php
namespace TouchIt;

use TouchIt\TouchIt;
use TouchIt\DataProvider\Provider;
use pocketmine\Server;
use pocketmine\event\Event;
use pocketmine\block\Block;
use pocketmine\tile\Sign;
use pocketmine\utils\TextFormat;
use pocketmine\level\Level;

class SignManager{
    private $touchit, $config, $database, $stop;
    
    private $updates, $announcement, $bcoffset;
    
    public function __construct(){
        $this->touchit = TouchIt::getTouchIt();
        $this->stop = false;
        $this->isChoosing = false;
        $this->announcement = "";
        $this->nextAnnouncement();
    }
    
    public function onDisable(){
        $this->announcement = "";
    }
    
    public function addToUpdate($level){
        $this->updates[] = $level;
    }
    
    public function needUpdates($type){
        $signs = [];
        switch($type){
            case TouchIt::SIGN_TELEPORT:
                $signs = $this->updates;
                $this->updates = [];
            default:
                $signs = TouchIt::getDataProvider()->getByType($type);
        }
        return $signs;
    }
    
    public function getAnnouncement(){
        return $this->announcement;
    }
    
    public function nextAnnouncement(){
        if(!($fp = @fopen(TouchIt::getTouchIt()->getDataFolder()."announcement.txt", "r+"))){
            @fclose($fp);
            if($this->announcement == "")$this->announcement = TouchIt::getLang("sign.boardcase.unavailable");
            return;
        }
        if($this->bcoffset !== 0){
            fseek($fp, $this->bcoffset);
            if(feof($fp)){
                fseek($fp, 0);
            }
        }
        $this->announcement = fgets($fp);
        $this->bcoffset = ftell($fp);
        @fclose($fp);
    }
    
    public function onBlockPlace(BlockPlaceEvent $event){
        if($event->getBlock()->getID() === Block::WALL_SIGN or $event->getBlock()->getID === Block::SIGN_POST){
            if(TouchIt::getDataProvider()->exists($event->getBlock())){
                $info = TouchIt::getDataProvider()->get($event->getBlock());
                switch($info['type']){
                    case TouchIt::SIGN_TELEPORT:
                        if(($target = Server::getInstance()->getLevelByName($info['target'])) !== null and $target instanceof Level){
                            $event->getPlayer()->sendMessage(TextFormat::DARK_GREEN."[TouchIt] ".TouchIt::getLang("sign.teleport.running").$target->getName());
                            $event->getPlayer()->teleport($target->getSpawnLocation());
                        }else{
                            $event->getPlayer()->sendMessage(TextFormat::YELLOW."[TouchIt] ".TouchIt::getLang("sign.teleport.notopen"));
                        }
                    case TouchIt::SIGN_BOARDCASE:
                        $event->getPlayer()->sendMessage(TextFormat::GOLD."[".TouchIt::getLang("sign.boardcase.title")."] ".$this->getAnnouncement());
                    case TouchIt::SIGN_COMMAND:
                        Server::getInstance()->dispatchCommand($event->getPlayer(), $info['command']);
                }
                $event->setCancelled();
            }
        }
    }
    
    public function onBlockBreak(BlockBreakEvent $event){
        if($event->getBlock()->getID() === Block::WALL_SIGN or $event->getBlock()->getID === Block::SIGN_POST){
            if(TouchIt::getDataProvider()->exists($event->getBlock())){
                if($event->getPlayer()->hasPermission("touchit.sign.break")){
                    TouchIt::getDataProvider()->remove($event->getBlock());
                    $event->getPlayer()->sendMessage(TextFormat::DARK_GREEN."[TouchIt] ".TouchIt::getLang("sign.destroy.done"));
                }else{
                    $event->getPlayer()->sendMessage(TextFormat::RED."[TouchIt] ".TouchIt::getLang("sign.destroy.permission"));
                    $event->setCancelled();
                }
            }
        }
    }
}
?>
