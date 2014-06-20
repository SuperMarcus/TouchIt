<?php
namespace TouchIt\DataProvider;

use TouchIt\TouchIt;
use TouchIt\DataProvider\Provider;
use pocketmine\tile\Sign;
use pocketmine\level\Level;
use pocketmine\level\Position;

class SQLDataProvider implements Provider{
    private $database, $main, $lock;
    
    public function __construct(TouchIt $touchit){
        $this->lock = false;
        $this->main = $touchit;
        $this->loadDataBase();
    }
    
    public function getSign(Position $pos){
        $level = $pos->getLevel();
        if(!$level or ($level instanceof Level) === false)return false;
        return true;
    }
    
    public function addSign(Sign $sign){
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
