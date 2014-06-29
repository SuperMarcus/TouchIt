<?php
namespace TouchIt;

use pocketmine\plugin\PluginBase;
use pocketmine\utils\TextFormat;
use pocketmine\command\CommandSender;
use pocketmine\command\Command;
use TouchIt\DataProvider\CNFDataProvider;
use TouchIt\DataProvider\SQLDataProvider;
use TouchIt\Listener\EventListener;
use TouchIt\SignManager;

class TouchIt extends PluginBase implements CommandExecutor{
    public $config, $database, $listener, $signManager;
    
    public function onLoad(){
        $this->config = new CNFDataProvider($this, $this->getDataFolder()."Config.cnf");
        $this->database = new SQLDataProvider($this);
        $this->signManager = new SignManager($this, $this->config, $this->database);
        $this->listener = new EventListener($this, $this->signManager);
    }
    
    public function onEnable(){
        $this->config->onEnable();
        $this->database->onEnable();
        $this->signManager->start()
    }
    
    public function onDisable(){
        $this->config->onDisable();
        $this->database->onDisable();
        $this->signManager->stop();
    }
    
    public function onCommand(CommandSender $sender, Command $command, $label, array $args){
        return $this->signManager->onCommand(CommandSender $sender, Command $command, $label, array $args);
    }
}
?>
