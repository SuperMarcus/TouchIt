<?php
namespace TouchIt;

use TouchIt\TouchIt;

class ConfigAccessor{
    public $data;
    
    private static $valtype = [
    	"maxPeople" => "intval",
    	"createTimeout" => "intval",
    	"showCount" => "boolval",
    	"showFull" => "boolval",
    	"allowPlayerBuild" => "boolval",
    	"allowPlayerBreak" => "boolval",
    	"autoDeleteSign" => "boolval",
    	"checkLevel" => "boolval"
    ];
    
    public function __construct(){
    	$this->analyzeFile();
    }
    
    private function analyzeFile(){
    	if(!file_exists(TouchIt::getTouchIt()->getDataFolder()."touchit.config")){
    		file_put_contents(TouchIt::getTouchIt()->getDataFolder()."touchit.config", stream_get_contents(TouchIt::getTouchIt()->getResource("touchit.config")));
    	}
    	$fp = fopen(TouchIt::getTouchIt()->getDataFolder()."touchit.config", "r");
    	while(!feof($fp)){
    		$line = fgets($fp);
    		if($line{0} == "#" or trim($line) == "")continue;
    		$data = explode("=", $line);
    		if(isset(self::$valtype[$data[0]])){
    			if(self::$valtype[$data[0]] === "boolval"){
    				switch($data[1]){
    					case "on":
    					case "true":
    						$data[1] = true;
    					case "off":
    					case "false":
    						$data[1] = false;
    					default:
    						$data[1] = boolval($data[1]);
    				}
    			}else{
    				$data[1] = call_user_func(self::$valtype[$data[0]], $data[1]);
    			}
    		}
    		$this->data[$data[0]] = $data[1];
    	}
    	fclose($fp);
    }
}
?>
