<?php
namespace TouchIt\DataProvider;

abstract class ThreadProvider extends Provider{
    /**
     * Process
     */
    public final function run(){
        while($this->isEnable()){
            $this->onLoop();
        }
        exit(0);
    }

    public final function startThread(){
        $this->isenable = true;
        $this->onEnable();
        $this->start(PTHREADS_INHERIT_ALL & ~PTHREADS_INHERIT_CLASSES);
    }

    /**
     * Process thread
     */
    abstract public function onLoop();
}
?>
