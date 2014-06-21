<?php
namespace TouchIt\Listener

use pocketmine\event\Listener;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\Server;
use TouchIt\TouchIt;

class EventListener implements Listener{
    private $touchit;
    
    public function __construct(TouchIt $touchit){
        $this->touchit = $touchit;
    }
    
    public function onPlayerJoin(PlayerJoinEvent $event){
    }
}
?>
