<?php
namespace TouchIt\Thread;

use TouchIt\Thread\Worker;
use TouchIt\TouchIt;
use pocketmine\Tile\Sign;
use pocketmine\level\Position;
use pocketmine\Server;

class NewSignUpdater extends Worker{
    private $startTime, $position, $creater;
    
    public function __construct(Position $sign, Player $creater){
        $this->position = $pos;
        $this->startTime = time();
        $this->creater = $creater;
    }
    
    public function onRun(){
        if((time() - $this->startTime) > 5)return true;
        if((time() - $this->startTime) > TouchIt::getConfigProvider()->get("createTimeout", 60))return false;
        $tile = $this->position->getLevel()->getTile($this->position);
        if($tile === false or !($tile instanceof Sign))return false;
        $text = $tile->getText();
        if(trim($text[0]) === "" and trim($text[1]) === "" and trim($text[2]) === "" and trim($text[3]) === "")return true;
        elseif(strtolower(trim($text[0])) === "touchit"){
            if($this->creater instanceof Player and $this->creater->isOnline()){
                if(TouchIt::getConfigProvider()->get("AllowPlayerBuild") or $this->creater->hasPermission("touchit.sign.build")){
                    if(!Server::getInstance()->isLevelLoaded(trim($text[3])) and TouchIt::getConfigProvider()->get("checkLevel", true)){
                        $this->creater->sendMessage("[TouchIt] ".TouchIt::getLang("update.new.warning.level"));
                    }
                    TouchIt::getDataProvider()->create($tile);
                    $this->creater->sendMessage("[TouchIt] ".TouchIt::getLang("update.new.create"));
                    $tile->setText("[TouchIt]", "------------", TouchIt::getLang("update.new.wait"), "-TouchIt 2014-");
                }else{
                    $this->creater->sendMessage("[TouchIt] ".TouchIt::getLang("update.new.warning.permission"));
                }
            }else{
                $tile->setText("[".TouchIt::getLang("update.new.warning.title")."]", "------------", $tile->setText("[".TouchIt::getLang("update.new.warning.offline"), "-TouchIt 2014-");
            }
        }
        return false;
    }
}
?>
