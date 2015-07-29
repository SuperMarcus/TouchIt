<?php
namespace touchit\listener;

use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\Listener;
use touchit\sign\TouchItSign;
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
        if($event->getBlock()->getLevel()->getTile($event->getBlock()) instanceof TouchItSign){
            if($event->getPlayer()->hasPermission("touchit.sign.destroy")){
                $event->getPlayer()->sendTip($this->manager->getTranslator()->translateString("touchit.event.destroy"));
            }else{
                $event->getPlayer()->sendTip($this->manager->getTranslator()->translateString("touchit.event.permission"));
                $event->setCancelled();
            }
        }
    }
}