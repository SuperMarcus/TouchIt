<?php
namespace TouchIt\Listener

use pocketmine\event\Listener;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\Server;
use TouchIt\TouchIt;
use TouchIt\SignManager;

class EventListener implements Listener{
    private $touchit, $manager;
    
    public function __construct(TouchIt $touchit, SignManager $manager){
        $this->touchit = $touchit;
        $this->manager = $manager;
    }
    
    public function onBlockPlace(BlockPlaceEvent $event){
        $this->manager->onBlockPlaceEvent($event);
    }
    
    public function onBlockBreak(BlockBreakEvent $event){
        $this->manager->onBlockBreakEvent($event);
    }
    
    public function onPlayerLogin(PlayerLoginEvent $event){
        $this->onUpdateEvent($event);
    }
    
    public function onPlayerRespawn(PlayerRespawnEvent $event){
        $this->onUpdateEvent($event);
    }
    
    public function onPlayerQuit(PlayerQuitEvent $event){
        $this->onUpdateEvent($event);
    }
    
    public function onLevelChange(EntityLevelChangeEvent $event){
        $this->onUpdateEvent($event);
    }
    
    public function onUpdateEvent($event){
        $this->manager->onUpdateEvent($event);
    }
}
?>
