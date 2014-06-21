<?php
namespace TouchIt;

use pocketmine\plugin\PluginBase;
use pocketmine\utils\TextFormat;
use TouchIt\DataProvider\CNFDataProvider;
use TouchIt\DataProvider\SQLDataProvider;

class TouchIt extends PluginBase implements Listener, CommandExecutor{
    public $config, $sign;
    
    public function onLoad(){
        $this->$config = new CNFDataProvider();
    }
}
?>
