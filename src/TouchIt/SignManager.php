<?php
namespace TouchIt;

use TouchIt\TouchIt;
use TouchIt\Exchange\signInfo;
use TouchIt\DataProvider\CNFDataProvider;
use TouchIt\DataProvider\SQLDataProvider;
use pocketmine\Server;

class SignManager{
    private $touchit, $config, $database;
    
    public function __construct(TouchIt $touchit, CNFDataProvider &$config, SQLDataProvider &$database){
        $this->touchit = $touchit;
        $this->config = $config;
        $this->database = $database;
    }
    
    public function onUpdateEvent(Event $event){
        $contents = $this->database->getContents();
        $server = Server::getInstance();
        while($sign = $contents->getNext()){
            $tile = $sign->getTile();
        }
    }
}
?>
