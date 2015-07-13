<?php
namespace touchit\task;

use pocketmine\scheduler\PluginTask;
use pocketmine\tile\Tile;
use touchit\TouchIt;

class TileCreateTask extends PluginTask{
    private $args;
    private $calls;

    public function __construct(TouchIt $plugin, array $calls, ...$args){
        parent::__construct($plugin);
        $this->args = $args;
        $this->calls = $calls;
    }

    public function onRun($currentTick){
        $tile = Tile::createTile(...$this->args);
        foreach($this->calls as $call){
            @call_user_func_array([$tile, $call[0]], $call[1]);
        }
    }
}