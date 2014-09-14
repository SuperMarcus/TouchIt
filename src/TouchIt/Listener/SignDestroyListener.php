<?php
namespace TouchIt\Listener;

use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\Listener;
use TouchIt\SignManager;

class SignDestroyListener implements Listener{
    /** @var SignManager */
    private $manager;

    public function __construct(SignManager $manager){
        $this->manager = $manager;
    }

    /**
     * @param BlockBreakEvent $event
     * @priority HIGHEST
     * @ignoreCancelled true
     */
    public function onBlockDestroy(BlockBreakEvent $event){
        if($this->manager->getProvider()->exists($event->getBlock()->getFloorX(), $event->getBlock()->getFloorY(), $event->getBlock()->getFloorZ(), $event->getBlock()->getLevel()->getName())){
            if($event->getPlayer()->hasPermission("touchit.sign.destroy")){
                $event->getPlayer()->sendMessage($this->manager->getLang("event.destroy.load"));
                $this->manager->getProvider()->remove($event->getBlock()->getFloorX(), $event->getBlock()->getFloorY(), $event->getBlock()->getFloorZ(), $event->getBlock()->getLevel()->getName());
                return;
            }
            $event->setCancelled();
            $event->getPlayer()->sendMessage($this->manager->getLang("event.destroy.permission"));
        }
    }
}