<?php
namespace TouchIt\Listener

use pocketmine\event\Listener;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\Server;
use TouchIt\TouchIt;
use TouchIt\SignManager;

class EventListener implements Listener{
    private $touchit, $manager;
    
    public function __construct(TouchIt $touchit, SignManager $manager){
        $this->touchit = $touchit;
        $this->manager = $manager;
    }
    
    public function onPlayerJoin(PlayerJoinEvent $event){
    }
}
?>
