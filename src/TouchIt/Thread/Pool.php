<?php
namespace TouchIt\Thread;

use TouchIt\Thread\Worker;
use TouchIt\TouchIt;
use pocketmine\utils\MainLogger;

class Pool implements \countable{
    private $workers, $working, $isEnable;
    
    public function __construct(){
        $this->workers = [];
        $this->working = [];
        $this->isEnable = false;
    }
    
    /**
     * Submit a worker to pool
     * 
     * @param Worker $worker
     */
    public function submitWorker(Worker $worker){
        $this->workers[$worker->submitThread($this)] = $worker;
        if($this->isEnable)$worker->startThread();
    }
    
    public function onEnable(){
        $this->isEnable = true;
        foreach($this->workers as $worker){
            $worker->startThread();
        }
        usleep(20);
        if(count($this->workers) <= 0){
            MainLogger::getLogger()->debug("[TouchIt] No thread has been start.");
        }
    }
    
    public function removeThread($description){
        if(isset($this->working[$description])){
            if(isset($this->workers[$description])){
                $this->workers[$description]->stopThread();
            }
        }
        unset($this->workers[$description]);
        unset($this->working[$description]);
    }
    
    public function onDisable(){
        $this->isEnable = false;
        foreach($this->workers as $worker){
            $worker->stopThread();
        }
        usleep(50);
        if(count($this->working) > 0){
            MainLogger::getLogger()->debug("There's some thread still working.");
        }
    }
    
    public function getThreadId(string $class){
        if(isset($this->working[$class]))return $this->working[$class];
        else return null;
    }
    
    public function startThread($id, $description = false){
        if(isset($this->workers[$description]))$this->working[$description] = $id;
        else MainLogger::getLogger()->debug("Could not start an none-register thread. (Thread ID: ".$id." , Thread: ".(string) $description.")");
    }
    
    public function stopThread($id){
        if(isset($this->working[$id])){
            unset($this->working[$id]);
        }else{
            MainLogger::getLogger()->debug("An none-register thread has just stop. Thread ID: ".$id);
        }
    }
    
    public function count(){
        return count($this->working);
    }
}
?>
