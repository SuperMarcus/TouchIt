<?php
namespace TouchIt\Thread;

use TouchIt\ThreadManager;
use pocketmine\tile\Sign;

class CheckThread extends \Thread{
    private $thread_manager, $tiles;
    
    public function run(){
        $next_check = [];
        while($this->check(null, 3) > 0){
            $info = $this->check(null, 2);
            if($info === null or (time() - $info[1]) > $this->thread_manager->config->get("createTimeout", 60))break;
            if(!$info[0]->valid())continue;
            $info[0]->acquire();
            $tile = $ref[0]->get();
            if($tile instanceof Sign){
                $text = $tile->getText();
                if($text[0] === "" and $text[1] === "" and $text[2] === "" and $text[3] === ""){
                    $next_check[] = $tile;
                    $info[0]->release();
                }else{
                    if(strtolower(trim($text[0])) === "touchit"){
                        if(substr($text[2], 0, 1) === "/" or substr($text[2], 0, 1) === "\\" or ($text[1] === "" and $text[2] === "" and $text[3] === "")){
                            $this->thread_manager->provider->create($info[0]->get());
                            $info[0]->release();
                        }else{
                            if($this->thread_manager->config->get("checkLevel", true) and !$this->thread_manager->plugin->getServer()->isLevelLoaded(trim($text[2]))){
                                $info[0]->get()->setText("[".$this->thread_manager->plugin->findLang("update.new.warning.title")."]", );
                            }
                        }
                    }
                }
            }
        }
    }
    
    public function __construct(ThreadManager $thread_manager){
        $this->thread_manager = $thread_manager;
    }
    
    protected function check($tile, $action = 0){
        switch($action){
            case 0:
                if(!($tile instanceof Sign))break;
                $this->tile[] = [(new \WeakRef($tile)), time()];
                break;
            case 1:
                return @array_shift($this->tile);
            case 2:
                return count($this->tile);
        }
    }
}
?>
