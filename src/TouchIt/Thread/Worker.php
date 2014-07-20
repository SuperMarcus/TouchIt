<?php
namespace TouchIt\Thread;

use TouchIt\Thread\Pool;
use TouchIt\TouchIt;

abstract class Worker extends \Thread{
    /** @var Pool */
    private $pool;
    
    private $kill;
    
    abstract public function onRun();
    
    public function onStop(){}
    
    public final function setKillStop($v = true){
        $this->kill = (bool) $v;
    }
    
    public final function run(){
        $this->setKillStop();
        $this->pool->startThread($this->getCurrentThreadId(), get_class($this));
        while(@$this->onRun() === true){
            usleep(300);//to save cpu
        }
        $this->pool->stopThread($this->getCurrentThreadId());
    }
    
    public final function getPool(){
        return $this->pool;
    }
    
    public final function stopThread(){
        $this->onStop();
        usleep(70);
        if($this->isRunning() and $this->kill){
            $this->kill();
            $this->pool->stopThread($this->getCurrentThreadId());
        }
    }
    
    public final function setPool(Pool $pool){
        $this->pool = $pool;
    }
    
    public final function startThread(){
        $this->start();
    }
}
?>
