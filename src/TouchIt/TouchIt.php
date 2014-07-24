<?php
namespace TouchIt;

use pocketmine\plugin\PluginBase;
use TouchIt\DataProvider\CNFDataProvider;
use TouchIt\DataProvider\Provider;
use TouchIt\DataProvider\SQLDataProvider;
use TouchIt\Listener\EventListener;
use TouchIt\SignManager;

class TouchIt extends PluginBase{
    const SIGN_TELEPORT = 0;
    const SIGN_COMMAND = 1;
    const SIGN_BOARDCASE = 2;
	
    private $objects;
    
    public static $lang = [];//TouchIt language profile
    
    /** @var SignManager */
    public static $manager;
    /** @var Provider */
    public static $configProvider;
    /** @var Provider */
    public static $dataProvider;
    /** @var EventListener */
    public static $listener;
    /** @var TouchIt */
    public static $main;
    
    public function onLoad(){
        $this->objects = [//The providers and managers
            "manager" => new SignManager(),
            "config" => new Config($this->getDataFolder()."Config.cnf"),
            "data" => new SQLDataProvider(),
            "listener" => new EventListener()
        ];
        
        self::$manager = $this->objects["manager"];
        self::$configProvider = $this->objects["config"];
        self::$dataProvider = $this->objects["data"];
        self::$listener = $this->objects["listener"];
        
        self::$lang = $this->["config"]->getLang();
        
        self::$main = $this;
    }
    
    /**
     * @return TouchIt
     */
    public static function getTouchIt(){
        retunr self::$main;
    }
    
    /**
     * @return SignManager
     */
    public static function getManager(){
        return self::$manager;
    }
    
    /**
     * @return Provider
     */
    public static function getDataProvider(){
        return self::$dataProvider;
    }
    
    /**
     * @return Provider
     */
    public static function getConfigProvider(){
        return self::$configProvider;
    }
    
    /**
     * @return []
     */
    public static function getLang(){
        return self::$lang;
    }
    
    /**
     * @return EventListener
     */
    public static function getEventListener(){
        return self::$listener;
    }
    
    /**
     * @param Provider $object
     */
    public function setDataProvider(Provider $object){
        $this->objects["data"] = $object;
        self::$dataProvider = $object;
    }
    
    public function onEnable(){
        $this->config->onEnable();
        $this->database->onEnable();
        $this->signManager->onEnable();
    }
    
    public function onDisable(){
        $this->config->onDisable();
        $this->database->onDisable();
        $this->signManager->onDisable();
    }
}
?>
