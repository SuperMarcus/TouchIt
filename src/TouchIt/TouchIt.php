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

    public $types = [
        0 => "SIGN_TELEPORT",
        1 => "SIGN_COMMAND",
        2 => "SIGN_BOARDCASE"
    ];
	
    private $objects;
    
    public $lang = [];//TouchIt language profile
    
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
    
    public function onEnable(){
    	self::$main = $this;
    	
    	@mkdir(TouchIt::getTouchIt()->getDataFolder());
    	
        $this->objects = [//The providers and managers
            "manager" => new SignManager(),
            "config" => new ConfigAccessor($this->getDataFolder()."config.cnf", $this),
            "data" => new SQLDataProvider(),
            "listener" => new MainListener,
            "updatelistener" => new UpdateListener
        ];
        
        self::$manager = $this->objects["manager"];
        self::$configProvider = $this->objects["config"];
        self::$dataProvider = $this->objects["data"];
        self::$listener = $this->objects["listener"];
        
        $this->lang = $this->objects["config"]->getLang();
        
        $this->objects['config']->analyzeFile();
        $this->objects['manager']->onEnable();
        
        $this->getServer()->getPluginManager()->registerEvents($this->objects["listener"], $this);
        $this->getServer()->getPluginManager()->registerEvents($this->objects["updatelistener"], $this);
        //Auto register all the events
    }
    
    public function findLang($key){
    	if(isset($this->lang[$key]))return $this->lang[$key];
        return "Language profile error";
    }
    
    public function onDisable(){
    	$this->objects['manager']->onDisable();
    	
    	$this->objects = [];
    	
    	//Destroy all the objects, then some of the method in this class will return null.
    	self::$manager = null;
        self::$configProvider = null;
        self::$dataProvider = null;
        self::$listener = null;
        self::$main = null;
    }
    
    /**
     * @return TouchIt|null
     */
    public static function getTouchIt(){
        return self::$main;
    }
    
    /**
     * @return SignManager|null
     */
    public static function getManager(){
        return self::$manager;
    }
    
    /**
     * @return Provider|null
     */
    public static function getDataProvider(){
        return self::$dataProvider;
    }
    
    /**
     * @return Provider|null
     */
    public static function getConfigProvider(){
        return self::$configProvider;
    }

    /**
     * @param $key
     * @return string
     */
    public static function getLang($key){
        return self::$main->findLang($key);
    }
    
    /**
     * @return EventListener|null
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

    /**
     * @return array
     */
    public function getTypes(){
        return $this->types;
    }
}
?>
