<?php
namespace TouchIt;

use TouchIt\TouchIt;
use TouchIt\Thread\Worker;
use pocketmine\tile\Sign;

class BoardCaseSignUpdater extends Worker{
    public function __construct(){}
    
    public function onRun(){
        $updates = TouchIt::getManager()->needUpdates(TouchIt::SIGN_BOARDCASE);
        
        if(count($updates) > 0){
            foreach($updates as $sign){
                $pos = $sign['position'];
                $tile = $pos->getLevel()->getTile($pos);
                if($tile instanceof Sign){
                    $massage = TouchIt::getManager()->getAnnouncement();
                }
            }
        }
        return $this->stop;
    }
}
?>
