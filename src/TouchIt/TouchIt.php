<?php
namespace TouchIt;

use pocketmine\plugin\PluginBase;
use pocketmine\event\Listener;
use TouchIt\DataProvider\Provider;
use TouchIt\DataProvider\SQLDataProvider;
use TouchIt\Listener\MainListener;
use TouchIt\Listener\UpdateListener;

class TouchIt extends PluginBase{
    /** @var array */
    public $lang = [];//TouchIt language profile

    /** @var null|SignManager */
    private $manager = null;

    /** @var null|ConfigAccessor */
    private $touchit_config = null;

    /** @var null|Listener[] */
    private $listener = null;

    /** @var null|Provider */
    private $provider = null;

    /** @var null|UnitLoader */
    private $unit_loader = null;

    /**
     * Call when enable
     */
    public function onEnable(){
    	@mkdir($this->getDataFolder());

        $this->unit_loader = new UnitLoader($this);
        $this->provider = new SQLDataProvider($this);
        $this->manager = new SignManager($this, $this->provider, $this->unit_loader);
        $this->listener = [new MainListener($this->manager, $this), new UpdateListener($this->manager)];

        $this->getConfig()->analyzeFile();
        
        $this->lang = $this->getConfig()->getLang();

        $this->manager->onEnable();

        foreach($this->listener as $listener){
            $this->getServer()->getPluginManager()->registerEvents($listener, $this);
        }
        //Auto register all the events
    }

    /**
     * @param $key
     * @return string
     */
    public function findLang($key){
    	if(isset($this->lang[$key]))return $this->lang[$key];
        return "Language profile error";
    }

    /**
     * Call when disable
     */
    public function onDisable(){
        $this->manager->onDisable();
        $this->provider->onDisable();
    }

    public function saveDefaultConfig(){
        $this->reloadConfig();
    }

    /**
     * Re-analyze config
     */
    public function reloadConfig(){
        $this->touchit_config = new ConfigAccessor($this->getDataFolder()."config.cnf", $this);
        $this->touchit_config->analyzeFile();
    }

    /**
     * Same as reloadConfig();
     */
    public function saveConfig(){
        $this->reloadConfig();
    }

    /**
     * @return ConfigAccessor
     */
    public function getConfig(){
        if($this->touchit_config instanceof ConfigAccessor){
            return $this->touchit_config;
        }else{
            $this->reloadConfig();
            return $this->getConfig();
        }
    }
}
?>
