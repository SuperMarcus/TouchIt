<?php
namespace TouchIt;

use pocketmine\plugin\PluginBase;

class TouchIt extends PluginBase{
    /** @var string */
    private $lang;

    /**
     * Call when enable
     */
    public function onEnable(){
        $this->reloadLang();
    }

    /**
     * Call when disable
     */
    public function onDisable(){

    }

    /**
     * Use to reload language profile
     */
    public function reloadLang(){
        $this->lang = [];
        $stream = $this->getResource("language/".strtolower($this->getConfig()->get("language")).".lang");
        if(!$stream or !is_readable($stream)){
            $stream = $this->getResource("language/english.lang");
            if(!$stream or !is_readable($stream)){
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
                $this->lang[substr($line, 0, ($pos -1))] = substr($line, $pos);
            }
        }
        @fclose($stream);
    }
}
?>
