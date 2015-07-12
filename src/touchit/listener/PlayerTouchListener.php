<?php
namespace touchit\listener;

use pocketmine\event\Listener;
use pocketmine\event\player\PlayerCommandPreprocessEvent;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\level\Position;
use touchit\command\OperatorCommandSender;
use touchit\SignManager;

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
                    $opts = ["operator" => false, "preloaded" => false];
                    if(isset($data['data']['option'])){
                        $opts = $data['data']['option'];
                    }
                    if($opts['preloaded']){
                        if(file_exists($this->manager->getPreloadedDataFolder().$data['data']['cmd'].".txt")){
                            $fp = @fopen($this->manager->getPreloadedDataFolder().$data['data']['cmd'].".txt", "r");
                            if($fp){
                                while(!feof($fp)){
                                    $line = str_replace(["@time", "@player", "@level"], [time(), $event->getPlayer()->getName(), $event->getPlayer()->getLevel()->getName()], ltrim(fgets($fp)));
                                    if(substr(trim($line)."#", 0, 1) !== "#"){
                                        $this->manager->getServer()->getPluginManager()->callEvent($e = new PlayerCommandPreprocessEvent($event->getPlayer(), "/".$line));
                                        if(!$e->isCancelled()){
                                            $this->manager->getServer()->dispatchCommand(($opts['operator'] ? new OperatorCommandSender($event->getPlayer(), $this->manager->getServer()) : $event->getPlayer()), $line);
                                        }
                                    }
                                }
                            }else{
                                $event->getPlayer()->sendMessage($this->manager->getLang("event.command.preloaded.unreadable"));
                            }
                        }else{
                            $event->getPlayer()->sendMessage($this->manager->getLang("event.command.preloaded.unexists"));
                        }
                    }else{
                        $cmd = str_replace(["@time", "@player", "@level"], [time(), $event->getPlayer()->getName(), $event->getPlayer()->getLevel()->getName()], $data['data']['cmd']);
                        $this->manager->getServer()->getPluginManager()->callEvent($e = new PlayerCommandPreprocessEvent($event->getPlayer(), "/".$cmd));
                        if(!$e->isCancelled()){
                            $this->manager->getServer()->dispatchCommand(($opts['operator'] ? new OperatorCommandSender($event->getPlayer(), $this->manager->getServer()) : $event->getPlayer()), $cmd);
                        }
                    }
                    break;
                default:
                    $event->getPlayer()->sendMessage(str_replace("{type}", $data['type'], $this->manager->getLang("event.unknowtype")));
            }
        }
    }
}