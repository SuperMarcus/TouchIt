<?php
namespace TouchIt\Thread;

use TouchIt\TouchIt;
use TouchIt\Thread\Worker;
use pocketmine\tile\Sign;

class TeleportSignUpdater extends Worker{
    private $stop;
    
    public function __construct(){
        $this->stop = true;
    }
    
    public function onRun(){
        $updates = TouchIt::getManager()->needUpdates(TouchIt::SIGN_TELEPORT);
        $config = TouchIt::getConfigProvider();
        
        if(count($updates) > 0){
            foreach($updates as $level){
                $signs = TouchIt::getDataProvider()->getByTargetLevel($level);
                if(count($signs) > 0){
                    foreach($signs as $sign){
                        if($sign['type'] !== TouchIt::SIGN_TELEPORT)continue;
                        $tile = $sign['position']->getLevel()->getTile($sign['position']);
                        if($tile instanceof Sign){
                            if(!($target = Server::getInstance()->getLevelByName($sign['target']))){
                                $tile->setText("[".$config['name']."]", TouchIt::getLang("update.level.notloaded.line2"), TouchIt::getLang("update.level.notloaded.line3"), TouchIt::getLang("update.level.notloaded.line4"));
                            }elseif(count($target->getPlayers()) >= TouchIt::getConfigProvider()->get("maxPeople", 20)){
                                $tile->setText("[".$config['name']."]", TouchIt::getLang("update.level.limit.line2"), TouchIt::getLang("update.level.limit.line3"), TouchIt::getLang("update.level.limit.line4"));
                            }else{
                                $tile->setText("[".$config['name']."]", $sign['description'], TouchIt::getLang("update.level.limit.line3"), min(count($target->getPlayers()), "[".TouchIt::getConfigProvider()->get("maxPeople", 20))."/".TouchIt::getConfigProvider()->get("maxPeople", 20)."]");
                            }
                        }
                    }
                }
            }
        }
        return $this->stop;
    }
    
    public function onStop(){
        $this->stop = false;
    }
}
?>
