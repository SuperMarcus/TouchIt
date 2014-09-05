<?php
namespace TouchIt\DataProvider;

use Thread;
use TouchIt\TouchIt;

abstract class Provider extends Thread{
    /** @var TouchIt */
    private $plugin;

    private $isenable;

    /**
     * Initialize method
     * @param TouchIt $plugin
     */
    public final function __construct(TouchIt $plugin){
        $this->plugin = $plugin;
    }

    /**
     * Process
     */
    public final function run(){
        while($this->isEnable()){
            $this->onLoop();
        }
        exit(0);
    }

    public final function start(){
        $this->isenable = true;
        $this->onEnable();
        parent::start(PTHREADS_INHERIT_ALL & ~PTHREADS_INHERIT_CLASSES);
    }

    /**
     * Call when need to stop the thread
     */
    public final function stop(){
        $this->isenable = false;
        $this->notify();
        $this->join();
        $this->onDisable();
    }

    /**
     * @return bool
     */
    public final function isEnable(){
        return (bool) $this->isenable;
    }

    /**
     * Process thread
     */
    abstract public function onLoop();

    /**
     * Add a new sign
     * @param $type
     * @param $data
     * @param $x
     * @param $y
     * @param $z
     * @param $level
     */
    abstract public function create($type, $data, $x, $y, $z, $level);

    /**
     * Check the sign exists or not
     * @param $x
     * @param $y
     * @param $z
     * @param $level
     * @return bool
     */
    abstract public function exists($x, $y, $z, $level);

    /**
     * Remove a sign
     * @param $x
     * @param $y
     * @param $z
     * @param $level
     */
    abstract public function remove($x, $y, $z, $level);

    /**
     * Get all the sign
     * @see Provider::get()
     * @return array
     */
    abstract public function getAll();

    /**
     * Get sign from provider
     *
     * Format:
     * [
     *   "position" => ["x" => int, "y" => int, "z" => int],
     *   "type" => int,
     *   "data" => string
     * ]
     *
     * @param $x
     * @param $y
     * @param $z
     * @param $level
     * @return array
     */
    abstract public function get($x, $y, $z, $level);

    /**
     * Internal method
     */
    abstract public function onEnable();

    /**
     * Internal method
     */
    abstract public function onDisable();
}
?>
