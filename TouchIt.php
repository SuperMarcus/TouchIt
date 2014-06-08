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
            $this->api->addHandler('player.teleport.level', array($this, 'teleportHandler'), (int) $this->config->get("priority"));
			$this->api->addHandler('tile.update', array($this, 'tileHandler'), (int) $this->config->get("priority"));
			$this->api->schedule(20 * 10, array($this,"updateSign"), array(true), false);//first update
			if($this->config->get("useCommand"))$this->api->console->register("touch", "TouchIt command.", array($this, "commandHandler"));
		}
    }
    
    public function updateSign($isSchedule = false){
        unset($this->config);
        $this->loadCfg();
        $query = $this->sql->query("SELECT * FROM sign;");
        
        while(($sign = $query->fetchArray(SQLITE3_ASSOC)) !== false){
            $toLevel = $this->api->level->get($sign['toLevel']);
            $level = $this->api->level->get($sign['level']);
            if($level === false){
			    console("[DEBUG] TouchIt FROM Level: ".$sign['level']." NOT LOADED!", true, true, 2);
				continue;
			}
			if($toLevel === false){
			    console("[DEBUG] TouchIt TO Level: ".$sign['toLevel']." NOT LOADED!", true, true, 2);
				$tile = $this->api->tile->get(new Position((int) $sign['x'], (int) $sign['y'], (int) $sign['z'], $level));
				if($tile instanceof Tile and $tile->class === TILE_SIGN){
				    $tile->setText("[".$this->config->get("name")."]", "NOT OPEN", ($this->config->get("showCount") ? "* * *" : $this->config->get("informationLine1")), ($this->config->get("showCount") ? "* * *" : $this->config->get("informationLine2")));//set sign not loaded
				}
				continue;
			}
            $tile = $this->api->tile->get(new Position((int) $sign['x'], (int) $sign['y'], (int) $sign['z'], $level));
            if($tile instanceof Tile and $tile->class === TILE_SIGN){
                if((int) $sign['hasDescription'] === 1){
                    $description = $this->sql->query("SELECT description FROM description WHERE id = ".$sign['id'].";")->fetchArray(SQLITE3_ASSOC)['description'];
                }
                
                $count = count($this->api->player->getAll($toLevel));//get count
                $tile->setText("[".$this->config->get("name")."]", (isset($description) ? $description : "To: ".$sign['toLevel']), ($this->config->get("showCount") ? "Peoples count" : $this->config->get("informationLine1")), ($this->config->get("showCount") ? "[".min($count, $this->config->get("maxPeople"))."/".$this->config->get("maxPeople")."]" : $this->config->get("informationLine2")));
                //set text, if the count of the people in this world are more than the number you set, it will show the number you set.
            }elseif($this->config->get("autoDeleteSign")){
                $this->sql->exec("DELETE FROM sign WHERE id = ".$sign['id']);
                if((int) $sign['hasDescription'] === 1)
                    $this->sql->exec("DELETE FROM description WHERE id = ".$sign['id']);
                console("[DEBUG] Can't find sign in database! (ID: ".$sign['id'].")", true, true, 2);
                console("[DEBUG] This sign has been DELETE!", true, true, 2);
            }else{
                console("[DEBUG] Can't find sign in database! (ID: ".$sign['id'].")", true, true, 2);
            }
            
            if(isset($description))unset($description);
            unset($count);
            unset($tile);
        }
		
		if($isSchedule and $this->config->get("enable") and $this->config->get("autoUpdateSign"))$this->api->schedule(20 * 10, array($this,"updateSign"), array(true), false);//auto updates sign
    }
    
    public function teleportHandler($data, $event){
        $this->updateSign();
    }
	
	public function commandHandler($cmd, $params, $issuer, $alias){
	    $output = "";
	    switch($params[0]){
		    case "update":
			    $this->updateSign();
				$output .= "Done!";
				break;
		}
	    return $output;
	}
    
    public function touchHandler($data, $event){
        $tile = $this->api->tile->get(new Position($data["target"], false, false, $data["target"]->level));
        if($tile instanceof Tile and $tile->class === TILE_SIGN){
            $query = $this->sql->query("SELECT * FROM sign WHERE level = '".$data["player"]->level->getName()."' AND x = ".(int) $data["target"]->x." AND y = ".(int) $data["target"]->y." AND z = ".(int) $data["target"]->z.";");//get data
            if($query !== false and ($query = $query->fetchArray(SQLITE3_ASSOC)) !== false){
                if($data['type'] === "break"){//for BREAK
                    if($this->config->get("allowPlayerBreak") and $this->api->ban->isOp((($this->config->get("opCheckByLowerName")) ? $player->iusername : $player->username))){
                        console("[DEBUG] Player: ".$player->username." trying to break teleport sign!", true, true, 2);
                        $data["player"]->sendChat("[TouchIt] You can not break the teleport sign!");
                        return false;
                    }else{
                        $this->sql->exec("DELETE FROM sign WHERE id = ".$query['id']);//delete sign from database
                        if((int) $query['hasDescription'] === 1)
                            $this->sql->exec("DELETE FROM description WHERE id = ".$query['id']);
                        console("[DEBUG] A teleport sign has been delete! (ID: ".$query['id'].")", true, true, 2);
                        $data["player"]->sendChat("[TouchIt] This sign has been delete!");
                        return true;
                    }
                }
                
                if(($level = $this->api->level->get($query['toLevel'])) === false){
                    $data['player']->sendChat("[TouchIt] This world is not open!");
                }else{
                    $data['player']->sendChat("[TouchIt] Teleport to ".$query['toLevel']);
                    $data['player']->teleport($this->config->get("safeSpawn") ? $level->getSafeSpawn() : $level->getSpawn());
                }
                
                return false;
            }
        }
    }
	
	public function tileHandler($data, $event){
	    if($data instanceof Tile and $data->class === TILE_SIGN and ($player = $this->api->player->get($data->data['creator'])) instanceof Player){
		    $text = $data->getText();
			if(strtolower($text[0]) === "touchit"){
			    if(!$this->config->get("allowPlayerBuild") and !$this->api->ban->isOp((($this->config->get("opCheckByLowerName")) ? $player->iusername : $player->username))){//check permission
				    $player->sendChat("[TouchIt] You don't have permission to build teleport sign.");
                    console("[DEBUG] Player: ".$player->username." trying to create teleport sign!", true, true, 2);
					$data->setText("[WARNING]", "---------", "no permission", "to build");
					return false;
				}
				if($this->config->get("checkLevel") and $this->api->level->get($text[2]) === false){//check level
				    $player->sendChat("[TouchIt] This world has not been loaded.");
					$player->sendChat("[TouchIt] Please check line 3 on this sign.");
					$data->setText("[WARNING]", "---------", "has not", "loaded");
					return false;
				}
				$this->sql->exec("INSERT INTO sign(level, toLevel, x, y, z, hasDescription) VALUES ('".$data->level->getName()."', '".$text[2]."', ".(int) $data->x.", ".(int) $data->y.",".(int) $data->z.", ".(($text[1] === "")?"0":"1").")");
				if($text[1] !== ""){
				    $id = $this->sql->query("SELECT id FROM sign WHERE hasDescription = 1 AND level = '".$data->level->getName()."' AND toLevel = '".$text[2]."' AND x = ".$data->x." AND y = ".$data->y." AND z = ".$data->z.";")->fetchArray(SQLITE3_ASSOC)['id'];
                    $this->sql->exec("INSERT INTO description(id, description) VALUES (".$id.", '".$text[1]."')");
				}
				//insert into database
                
                $player->sendChat("[TouchIt] Successful!.");
                $data->setText("[TouchIt]", "---------", "Loading...", "Loading...");
                
                $this->updateSign();
                return false;
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
		return true;
	}
	
	private function loadCfg(){
	    $this->config = new Config($this->api->plugin->configPath($this)."config.cnf", CONFIG_CNF, array(
		    "priority" => 5,
            "name" => "Teleport",
            "maxPeople" => 20,
            "showCount" => true,
            "informationLine1" => "Touch Sign",
            "informationLine2" => "to teleport",
			"allowPlayerBuild" => false,
            "allowPlayerBreak" => false,
			"opCheckByLowerName" => true,
            "autoDeleteSign" => true,
			"autoUpdateSign" => true,
			"autoUpdateTime" => 10,
            "safeSpawn" => true,
			"checkLevel" => true,
			"useCommand" => true,
			"enable" => true
		));
        
        if(strtolower($this->config->get("name")) === "touchit"){
            console("[WARNING] TouchIt config error: \"name\" Can not be \"".$this->config->get("name")."\" !", true, true, 0);
            $this->config->set("name", "Touch to teleport");
        }
		
		if($this->config->get("autoUpdateSign")){
		    if(!$this->config->exists("autoUpdateTime"))$this->config->set("autoUpdateTime", 10);
		}elseif($this->config->exists("autoUpdateTime"))$this->config->remove("autoUpdateTime");
        
        if($this->config->get("showCount")){
            $this->config->remove("informationLine1");
            $this->config->remove("informationLine2");
        }else{
            if(!$this->config->exists("informationLine1"))$this->config->set("informationLine1", "Touch Sign");
            if(!$this->config->exists("informationLine2"))$this->config->set("informationLine2", "to teleport");
        }
        
        $this->config->save();
	}
	
    public function __destruct(){}
}
?>
