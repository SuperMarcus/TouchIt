<?php
namespace TouchIt;

use pocketmine\plugin\PluginBase;
use TouchIt\ConfigAccessor;
use TouchIt\DataProvider\Provider;
use TouchIt\DataProvider\SQLDataProvider;
use TouchIt\Listener\MainListener;
use TouchIt\Listener\UpdateListener;
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
    /** @var MainListener */
    public static $listener;
    /** @var TouchIt */
    public static $main;
    
    public function onLoad(){
        $this->objects = [//The providers and managers
            "manager" => new SignManager(),
            "config" => new ConfigAccessor($this->getDataFolder()."Config.cnf"),
            "data" => new SQLDataProvider(),
            "listener" => new MainListener,
            "updatelistener" => new UpdateListener
        ];
        
        self::$manager = $this->objects["manager"];
        self::$configProvider = $this->objects["config"];
        self::$dataProvider = $this->objects["data"];
        self::$listener = $this->objects["listener"];
        
        self::$lang = $this->["config"]->getLang();
        
        self::$main = $this;
        
        $this->getServer()->getPluginManager()->registerEvents($this->objects["listener"]);
        $this->getServer()->getPluginManager()->registerEvents($this->objects["updatelistener"]);
        //Auto register all the events
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
     * @return string
     */
    public static function getLang($key){
        $id = explode(".", $key);
        $lang = self::$lang;
        while(count($id) > 0){
        	$key = array_shift($id);
        	if(isset($lang[$key])){
        		$lang = $lang[$key];
        	}else{
        		trigger_error("Could not find lang: ".$key." in language profile.", E_USER_ERROR);
        		return "Language profile error.";
        	}
        }
        if(is_array($lang)){
        	trigger_error("Could not return an array of lang: ".$key.".", E_USER_ERROR);
        	return "Language profile error.";
        }
        return (string) $lang;
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
