<?php
namespace touchit\listener;

use pocketmine\event\Listener;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\Player;
use touchit\sign\TouchItSign;
use touchit\SignManager;

class PlayerTouchListener implements Listener{
    /** @var SignManager */
    private $manager;

    /** @var string[][] */
    private $editModePlayers = [];

    public function __construct(SignManager $manager){
        $this->manager = $manager;
    }

    /**
     * @param PlayerInteractEvent $event
     */
    public function onPlayerTouch(PlayerInteractEvent $event){
        if(($sign = $event->getBlock()->getLevel()->getTile($event->getBlock())) instanceof TouchItSign){
            /** @var TouchItSign $sign */
            if(isset($this->editModePlayers[\spl_object_hash($event->getPlayer())])){
                $event->getPlayer()->sendTip($this->manager->getTranslator()->translateString("touchit.command.edit.apply", [(new \ReflectionClass($sign))->getShortName()]));
                $sign->doEdit($event->getPlayer(), $this->editModePlayers[\spl_object_hash($event->getPlayer())], $this->manager);
                unset($this->editModePlayers[\spl_object_hash($event->getPlayer())]);
            }else{
                $sign->onActive($event->getPlayer(), $this->manager);
            }
        }
    }

    /**
     * @param PlayerQuitEvent $event
     */
    public function onPlayerQuit(PlayerQuitEvent $event){
        if(isset($this->editModePlayers[\spl_object_hash($event->getPlayer())])){
            unset($this->editModePlayers[\spl_object_hash($event->getPlayer())]);
        }
    }

    /**
     * @param Player $player
     * @param string[] $args
     */
    public function addEditModePlayer(Player $player, $args){
        $this->editModePlayers[\spl_object_hash($player)] = $args;
    }
}