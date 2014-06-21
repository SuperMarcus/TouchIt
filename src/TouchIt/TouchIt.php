<?php
namespace TouchIt;

use pocketmine\plugin\PluginBase;
use pocketmine\utils\TextFormat;
use TouchIt\DataProvider\CNFDataProvider;
use TouchIt\DataProvider\SQLDataProvider;
use TouchIt\Listener\EventListener;

class TouchIt extends PluginBase implements Listener, CommandExecutor{
    public $config, $sign, $listener;
    
    public function onLoad(){
        $this->$config = new CNFDataProvider($this, $this->getDataFolder()."Config.cnf");
        $this->listener = new EventListener($this);
    }
}
?>
