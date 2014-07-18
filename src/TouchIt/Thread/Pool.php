<?php
namespace TouchIt\Thread;

use TouchIt\Pool\Worker;
use TouchIt\TouchIt;

class Pool implements Countable{
    private $workers, $working;
    
    public function __construct(){
        $this->workers = [];
        $this->working = [];
    }
    
    public function onEnable(){
        foreach($this->workers as $worker){
            $worker->startThread();
        }
        usleep(20);
        if(count($this->workers) <= 0){
            TouchIt::getTouchIt()->getLogger()->debug("[TouchIt] None thread has been start.");
        }
    }
    
    public function onDisable(){
        foreach($this->workers as $worker){
            $worker->stopThread();
        }
        usleep(50);
        if(count($this->working) > 0){
            TouchIt::getTouchIt()->getLogger()->debug("[TouchIt] There's some thread still working.");
        }
    }
    
    public function getThreadDescription($id){
        if(isset($this->working[$id]))return $this->working[$id];
        else return null;
    }
    
    public function startThread($id, $description = false){
        $this->working[$id] = $description;
    }
    
    public function stopThread($id){
        if(isset($this->working[$id])){
            unset($this->working[$id]);
        }else{
            TouchIt::getTouchIt()->getLogger()->debug("[TouchIt] An none-register thread has just stop. Thread ID: ".$id);
        }
    }
    
    public function count(){
        return count($this->working);
    }
}
?>
