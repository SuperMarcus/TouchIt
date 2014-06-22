<?php
namespace TouchIt;

use pocketmine\plugin\PluginBase;
use pocketmine\utils\TextFormat;
use TouchIt\DataProvider\CNFDataProvider;
use TouchIt\DataProvider\SQLDataProvider;
use TouchIt\Listener\EventListener;
use TouchIt\SignManager;

class TouchIt extends PluginBase implements Listener, CommandExecutor{
    public $config, $database, $listener, $signManager;
    
    public function onEnable(){
        $this->config = new CNFDataProvider($this, $this->getDataFolder()."Config.cnf");
        $this->database = new SQLDataProvider($this);
        $this->signManager = new SignManager($this, $this->config, $this->database);
        $this->listener = new EventListener($this, $this->signManager);
    }
}
?>
