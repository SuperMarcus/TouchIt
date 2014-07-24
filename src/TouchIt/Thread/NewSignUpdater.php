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
                if(TouchIt::getConfigProvider()->get("AllowPlayerBuild") or $this->creater->isOp()){
                    if(!Server::getInstance()->isLevelLoaded(trim($text[3])) and TouchIt::getConfigProvider()->get("checkLevel", true)){
                        $this->creater->sendMessage("[TouchIt] The level you set is not loaded.");
                    }
                    TouchIt::getDataProvider()->create($tile);
                    $this->creater->sendMessage("[TouchIt] Your sign has been create.");
                    $tile->setText("[TouchIt]", "------------", "Waiting...", "-TouchIt 2014-");
                }else{
                    $this->creater->sendMessage("[TouchIt] You are not allowed to build this sign.");
                }
            }else{
                $tile->setText("[WARNING]", "------------", "Player offline", "-TouchIt 2014-");
            }
        }
        return false;
    }
}
?>
