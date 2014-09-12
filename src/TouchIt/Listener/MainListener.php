<?php
namespace TouchIt\Listener;

use pocketmine\event\Listener;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\event\block\BlockPlaceEvent;
use pocketmine\event\block\SignChangeEvent;
use pocketmine\block\SignPost;
use pocketmine\block\WallSign;
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
        $block = $event->getBlock();
        if($block instanceof SignPost or $block instanceof WallSign){
            $this->manager->onPlayerTouch($block, $event->getPlayer(), $event);
        }
    }

    /**
     * @param BlockPlaceEvent $event
     */
    public function onBlockPlace(BlockPlaceEvent $event){
        if(($tile = $event->getBlock()->getLevel()->getTile($event->getBlock())) instanceof Sign){
            $this->manager->onBlockPlace($tile);
        }
    }

    /**
     * @param SignChangeEvent $event
     *
     * @priority HIGH
     */
    public function onSignChange(SignChangeEvent $event){
        if(strtolower(trim($event->getLine(0))) == "touchit"){
            $this->manager->onNewSign($event->getPlayer(), $event->getLines(), $event->getBlock());
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