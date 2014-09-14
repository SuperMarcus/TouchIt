<?php
namespace TouchIt\Listener;

use pocketmine\event\Listener;
use pocketmine\event\player\PlayerInteractEvent;
use TouchIt\SignManager;

class PlayerTouchListener implements Listener{
    /** @var SignManager */
    private $manager;

    public function __construct(SignManager $manager){
        $this->manager = $manager;
    }

    /**
     * @param PlayerInteractEvent $event
     * @ignoreCancelled true
     */
    public function onPlayerTouch(PlayerInteractEvent $event){
        if($this->manager->getProvider()->exists($event->getBlock()->getFloorX(), $event->getBlock()->getFloorY(), $event->getBlock()->getFloorZ(), $event->getBlock()->getLevel()->getName())){
            $event->setCancelled();
            if(!$event->getPlayer()->hasPermission("touchit.sign.use")){
                $event->getPlayer()->sendMessage($this->manager->getLang("event.permission"));
                return;
            }
            $data = $this->manager->getProvider()->get($event->getBlock()->getFloorX(), $event->getBlock()->getFloorY(), $event->getBlock()->getFloorZ(), $event->getBlock()->getLevel()->getName());
            switch($data['type']){
                case SignManager::SIGN_TELEPORT:
                    if(!$event->getPlayer()->hasPermission("touchit.sign.use.teleport")){
                        $event->getPlayer()->sendMessage($this->manager->getLang("event.permission"));
                        break;
                    }
                    if($this->manager->getServer()->isLevelLoaded($data['target'])){
                        $level = $this->manager->getServer()->getLevelByName($data['target']);
                        if($data['target'] !== $this->manager->getConfig()->get("MainLevel") and (count($level->getPlayers()) >= $this->manager->getConfig()->get("MaxPlayers"))){
                            $event->getPlayer()->sendMessage(str_replace(["{target}", "{origin}", "{player}"], [$data['target'], $event->getPlayer()->getLevel()->getName(), $event->getPlayer()->getName()], $this->manager->getLang("event.limit")));
                            break;
                        }
                        $event->getPlayer()->sendMessage(str_replace(["{target}", "{origin}", "{player}"], [$data['target'], $event->getPlayer()->getLevel()->getName(), $event->getPlayer()->getName()], $this->manager->getLang("event.teleporting")));
                        $event->getPlayer()->teleport($level->{$this->manager->getConfig()->get("SafeSpawn") ? "getSafeSpawn" : "getSpawnLocation"}());
                        break;
                    }
                    $event->getPlayer()->sendMessage(str_replace(["{target}", "{origin}", "{player}"], [$data['target'], $event->getPlayer()->getLevel()->getName(), $event->getPlayer()->getName()], $this->manager->getLang("event.notopen")));
                    break;
                default:
                    $event->getPlayer()->sendMessage(str_replace("{type}", $data['type'], $this->manager->getLang("event.unknowtype")));
            }
        }
    }
}