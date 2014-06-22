<?php
namespace TouchIt\DataProvider;

use TouchIt\TouchIt;
use TouchIt\DataProvider\signProvider;
use TouchIt\Exchange\SignData;
use TouchIt\Exchange\SignContentsData;
use pocketmine\tile\Sign;
use pocketmine\level\Level;
use pocketmine\level\Position;

class SQLDataProvider implements signProvider{
    private $database, $main, $lock;
    
    public function __construct(TouchIt $touchit){
        $this->lock = false;
        $this->main = $touchit;
        $this->loadDataBase();
    }
    
    public function unlockProvider(){
    	$this->lock = false;
    }
    
    public function lockProvider(){
    	$this->lock = true;
    }
    
    public function getContents(){
    	if($this->lock)return false;
    	return new SignContentsData($this->database);
    }
    
    public function removeSign(Position $pos){
    	if($this->lock)return false;
    	$sign = $this->database->query("SELECT * FROM sign WHERE level = '".$pos->getLevel->getName()."' AND x = ".(int) $pos->x." AND y = ".(int) $pos->y." AND z = ".(int) $pos->z.";");
    	if(!sign or !($sign instanceof SQLite3Result))return false;
    	$sign = $sign->fetchArray(SQLITE3_ASSOC);
    	if(!isset($sign['id']))return false;
    	if($sign['hasDescription'] == 1){
    		$this->database->exec("DELETE FROM description WHERE id = ".$sign['id']);
    	}
    	return $this->database->exec("DELETE FROM sign WHERE id = ".$sign['id']);
    }
    
    public function getSign(Position $pos){
    	if($this->lock)return false;
        $level = $pos->getLevel();
        if(!$level or ($level instanceof Level) === false)return false;
        $query = $this->sql->query("SELECT * FROM sign WHERE level = '".$data["player"]->level->getName()."' AND x = ".(int) $data["target"]->x." AND y = ".(int) $data["target"]->y." AND z = ".(int) $data["target"]->z.";");//get data
        return new SignData($query, $this->database);
    }
    
    public function addSign(Sign $sign){
    	if($this->lock)return false;
        $text = $sign->getText();
        $level = $sign->getLevel();
        if(!$level or ($level instanceof Level) === false)return false;
        $this->database->exec("INSERT INTO sign(level, toLevel, x, y, z, hasDescription) VALUES ('".$level->getName()."', '".$text[2]."', ".(int) $sign->x.", ".(int) $sign->y.",".(int) $sign->z.", ".(($text[1] === "")?"0":"1").")");//Write to DataBase
        if($text[1] !== ""){//Write description
			$id = $this->database->query("SELECT id FROM sign WHERE hasDescription = 1 AND level = '".$level->getName()."' AND toLevel = '".$text[2]."' AND x = ".$sign->x." AND y = ".$sign->y." AND z = ".$sign->z.";")->fetchArray(SQLITE3_ASSOC)['id'];
            $this->database->exec("INSERT INTO description(id, description) VALUES (".$id.", '".$text[1]."')");
		}
		return true;
    }
    
    private function loadDataBase(){
        if(!extension_loaded("sqlite3")){
            $this->lock = true;
            return;
        }
        if($this->main->isPhar()){
            @mkdir($this->main->getFile().DIRECTORY_SEPARATOR."data".DIRECTORY_SEPARATOR);
            $this->database = new \SQLite3($this->main->getFile().DIRECTORY_SEPARATOR."data".DIRECTORY_SEPARATOR, SQLITE3_OPEN_READWRITE|SQLITE3_OPEN_CREATE);
        }else{
            @mkdir($this->main->getDataFolder());
            $this->database = new \SQLite3($this->main->getDataFolder(), SQLITE3_OPEN_READWRITE|SQLITE3_OPEN_CREATE);
        }
        $this->database->exec("CREATE TABLE IF NOT EXISTS sign(id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, level TEXT NOT NULL, toLevel TEXT NOT NULL, x INTEGER NOT NULL, y INTEGER NOT NULL, z INTEGER NOT NULL, hasDescription INTEGER DEFAULT 0)");
        $this->database->exec("CREATE TABLE IF NOT EXISTS description(id INTEGER PRIMARY KEY NOT NULL, description STRING NOT NULL)");
    }
}
?>
