<?php
namespace TouchIt\Thread;

use pocketmine\plugin\PluginLogger;
use pocketmine\tile\Sign;
use TouchIt\TouchIt;
use TouchIt\Thread\Thread;
use TouchIt\DataProvider\Provider;
use TouchIt\ConfigAccessor;

class ThreadManager extends \Thread{
    private $isenable;
    
    /** @var UpdateThread[] */
    private $update_thread;
    
    /** @var CheckThread */
    private $check_thread;
    
    /** @var Provider */
    public $provider;
    
    /** @var PluginLogger */
    public $logger;
    
    /** @var TouchIt */
    public $plugin;
    
    /** @var ConfigAccessor */
    public $config;
    
    public function __construct(TouchIt $plugin, PluginLogger $logger, Provider $provider, ConfigAccessor $config){
        $this->plugin = $plugin;
        $this->logger = $logger;
        $this->provider = $provider;
        $this->config = $config;
        $this->isenable = false;
    }
    
    public function submitNewSign(Sign $tile){
        $this->check_thread->check($tile);
    }
    
    public function start(){
        $this->isenable = true;
        parent::start(PTHREADS_INHERIT_ALL & ~PTHREADS_INHERIT_CLASSES);
    }
    
    public function update(){
        $this->notify();
    }
    
    public function onDisable(){
        $this->isenable = false;
        $this->notify();
        $this->logger->info($this->plugin->findLang("thread.stop"));
    }
    
    public function run(){
        /** --- Start thread --- */
        $this->logger->info($this->plugin->findLang("thread.start"));
        $this->check_thread = new CheckThread($this);
        $this->update_thread = [];
        if(($thread = $this->config->get("thread", 3)) <= 1){
            $this->logget->warning($this->plugin->findLang("thread.warning.notenough"));
            $thread = 3;
        }
        while($thread > 0){
            $this->update_thread[] = new UpdateThread($this);
            $thread--;
        }
        
        /** --- Main process --- */
        while($this->isenable){
            $this->check_thread->start(PTHREADS_INHERIT_ALL & ~PTHREADS_INHERIT_CLASSES);
            $this->check_thread->join();
            $updates = $this->provider->getAll();
            while(count($updates) > 0){
                foreach($this->update_thread as $thread){
                    $thread->submit(@array_shift($updates));
                }
            }
            foreach($this->update_thread as $thread){
                $thread->start(PTHREADS_INHERIT_ALL & ~PTHREADS_INHERIT_CLASSES);
            }
            foreach($this->update_thread as $thread){
                $thread->join();
            }
            unset($updates);
            $this->wait(10);
        }
    }
}
?>
