<?php
namespace TouchIt\DataProvider;

use TouchIt\TouchIt;
use TouchIt\DataProvider\Provider;
use TouchIt\Exchange\SignData;
use TouchIt\Exchange\SignContentsData;
use pocketmine\tile\Sign;
use pocketmine\level\Level;
use pocketmine\level\Position;
use pocketmine\Server;

class SQLDataProvider implements Provider{
    private $database;
    
    public function __construct(){}
    
    public function getByLevel($levelName){
        $result = [];
        $query = $this->database->query("SELECT * FROM index WHERE level = ".$levelName.";");
        if($data instanceof \SQLite3Result){
            while($value = $data->fetchArray(SQLITE3_ASSOC)){
                $level = Server::getInstance()->getLevelByName($data['level']);
    			if(!$level)continue;
    			$vector = explode("_", $data['id']);
    			$info = $this->get(new Position((int) $vector[0], (int) $vector[1], (int) $vector[2], $level));
    			if($info !== null)$result[] = $info;
    			unset($info);
    			unset($vector);
    			unset($level);
            }
        }
        return $result;
    }
    
    public function create(Sign $sign){
    	$text = $sign->getText();
    	$type = 0;
    	if(substr(trim($text[2]), 0, 1) === "/"){
    		$type = TouchIt::SIGN_COMMAND;
    		$command = substr(trim($text[2]), 1).trim($text[3]);
    		$this->database->exec("INSERT INTO command (id, command, description) VALUES ('".$this->getId($sign)."', '".$command."', '".((trim($text[1]) === "") ? "Tap to run command." : trim($text[1]))."')");
    	}elseif(trim($text[1]) === "" and trim($text[2]) === "" and trim($text[3]) === ""){
    	    $type = TouchIt::SIGN_BOARDCASE;
    	}else{
    	    $type = TouchIt::SIGN_TELEPORT;
    	    $this->database->exec("INSERT INTO teleport (id, level, target, description) VALUES ('".$this->getId($sign)."', '".$sign->getLevel()->getName()."', '".trim($text[2])."', '".((trim($text[3]) === "") ? "To: ".trim($text[2]) : trim($text[3]))."')");
    	}
    	$this->database->exec("INSERT INTO index (id, type) VALUES ('".$this->getId($sign)."', ".$type.")");
    	return $type;
    }
    
    public function exists(Position $pos){
    	$query = $this->database->query("SELECT * FROM index WHERE id = ".$this->getId($pos));
    	if($data instanceof \SQLite3Result){
    		$id = $data->fetchArray(SQLITE3_ASSOC);
    		return isset($id['id']) and $id['id'] === $this->getId($pos);
    	}
    	return false;
    }
    
    public function remove(Position $pos){
    	if($this->exists($pos)){
    		$data = $this->database->query("SELECT * FROM index WHERE id = ".$this->getId($pos));
    		if($data instanceof \SQLite3Result){
    			$data = $data->fetchArray(SQLITE3_ASSOC);
    			if(isset($data['id'] and $data['id'] === $this->getId($pos))){
    				switch((int) $data['type']){
    					case TouchIt::SIGN_TELEPORT:
    					    return $this->database->exec("DELETE FROM index WHERE id = ".$this->getId($pos)) and $this->database->exec("DELETE FROM teleport WHERE id = ".$this->getId($pos));
    					    break;
    					case TouchIt::SIGN_COMMAND:
    						return $this->database->exec("DELETE FROM index WHERE id = ".$this->getId($pos)) and $this->database->exec("DELETE FROM command WHERE id = ".$this->getId($pos));
    						break;
    					default:
    						return $this->database->exec("DELETE FROM index WHERE id = ".$this->getId($pos));
    				}
    			}
    		}
    	}
    	return false;
    }
    
    public function getAll(){
    	$query = $this->database->query("SELECT * FROM index WHERE id = ".$this->getId($pos).";");
    	$result = [];
    	if($query instanceof \SQLite3Result){
    		while($data = $query->fetchArray(SQLITE3_ASSOC)){
    			$level = Server::getInstance()->getLevelByName($data['level']);
    			if(!$level)continue;
    			$vector = explode("_", $data['id']);
    			$info = $this->get(new Position((int) $vector[0], (int) $vector[1], (int) $vector[2], $level));
    			if($info !== null)$result[] = $info;
    			unset($info);
    			unset($vector);
    			unset($level);
    		}
    	}
    	return $result;
    }
    
    public function get(Position $pos){
    	if(!($pos->getLevel() instanceof Level))return null;
    	
    	$query = $this->database->query("SELECT * FROM index WHERE id = ".$this->getId($pos).";");
    	if($query instanceof \SQLite3Result){
    		$data = $query->fetchArray(SQLITE3_ASSOC);
    		$query->finalize();
    		unset($query);
    		if(isset($data['id']) and $data['id'] === $this->getId($pos)){
    			switch((int) $data['type']){
    				case TouchIt::SIGN_TELEPORT:
    					$query = $this->database->query("SELECT * FROM teleport WHERE id = ".$this->getId($pos).";");
    					unset($data);
    					if($query instanceof \SQLite3Result){
    						$data = $query->fetchArray(SQLITE3_ASSOC);
    					    if(isset($data['id']) and $data['id'] === $this->getId($pos)){
    					    	return [
    					    		"type" => TouchIt::SIGN_TELEPORT,
    					    		"position" => $pos,
    					    		"target" => $data['target'],
    					    		"level" => $data['level'],
    					    		"description" => $data['description']
    					    	];
    					    }
    					}
    					break;
    				case TouchIt::SIGN_COMMAND:
    					$query = $this->database->query("SELECT * FROM command WHERE id = ".$this->getId($pos).";");
    					unset($data);
    					if($query instanceof \SQLite3Result){
    						$data = $query->fetchArray(SQLITE3_ASSOC);
    						if(isset($data['id']) and $data['id'] === $this->getId($pos)){
    					    	return [
    					    		"type" => TouchIt::SIGN_COMMAND,
    					    		"position" => $pos,
    					    		"command" => $data['command'],
    					    		"description" => $data['description']
    					    	];
    					    }
    					}
    					break;
    				case TouchIt::SIGN_BOARDCASE:
    					return ["type" => TouchIt::SIGN_BOARDCASE];
    					break;
    			}
    		}
    	}
    	return null;
    }
    
    public function onEnable(){
    	$this->loadDataBase();
    }
    
    public function onDisable(){
    	$this->database->close();
    	unset($this->database);
    }
    
    private function getId(Position $pos){
    	return $pos->getFloorX()."_".$pos->getFloorY()."_".$pos->getFloorZ()."_".$pos->getLevel()->getName();
    }
    
    private function loadDataBase(){
    	if(file_exists(TouchIt::getTouchIt()->getDataFolder()."data.db")){
    		$this->database = new \SQLite3(TouchIt::getTouchIt()->getDataFolder()."data.db", SQLITE3_OPEN_READWRITE|SQLITE3_OPEN_CREATE);
    		$this->database->exec(stream_get_contents(TouchIt::getTouchIt()->getResource("database/sqlite3.sql")));
    	}else{
    		$this->database = new \SQLite3(TouchIt::getTouchIt()->getDataFolder()."data.db", SQLITE3_OPEN_READWRITE);
    	}
    }
}
?>
