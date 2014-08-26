<?php
namespace TouchIt\Listener;

use pocketmine\event\Listener;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\block\PlayerInteractEvent;
use pocketmine\tile\Sign;
use TouchIt\SignManager;
use TouchIt\TouchIt;

/** Main listener of TouchIt */
class MainListener implements Listener{
    private $manager;
    private $plugin;

    public function __construct(SignManager $manager, TouchIt $plugin){
        $this->manager = $manager;
        $this->plugin = $plugin;
    }

    /**
     * @param PlayerInteractEvent $event
     */
    public function onPlayerInteract(PlayerInteractEvent $event){
        if($event->getBlock()->getLevel()->getTile($event->getBlock()) instanceof Sign){
            $event->setCancelled();
            $this->manager->onPlayerTouch($event->getBlock(), $event->getPlayer());
        }
    }

    /**
     * @param BlockBreakEvent $event
     */
    public function onBlockBreak(BlockBreakEvent $event){
        if($event->getBlock()->getLevel()->getTile($event->getBlock()) instanceof Sign){
            $this->manager->onBlockBreak($event);
        }
    }
}
?>