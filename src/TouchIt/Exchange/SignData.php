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
    
    public function __construct($query, \SQLite3 $database){
        $this->check = false;
        if(!$query or $query instanceof \SQLite3Result)return;
        $this->data = $query->fetchArray(SQLITE3_ASSOC);
        if(!$this->data or !is_array($this->data))return;
        if($this->data['hasDescription'] == 1){
            $this->data['hasDescription'] = true;
            $this->data['description'] = $database->query("SELECT description FROM description WHERE id = ".$this->data['id'].";")->fetchArray(SQLITE3_ASSOC)['description'];
        }else $this->data['hasDescription'] = false;
    }
    
    public function hasDescription(){
        return $this->data['hasDescription'];
    }
    
    public function getDescription(){
        if($this->data['hasDescription'])return $this->data['description'];
        else return "To: ".$this->data['toLevel'];
    }
    
    public function isToLevelLoaded(){
        return Server::getInstance()->isLevelLoaded($this->data['toLevel']);
    }
    
    public function isFromLevelLoaded(){
        return Server::getInstance()->isLevelLoaded($this->data['level']);
    }
    
    public function getToLevel($name = false){
        if($this->isToLevelLoaded())return Server::getInstance()->getLevelByName($this->data['toLevel']);
        else return $name ? $this->data['toLevel'] : false;
    }
    
    public function getFromLevel($name = false){
        if($this->isToLevelLoaded())return Server::getInstance()->getLevelByName($this->data['level']);
        else return $name ? $this->data['level'] : false;
    }
    
    public function getPosition(){
        return new Position((int) $this->data['x'], (int) $this->data['y'], (int) $this->data['z'], $this->getFromLevel());
    }
    
    public function getTile(){
        if(!($level = $this->getFromLevel()))return false;
        return $level->getTile($this->getPosition());
    }
    
    public function getId(){
        return $this->data['id'];
    }
}
?>
