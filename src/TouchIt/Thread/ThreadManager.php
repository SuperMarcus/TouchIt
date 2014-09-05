<?php
namespace TouchIt\Thread;

use pocketmine\plugin\PluginLogger;
use pocketmine\Thread;
use pocketmine\tile\Sign;
use TouchIt\TouchIt;
use TouchIt\DataProvider\Provider;
use TouchIt\ConfigAccessor;
use TouchIt\UnitLoader;

class ThreadManager extends Thread{
    private $isenable;

    private $types;
    
    /** @var UpdateThread[] */
    private $update_threads;
    
    /** @var CheckThread */
    private $check_thread;

    /** @var UnitLoader */
    private $unit;
    
    /** @var Provider */
    public $provider;
    
    /** @var PluginLogger */
    public $logger;
    
    /** @var TouchIt */
    public $plugin;
    
    /** @var ConfigAccessor */
    public $config;
    
    public function __construct(TouchIt $plugin, UnitLoader $unit, PluginLogger $logger, Provider $provider, ConfigAccessor $config){
        $this->plugin = $plugin;
        $this->logger = $logger;
        $this->provider = $provider;
        $this->config = $config;
        $this->isenable = false;
        $this->types = $plugin->getTypes();
        $this->unit = $unit;
    }

    /**
     * @param Sign $tile
     */
    public function submitNewSign(Sign $tile){
        $this->check_thread->add($tile);
    }

    /**
     * Start process.
     */
    public function onEnable(){
        $this->isenable = true;
        $this->start(PTHREADS_INHERIT_ALL & ~PTHREADS_INHERIT_CLASSES);
    }

    /**
     * Call when need update signs
     */
    public function update(){
        $this->notify();
    }

    /**
     * Call when plugin disable
     */
    public function onDisable(){
        $this->isenable = false;
        $this->notify();
    }

    /**
     * Main process
     */
    public function run(){
        /** --- Start thread --- */
        $this->logger->info($this->plugin->findLang("thread.start"));
        $this->check_thread = new CheckThread($this, $this->unit->getUnits("unit_check"));
        $this->update_threads = [];
        if(($thread = $this->config->get("thread", 3)) <= 1){
            $this->logger->warning($this->plugin->findLang("thread.warning.notenough"));
            $thread = 3;
        }
        while($thread > 0){
            $update_thread = new UpdateThread($this, $this->unit->getUnits("unit_process"));
            $this->update_threads[] = $update_thread;
            $thread--;
        }
        foreach($this->unit->getUnits("unit_process") as $id => $unit){
            if(is_callable($unit)){
                foreach($this->update_threads as $thread){
                    $thread->addUnit($unit, $id);
                }
            }
        }
        $this->provider->start();//Start provider's thread
        unset($id, $unit, $thread, $update_thread);
        
        /** --- Main process --- */
        while($this->isenable){
            $this->check_thread->start(PTHREADS_INHERIT_ALL & ~PTHREADS_INHERIT_CLASSES);
            $this->check_thread->join();
            $updates = $this->provider->getAll();
            while(count($updates) > 0){
                foreach($this->update_threads as $thread){
                    $thread->submit(@array_shift($updates));
                }
            }
            foreach($this->update_threads as $thread){
                $thread->start(PTHREADS_INHERIT_ALL & ~PTHREADS_INHERIT_CLASSES);
            }
            foreach($this->update_threads as $thread){
                $thread->join();
            }
            unset($updates);
            $this->wait(10);
        }
        $this->provider->stop();//Stop provider's thread
        exit(0);
    }

    public function kill(){
        $this->logger->info($this->plugin->findLang("thread.stop"));
        if($this->isenable){
            $this->isenable = false;
            $this->notify();
            $this->logger->info($this->plugin->findLang("thread.shut"));
            $this->join();
        }
    }
}
?>
