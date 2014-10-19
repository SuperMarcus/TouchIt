<?php
namespace TouchIt\Listener;

use pocketmine\event\Listener;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\level\Position;
use TouchIt\SignManager;

class PlayerTouchListener implements Listener{
    /** @var SignManager */
    private $manager;

    public function __construct(SignManager $manager){
        $this->manager = $manager;
    }

    /**
     * @param PlayerInteractEvent $event
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
                case SignManager::SIGN_WORLD_TELEPORT:
                    if(!$event->getPlayer()->hasPermission("touchit.sign.use.world-teleport")){
                        $event->getPlayer()->sendMessage($this->manager->getLang("event.permission"));
                        break;
                    }
                    if($this->manager->getServer()->isLevelLoaded($data['data']['target'])){
                        $level = $this->manager->getServer()->getLevelByName($data['data']['target']);
                        if($this->manager->getConfig()->get("teleport")['EnableCount'] and @array_search($data['data']['target'], (array) $this->manager->getConfig()->get("teleport")['MainLevel']) === false and (count($level->getPlayers()) >= $this->manager->getConfig()->get("teleport")['MaxPlayers'])){
                            $event->getPlayer()->sendMessage(str_replace(["{target}", "{origin}", "{player}"], [$data['data']['target'], $event->getPlayer()->getLevel()->getName(), $event->getPlayer()->getName()], $this->manager->getLang("event.limit")));
                            break;
                        }
                        $event->getPlayer()->sendMessage(str_replace(["{target}", "{origin}", "{player}"], [$data['data']['target'], $event->getPlayer()->getLevel()->getName(), $event->getPlayer()->getName()], $this->manager->getLang("event.teleport.process")));
                        $event->getPlayer()->teleport($level->{$this->manager->getConfig()->get("teleport")['SafeSpawn'] ? "getSafeSpawn" : "getSpawnLocation"}());
                        break;
                    }
                    $event->getPlayer()->sendMessage(str_replace(["{target}", "{origin}", "{player}"], [$data['data']['target'], $event->getPlayer()->getLevel()->getName(), $event->getPlayer()->getName()], $this->manager->getLang("event.notopen")));
                    break;
                case SignManager::SIGN_PORTAL:
                    if(!$event->getPlayer()->hasPermission("touchit.sign.use.portal")){
                        $event->getPlayer()->sendMessage($this->manager->getLang("event.permission"));
                        break;
                    }
                    if($data['data']['id'] === 0){
                        $event->getPlayer()->sendMessage(str_replace("{name}", $data['data']['name'], $this->manager->getLang("event.arrival")));
                    }else{
                        if(!$this->manager->getProvider()->exists($data['data']['target']['x'], $data['data']['target']['y'], $data['data']['target']['z'], $data['data']['target']['level'])){
                            $event->getPlayer()->sendMessage($this->manager->getLang("event.no-arrive"));
                            break;
                        }
                        if($this->manager->getServer()->isLevelLoaded($data['data']['target']['level'])){
                            $level = $this->manager->getServer()->getLevelByName($data['data']['target']['level']);
                            if($this->manager->getConfig()->get("portal")['UseLimits'] and @array_search($data['data']['target'], (array) $this->manager->getConfig()->get("teleport")['MainLevel']) === false and (count($level->getPlayers()) >= $this->manager->getConfig()->get("teleport")['MaxPlayers'])){
                                $event->getPlayer()->sendMessage(str_replace(["{target}", "{origin}", "{player}"], [$data['data']['target'], $event->getPlayer()->getLevel()->getName(), $event->getPlayer()->getName()], $this->manager->getLang("event.limit")));
                                break;
                            }
                            $event->getPlayer()->sendMessage(str_replace("{name}", $data['data']['name'], $this->manager->getLang("event.portal.process")));
                            $pos = new Position($data['data']['target']['x'], $data['data']['target']['y'], $data['data']['target']['z'], $level);
                            if($this->manager->getConfig()->get("portal")['SafeSpawn']){//Implements safe spawn option
                                $pos = $level->getSafeSpawn($pos);
                            }
                            $event->getPlayer()->teleport($pos);
                        }else{
                            $event->getPlayer()->sendMessage(str_replace(["{target}", "{origin}", "{player}"], [$data['data']['name'], $event->getPlayer()->getLevel()->getName(), $event->getPlayer()->getName()], $this->manager->getLang("event.notopen")));
                        }
                    }
                    break;
                case SignManager::SIGN_COMMAND:
                    if(!$event->getPlayer()->hasPermission("touchit.sign.use.command")){
                        $event->getPlayer()->sendMessage($this->manager->getLang("event.permission"));
                        break;
                    }
                    if($this->manager->getConfig()->get("command")['ShowStatus']){
                        $event->getPlayer()->sendMessage(str_replace("{cmd}", $data['data']['cmd'], $this->manager->getLang("event.command.process.run")));
                    }
                    if($this->manager->getServer()->dispatchCommand($event->getPlayer(), str_replace(["@p", "@player"], [$event->getPlayer()->getName(), $event->getPlayer()->getName()], $data['data']['cmd']))){//Run, will use target player to be the CommandSender
                        if($this->manager->getConfig()->get("command")['ShowStatus']){
                            $event->getPlayer()->sendMessage(str_replace("{cmd}", $data['data']['cmd'], $this->manager->getLang("event.command.process.done")));
                        }
                    }else{
                        if($this->manager->getConfig()->get("command")['ShowStatus']){
                            $event->getPlayer()->sendMessage(str_replace("{cmd}", $data['data']['cmd'], $this->manager->getLang("event.command.process.error")));
                        }
                    }
                    break;
                default:
                    $event->getPlayer()->sendMessage(str_replace("{type}", $data['type'], $this->manager->getLang("event.unknowtype")));
            }
        }
    }
}