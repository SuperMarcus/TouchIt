<?php
namespace TouchIt\Listener;

use TouchIt\TouchIt;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerLoginEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\event\player\PlayerRespawnEvent;
use pocketmine\event\entity\EntityLevelChangeEvent;

/** This listener is used for update sign */
class UpdateListener implements Listener{
    /**
     * Update when player join the game
     * @param PlayerLoginEvent $event
     */
    public function onPlayerLogin(PlayerLoginEvent $event){
        TouchIt::getManager()->addToUpdate($event->getPlayer()->getLevel()->getName());
    }

    /**
     * Update when player quit the game
     * @param PlayerQuitEvent $event
     */
    public function onPlayerQuit(PlayerQuitEvent $event){
        TouchIt::getManager()->addToUpdate($event->getPlayer()->getLevel()->getName());
    }

    /**
     * Update when player respawn
     * @param PlayerRespawnEvent $event
     */
    public function onPlayerRespawn(PlayerRespawnEvent $event){
        TouchIt::getManager()->addToUpdate($event->getRespawnPosition()->getLevel()->getName());
        //Respawn level
        TouchIt::getManager()->addToUpdate($event->getPlayer()->getLevel()->getName());
        //Origin level
    }

    /**
     * @param EntityLevelChangeEvent $event
     */
    //Update when player has been teleport between two level
    public function onPlayerLevelChange(EntityLevelChangeEvent $event){
        if($event->getEntity() instanceof Player){
            TouchIt::getManager()->addToUpdate($event->getOrigin()->getName());
            //Origin level
            TouchIt::getManager()->addToUpdate($event->getTarget()->getName());
            //Target level
        }
    }
}
?>
