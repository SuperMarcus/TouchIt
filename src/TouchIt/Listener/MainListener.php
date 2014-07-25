<?php
namespace TouchIt\Listener

use pocketmine\event\Listener;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\Server;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\block\BlockPlaceEvent;
use pocketmine\event\player\PlayerLoginEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\event\player\PlayerRespawnEvent;
use pocketmine\event\entity\EntityLevelChangeEvent;
use TouchIt\TouchIt;

class MainListener implements Listener{
    public function onBlockPlace(BlockPlaceEvent $event){
        TouchIt::getManager()->onBlockPlace($event);
    }
    
    public function onBlockBreak(BlockBreakEvent $event){
        TouchIt::getManager()->onBlockBreak($event);
    }
}
?>
