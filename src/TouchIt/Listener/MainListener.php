<?php
namespace TouchIt\Listener;

use pocketmine\event\Listener;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\block\BlockPlaceEvent;
use TouchIt\TouchIt;

/** Main listener of TouchIt */
class MainListener implements Listener{
    /**
     * @param BlockPlaceEvent $event
     */
    public function onBlockPlace(BlockPlaceEvent $event){
        TouchIt::getManager()->onBlockPlace($event);
    }

    /**
     * @param BlockBreakEvent $event
     */
    public function onBlockBreak(BlockBreakEvent $event){
        TouchIt::getManager()->onBlockBreak($event);
    }
}
?>