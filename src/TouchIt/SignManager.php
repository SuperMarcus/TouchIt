<?php
namespace TouchIt;

use TouchIt\DataProvider\Provider;
use pocketmine\Server;
use pocketmine\block\Block;
use pocketmine\tile\Sign;
use pocketmine\utils\TextFormat;
use pocketmine\level\Level;

class SignManager{
    /** @var TouchIt */
    private $plugin;

    /** @var Provider */
    private $provider;

    /** @var UnitLoader */
    private $unit;

    /** @var ConfigAccessor */
    private $config;
    
    public function __construct(TouchIt $plugin, Provider $provider, UnitLoader $unit){
        $this->plugin = $plugin;
        $this->provider = $provider;
        $this->unit = $unit;
        $this->config = $plugin->getConfig();//This call will load config ^_^
    }
    
    public function onEnable(){

    }
    
    public function onDisable(){

    }

    public function onUpdate(){

    }

    public function onPlayerTouch($block, $player){

    }
    
    public function onBlockBreak($event){

    }
}
?>
