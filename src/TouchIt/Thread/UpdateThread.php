<?php
namespace TouchIt\Thread;

use TouchIt\Thread\ThreadManager;
use pocketmine\tile\Sign;

class UpdateThread extends \Thread{
    private $thread_manager;
    private $signs;
    
    public function run(){
        while(count($this->signs) > 0){
            $sign = @array_shift($this->signs);
            $tile = $sign['position']->getLevel()->getTile($sign['position']);
        }
    }
    
    public function __construct(ThreadManager $thread_manager){
        $this->thread_manager = $thread_manager;
        $this->signs = [];
    }
    
    public function submit($sign = null){
        if(is_array($sign)){
            $this->signs[] = $sign;
        }
    }
}
?>
