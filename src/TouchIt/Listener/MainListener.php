<?php
namespace TouchIt\Listener;

use pocketmine\event\Listener;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\block\BlockPlaceEvent;
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
