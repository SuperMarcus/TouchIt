<?php
namespace TouchIt\Listener;

use TouchIt\SignManager;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerLoginEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\event\player\PlayerRespawnEvent;
use pocketmine\event\entity\EntityLevelChangeEvent;

/** This listener is used for update sign */
class UpdateListener implements Listener{
    private $manager;

    public function __construct(SignManager $manager){
        $this->manager = $manager;
    }
    /**
     * Update when player join the game
     * @param PlayerLoginEvent $event
     */
    public function onPlayerLogin(PlayerLoginEvent $event){

    }

    /**
     * Update when player quit the game
     * @param PlayerQuitEvent $event
     */
    public function onPlayerQuit(PlayerQuitEvent $event){

    }

    /**
     * Update when player respawn
     * @param PlayerRespawnEvent $event
     */
    public function onPlayerRespawn(PlayerRespawnEvent $event){

    }

    /**
     * @param EntityLevelChangeEvent $event
     */
    //Update when player has been teleport between two level
    public function onPlayerLevelChange(EntityLevelChangeEvent $event){

    }
}
?>
