<?php
namespace touchit\listener;

use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\Listener;
use pocketmine\tile\Sign;
use touchit\SignManager;

class SignDestroyListener implements Listener{
    /** @var SignManager */
    private $manager;

    public function __construct(SignManager $manager){
        $this->manager = $manager;
    }

    /**
     * @param BlockBreakEvent $event
     */
    public function onBlockDestroy(BlockBreakEvent $event){
        if($this->manager->getProvider()->exists($event->getBlock()->getFloorX(), $event->getBlock()->getFloorY(), $event->getBlock()->getFloorZ(), $event->getBlock()->getLevel()->getName())){
            if($event->getPlayer()->hasPermission("touchit.sign.destroy")){
                $event->getPlayer()->sendMessage($this->manager->getLang("event.destroy.load"));
                $this->manager->getProvider()->remove($event->getBlock()->getFloorX(), $event->getBlock()->getFloorY(), $event->getBlock()->getFloorZ(), $event->getBlock()->getLevel()->getName());
                return;
            }
            $tile = $event->getBlock()->getLevel()->getTile($event->getBlock());
            if($tile instanceof Sign){
                SignManager::spawnTemporary($event->getPlayer(), $tile, [
                    "[TouchIt]",
                    "----------",
                    $this->manager->getLang("event.destroy.permission"),
                    ""
                ]);
            }
            $event->setCancelled();
            $event->getPlayer()->sendMessage($this->manager->getLang("event.destroy.permission.message"));
        }
    }
}