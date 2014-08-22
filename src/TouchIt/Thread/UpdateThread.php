<?php
namespace TouchIt\Thread;

use TouchIt\Thread\ThreadManager;
use pocketmine\tile\Sign;

class UpdateThread extends \Thread{
    private $thread_manager;
    private $signs;
    private $callbacks;

    private $types = null;
    
    public function run(){
        while(count($this->signs) > 0){
            $sign = @array_shift($this->signs);
            $tile = $sign['position']->getLevel()->getTile($sign['position']);
            if($tile instanceof Sign){
                if(isset($this->callbacks[$sign['types']])){//Call unit
                    $callback = $this->callbacks[$sign['types']];
                    if(is_callable($callback)){
                        @call_user_func($callback, $sign, $tile, $this->thread_manager);
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

    public function addUnit(callable $unit, $id){
        $this->callbacks[$id] = $unit;
    }

    public function setTypes(array $types){
        if($this->types === null){
            $this->types === $types;
        }else{
            throw new \ErrorException("Duplicate assignment is not allowed.");
        }
    }
    
    public function submit($sign = null){
        if(is_array($sign)){
            $this->signs[] = $sign;
        }
    }
}
?>
