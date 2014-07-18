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
        $this->onRun();
        $this->pool->stopThread($this->getCurrentThreadId());
    }
    
    public final function stopThread(){
        $this->onStop();
        usleep(70);
        if($this->isRunning() and $this->kill){
            $this->kill();
        }
    }
    
    public final function startThread(){
        $this->start();
    }
    
    public final function __construct(Pool $pool){
        $this->pool = $pool;
    }
}
?>
