<?php
namespace TouchIt;

use pocketmine\command\Command;
use pocketmine\command\CommandExecutor;
use pocketmine\command\CommandSender;
use pocketmine\plugin\PluginBase;
use pocketmine\scheduler\CallbackTask;
use pocketmine\utils\TextFormat;
use TouchIt\Listener\PlayerTouchListener;
use TouchIt\Listener\SignCreateListener;
use TouchIt\Listener\SignDestroyListener;
use TouchIt\Provider\SQLite3;

class TouchIt extends PluginBase implements CommandExecutor{
    /** @var string */
    private $lang;

    /** @var SignManager */
    private $manager;

    /**
     * Call when enable
     */
    public function onEnable(){
        if(!file_exists($this->getDataFolder()."config.yml")){
            $this->saveDefaultConfig();
        }
        $this->reloadConfig();
        $this->reloadLang();
        $this->manager = new SignManager($this);
        if(class_exists(($class = "TouchIt\\Provider\\".$this->getConfig()->get("Provider")))){
            $this->manager->setProvider(new $class($this));
        }else{
            $this->getLogger()->alert($this->getLang("provider.notfound"));
            $this->manager->setProvider(new SQLite3($this));
        }
        $this->getServer()->getPluginManager()->registerEvents(new PlayerTouchListener($this->manager), $this);
        $this->getServer()->getPluginManager()->registerEvents(new SignCreateListener($this->manager), $this);
        $this->getServer()->getPluginManager()->registerEvents(new SignDestroyListener($this->manager), $this);
        if($this->getConfig()->get("AutoUpdate"))
            $this->getServer()->getScheduler()->scheduleRepeatingTask(new CallbackTask([$this->manager, "update"]), 20 * $this->getConfig()->get("ScheduleRepeatingPeriod"));
    }

    /**
     * @param CommandSender $sender
     * @param Command $command
     * @param string $label
     * @param array $args
     * @return bool
     */
    public function onCommand(CommandSender $sender, Command $command, $label, array $args){
        if(isset($args[0])){
            switch($args[0]){
                case "update":
                    $sender->sendMessage($this->getLang("command.update.start"));
                    $time = microtime(true);
                    $this->manager->update();
                    $sender->sendMessage(str_replace("{time}", round((microtime(true) - $time), 5), $this->getLang("command.update.stop")));
                    return true;
                case "portal":
                    if(isset($args[1])){
                        $info = [
                            [],//Departures
                            null//Arrival
                        ];
                        foreach($this->manager->getProvider()->getAll() as $sign){
                            if($sign['data']['type'] === SignManager::SIGN_PORTAL and $sign['data']['data']['name'] === $args[1]){
                                if($sign['data']['data']['id'] === 0)$info[1] = $sign['position']['x']." ".$sign['position']['y']." ".$sign['position']['z']." ".$sign['position']['level'];
                                else $info[0][] = $sign['position']['x']." ".$sign['position']['y']." ".$sign['position']['z']." ".$sign['position']['level'];
                            }
                        }
                        if($info[1] === null){
                            $sender->sendMessage(str_replace("{name}", $args[1], $this->getLang("command.portal.search.no")));
                        }else{
                            $sender->sendMessage(TextFormat::GREEN."-".$this->getLang("type.portal").": \"".$args[1]."\"-");
                            $sender->sendMessage(TextFormat::GOLD."Arrival: ".TextFormat::RESET.$info[1]);
                            $sender->sendMessage(TextFormat::GOLD."Departures:");
                            foreach($info[0] as $t => $sign){
                                $sender->sendMessage(TextFormat::AQUA."[".++$t."] ".TextFormat::RESET.$sign);
                            }
                        }
                    }else{
                        $portals = [];
                        foreach($this->manager->getProvider()->getAll() as $sign){
                            if($sign['data']['type'] === SignManager::SIGN_PORTAL and $sign['data']['data']['id'] === 0){
                                $portals[] = $sign['data']['data']['name'];
                            }
                        }
                        if(count($portals) > 0){
                            $sender->sendMessage($this->getLang("command.portal.all"));
                            foreach($portals as $c => $p){
                                $sender->sendMessage(TextFormat::AQUA."[".++$c."] ".TextFormat::RESET.$p);
                            }
                        }else{
                            $sender->sendMessage($this->getLang("command.portal.none"));
                        }
                    }
                    return true;
            }
        }
        return false;
    }

    /**
     * @param $key
     * @return string
     */
    public function getLang($key){
        if(isset($this->lang[$key])){
            return $this->lang[$key];
        }else if(is_array($this->lang)){
            return "Key not found.";
        }
        $this->reloadLang();
        return $this->getLang($key);
    }

    /**
     * Call when disable
     */
    public function onDisable(){
        $this->manager->close();
    }

    /**
     * Use to reload language profile
     */
    public function reloadLang(){
        $this->lang = [];
        $stream = $this->getResource("language/".strtolower($this->getConfig()->get("language")).".lang");
        if(!$stream){
            $stream = $this->getResource("language/english.lang");
            if(!$stream){
                $this->getLogger()->error("Unable to open stream. Could not load TouchIt languages.");
                $this->getServer()->forceShutdown();
                return;
            }
            $this->getLogger()->notice("Language \"".$this->getConfig()->get("language")."\" not found.");
            $this->getLogger()->notice("Make sure your spelling was correct. Change this option at \"plugins/TouchIt/config.yml\"");
        }
        while(!feof($stream)){
            $line = trim(fgets($stream));
            if((strlen($line) >= 3) and $line{0} !== "#" and ($pos = strpos($line, "=")) != false){
                $this->lang[substr($line, 0, $pos)] = substr($line, $pos + 1);
            }
        }
        @fclose($stream);
    }

    /**
     * Get the preloaded commands config folder
     * @return string
     */
    public function getPreloadedDataFolder(){
        $dir = $this->getDataFolder()."commands".DIRECTORY_SEPARATOR;
        @mkdir($dir);
        return $dir;
    }
}