<?php
namespace TouchIt;

use TouchIt\DataProvider\Provider;
use TouchIt\Thread\ThreadManager;
use pocketmine\Server;
use pocketmine\block\Block;
use pocketmine\tile\Sign;
use pocketmine\utils\TextFormat;
use pocketmine\level\Level;
use pocketmine\Player;

class SignManager{
    /** @var ThreadManager */
    private $thread_manager;

    /** @var TouchIt */
    private $plugin;

    /** @var Provider */
    private $provider;

    /** @var UnitLoader */
    private $unit;
    
    public function __construct(TouchIt $plugin, Provider $provider, UnitLoader $unit){
        $this->plugin = $plugin;
        $this->provider = $provider;
        $this->unit = $unit;

        $this->initialize();
    }

    
    public function onEnable(){
        $this->thread_manager->onEnable();
    }
    
    public function onDisable(){

    }

    public function onUpdate(){

    }

    public function onPlayerTouch($block, $player, $event){
        if($this->provider->exists($block->getFloorX(), $block->getFloorY(), $block->getFloorZ(), $block->getLevel()->getName())){
            $info = $this->provider->get($block->getFloorX(), $block->getFloorY(), $block->getFloorZ(), $block->getLevel()->getName());
            $unit = $this->unit->getUnits("unit_touch")[(int) $info['type']];
            $event->setCancelled();
            if(is_callable($unit)){
                @call_user_func($unit, $info, $player, $this);
            }
        }
    }

    public function onBlockPlace(Sign $tile){
        $text = $tile->getText();
        if(substr(strtolower(trim($text[0])), 0, 7) === "touchit"){
            return true;
        }
        return false;
    }

    public function onNewSign(Player $player, array $lines, Block $block){

    }
    
    public function onBlockBreak($event){

    }

    private function initialize(){
        $this->thread_manager = new ThreadManager($this->plugin, $this->unit, $this->plugin->getLogger(), $this->provider, $this->plugin->getConfig());
    }
}
?>
