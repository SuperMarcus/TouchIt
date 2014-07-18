<?php
namespace TouchIt\Exchange;

use TouchIt\TouchIt;
use TouchTt\Exchange\ExchangeInformation;
use pocketmine\level\Position;
use pocketmine\level\Level;
use pocketmine\Server;

class SignData implements ExchangeInformation{
    public $data;
    private $check;
    
    public function __construct($data){
        $this->data = $data;
    }
    
    public function getDescription(){
        return $this->data['description'];
    }
    
    public function isTargetLevelLoaded(){
        return Server::getInstance()->isLevelLoaded($this->data['targetLevel']);
    }
    
    public function isFromLevelLoaded(){
        return Server::getInstance()->isLevelLoaded($this->data['signLevel']);
    }
    
    public function getTargetLevel($name = false){
        if($name)return $this->data['targetLevel'];
        if($this->isTargetLevelLoaded())return Server::getInstance()->getLevelByName($this->data['targetLevel']);
        else return false;
    }
    
    public function getLevel($name = false){
        if($name)return $this->data['signLevel'];
        if($this->isToLevelLoaded())return Server::getInstance()->getLevelByName($this->data['signLevel']);
        else return false;
    }
    
    public function getPosition(){
        return new Position((int) $this->data['x'], (int) $this->data['y'], (int) $this->data['z'], $this->getLevel());
    }
    
    public function getTile(){
        if(!($level = $this->getLevel()))return false;
        return $level->getTile($this->getPosition());
    }
}
?>
