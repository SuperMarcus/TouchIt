<?php
namespace TouchIt\Thread;

use pocketmine\tile\Sign;

class CheckThread extends \Thread{
    /** @var ThreadManager  */
    private $thread_manager;

    /** @var \WeakRef[] */
    private $tiles;
    
    public function run(){
        while(count($this->tiles) > 0){
            $info = @array_shift($this->tiles);
            if($info === null or (time() - $info[1]) > $this->thread_manager->config->get("CreateTimeout", 60))break;
            if(!$info[0]->valid())continue;
            $info[0]->acquire();
            $tile = $info[0]->get();
            if($tile instanceof Sign){
                $text = $tile->getText();
                foreach($this->check_unit as $unit){
                    if(is_callable($unit)){
                        if(true === @call_user_func($unit, $text, $tile, $this->thread_manager)){
                            break;
                        }
                    }
                }
                /*if($text[0] === "" and $text[1] === "" and $text[2] === "" and $text[3] === ""){
                    $next_check[] = $tile;
                    $info[0]->release();
                }else{
                    if(strtolower(trim($text[0])) === "touchit"){
                        if(substr($text[2], 0, 1) === "/" or substr($text[2], 0, 1) === "\\" or ($text[1] === "" and $text[2] === "" and $text[3] === "")){
                            $this->thread_manager->provider->create($info[0]->get());
                            $info[0]->release();
                        }else{
                            if($this->thread_manager->config->get("checkLevel", true) and !$this->thread_manager->plugin->getServer()->isLevelLoaded(trim($text[2]))){
                                $info[0]->get()->setText("[".$this->thread_manager->plugin->findLang("update.new.warning.title")."]", "--------------", $this->thread_manager->plugin->findLang("update.new.warning.level.line3")." ".trim($text[2]), $this->thread_manager->plugin->findLang("update.new.warning.level.line4"));
                                $info[0]->release();
                            }else{
                                $info[0]->get()->setText("[Teleport]", $this->thread_manager->plugin->findLang("update.new.wait"), $this->thread_manager->plugin->findLang("update.new.wait.to")." ".$text[1], "- TouchIt -");
                                $this->thread_manager->provider->create($info[0]->get());
                                $info[0]->release();
                            }
                        }
                    }
                }*/
            }
        }
        exit(0);
    }
    
    public function __construct(ThreadManager $thread_manager, array $unit){
        $this->thread_manager = $thread_manager;
        $this->check_unit = $unit;
    }
    
    protected function add($tile){
        $this->tiles[] = new \WekRef($tile);
    }
}
?>
