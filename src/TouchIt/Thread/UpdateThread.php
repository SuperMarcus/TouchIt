<?php
namespace TouchIt\Thread;

use pocketmine\tile\Sign;
use pocketmine\math\Vector3;

class UpdateThread extends \Thread{
    private $thread_manager;
    private $signs;
    private $callbacks;

    private $types = null;

    /**
     * Process
     */
    public function run(){
        while(count($this->signs) > 0){
            $sign = @array_shift($this->signs);
            $level = $this->thread_manager->plugin->getServer()->getLevelByName($sign['position']['level']);
            if($level and ($tile = $level->getTile(new Vector3($sign['position']['x'], $sign['position']['y'], $sign['position']['z']))) instanceof Sign){
                if($tile instanceof Sign){
                    if(isset($this->callbacks[$sign['types']])){//Call unit
                        $callback = $this->callbacks[$sign['types']];
                        if(is_callable($callback)){
                            @call_user_func($callback, $sign, $tile, $this->thread_manager);
                        }
                    }
                }
            }
        }
        exit(0);
    }
    
    public function __construct(ThreadManager $thread_manager){
        $this->thread_manager = $thread_manager;
        $this->signs = [];
    }

    /**
     * Use to add process unit (*.callable)
     * @param callable $unit
     * @param $id
     */
    public function addUnit(callable $unit, $id){
        $this->callbacks[$id] = $unit;
    }

    /**
     * Init TouchIt supported sign list
     * Only call this method once!
     * @param array $types
     * @throws \ErrorException
     */
    public function setTypes(array $types){
        if($this->types === null){
            $this->types === $types;
        }else{
            throw new \ErrorException("Duplicate assignment is not allowed.");
        }
    }

    /**
     * Internal use
     * Add sign to process list
     * @param null $sign
     */
    public function submit($sign = null){
        if(is_array($sign)){
            $this->signs[] = $sign;
        }
    }
}
?>
