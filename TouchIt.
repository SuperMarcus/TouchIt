<?php
 
/*
__PocketMine Plugin__
name=TouchIt
description=A sign portal system.
version=1.0
apiversion=12,13
author=Marcus
class=touchIt
*/
 
class touchIt implements Plugin{
    private $api, $server, $config, $sql;
 
    public function __construct(ServerAPI $api, $server = false){
        $this->api  = $api;
		$this->server = ServerAPI::request();
    }
     
    public function init(){
	    $this->loadCfg();
		
		if($this->config->get("enable") and $this->loadDataBase()){
            $this->api->addHandler('player.block.touch', array($this, 'touchHandler'), (int) $this->config->get("priority"));
			$this->api->addHandler('tile.update', array($this, 'tileHandler'), (int) $this->config->get("priority"));
		}
    }
	
	public function tileHandler($data, $event){
	    if($data instanceof Tile and $data->class === TILE_SIGN and ($player = $this->api->player->get($data->data['creator'])) instanceof Player){
		    $text = $data->getText();
			if(strtolower($text[0]) === "touchit"){
			    if(!$this->config->get("allowPlayer") and !$this->api->ban->isOp((($this->config->get("opCheckByLowerName")) ? $player->iusername : $player->username))){//check permission
				    $player->sendChat("[TouchIt] You don't have permission to build teleport sign.");
					$data->setText("[WARNING]", "---------", "no permission", "to build");
					return false;
				}
				if($this->config->get("checkLevel") and $this->api->level->get($text[2]) === false){//check level
				    $player->sendChat("[TouchIt] This world has not been loaded.");
					$player->sendChat("[TouchIt] Please check line 3 on this sign.");
					$data->setText("[WARNING]", "---------", "has not", "loaded");
					return false;
				}
				$this->sql->exec("INSERT INTO sign(level, toLevel, x, y, z, hasDescription) VALUES ('".$data->level->getName()."', '".$text[2]."', ".$data->x.", ".$data->y.",".$data->z.", ".(($text[1] === "")?"0":"1").")");
				if($text[1] !== ""){
				    $id = $this->sql->query("SELECT id FROM sign WHERE hasDescription = 1 AND level = '".$data->level->getName()."' AND toLevel = '".$text[2]."' AND x = ".$data->x." AND y = ".$data->y." AND z = ".$data->z.";");
				}
				//insert into database
			}
		}
	}
	
	private function loadDataBase(){
	    if(class_exists("SQLite3")){
		    $this->sql = new SQLite3($this->api->plugin->configPath($this)."database.sql", SQLITE3_OPEN_READWRITE|SQLITE3_OPEN_CREATE);
			$this->sql->exec("CREATE TABLE IF NOT EXISTS sign(id INTEGER PRIMARY KEY AUTOINCREMENT, level TEXT NOT NULL, toLevel TEXT NOT NULL, x INTEGER NOT NULL, y INTEGER NOT NULL, z INTEGER NOT NULL, hasDescription INTEGER DEFAULT 0)");
			$this->sql->exec("CREATE TABLE IF NOT EXISTS description(id INTEGER PRIMARY KEY NOT NULL, description STRING NOT NULL)");
		}else{
		    console("[WARNING] Can't load TouchIt: Class \"SQLite3\" doesn't exists.", true, true, 0);
			$this->config->set("enable", false);
			return false;
		}
	}
	
	private function loadCfg(){
	    $this->config = new Config($this->api->plugin->configPath($this)."config.cnf", CONFIG_CNF, array(
		    "priority" => 5;
			"allowPlayer" => false;
			"opCheckByLowerName" => true;
			"checkLevel" => true;
			"enable" => true;
		));
	}
	
    public function __destruct()$this->sql->close;
}
?>
