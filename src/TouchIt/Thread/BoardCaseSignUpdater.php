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
                    $message = str_split(TouchIt::getManager()->getAnnouncement(), 15);
                    if(count($message) > 3){
                        $message[3] = substr($message[2], 0, -3)."...";
                        $tile->setText(TouchIt::getLang("update.boardcase.title"), ($message[0], isset($message[1]) ? $message[1] : ""), (isset($message[2]) ? $message[2] : ""));
                    }
                }
            }
        }
        return true;
    }
}
?>
