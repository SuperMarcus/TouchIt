<?php
namespace TouchIt;

use pocketmine\plugin\PluginBase;
use pocketmine\scheduler\CallbackTask;
use TouchIt\Listener\PlayerTouchListener;
use TouchIt\Listener\SignCreateListener;

class TouchIt extends PluginBase{
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
        $this->reloadLang();
        $this->manager = new SignManager($this);
        if(class_exists(($class = "TouchIt\\Provider\\".$this->getConfig()->get("Provider")))){
            $this->manager->setProvider(new $class($this));
        }else{
            $this->getLogger()->alert($this->getLang("provider.notfound"));
        }
        $this->getServer()->getPluginManager()->registerEvents(new PlayerTouchListener($this->manager), $this);
        $this->getServer()->getPluginManager()->registerEvents(new SignCreateListener($this->manager), $this);
        $this->getServer()->getScheduler()->scheduleRepeatingTask(new CallbackTask([$this->manager, "update"]), 20 * $this->getConfig()->get("ScheduleRepeatingPeriod"));
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
            $line = ltrim(fgets($stream));
            if((strlen($line) >= 3) and $line{0} !== "#" and ($pos = strpos($line, "=")) != false){
                $this->lang[substr($line, 0, $pos)] = substr($line, $pos + 1);
            }
        }
        @fclose($stream);
    }
}