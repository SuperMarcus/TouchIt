<?php
namespace touchit\task;

use pocketmine\scheduler\PluginTask;
use touchit\sign\TouchItSign;
use touchit\TouchIt;

class SignUpdateTask extends PluginTask{
    public function __construct(TouchIt $plugin){
        parent::__construct($plugin);
    }

    public function onRun($currentTick){
        /** @var TouchIt $plugin */
        $plugin = $this->getOwner();
        foreach($plugin->getServer()->getLevels() as $level){
            foreach($level->getTiles() as $tile){
                if($tile instanceof TouchItSign){
                    $tile->doUpdate($plugin->getManager());
                }
            }
        }
    }
}