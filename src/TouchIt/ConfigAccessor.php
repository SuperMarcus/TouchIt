<?php
namespace TouchIt;

use TouchIt\TouchIt;

class ConfigAccessor implements \arrayaccess{
    private $data;
    
    public function __construct(){}
    
    public function exists($offset){
        return isset($this->data[$offset]);
    }
    
    public function getLang(){
        $fp = TouchIt::getTouchIt()->getResource("language/".strtolower($this->get("Language", "english")).".yml");
        $contents = [];
        if(!$fp){
            @fclose($fp);
            $fp = TouchIt::getTouchIt()->getResource("language/english.yml");
        }
        while(!feof($fp)){
            $line = fgets($fp);
            if($line{0} === "#" or trim($line) === "")continue;
            $pos = strpos("=", $line);
            if($pos !== false){
                $contents[trim(substr($line, 0, $pos - 1))] = trim(substr($line, $pos + 1));
            }
        }
        @fclose($fp);
        return $contents;
    }
    
    public function get($offset, $default = null){
        if($this->exists($offset))return $this->data[$offset];
        else return $default;
    }
    
    public function analyzeFile(){
    	if(!file_exists(TouchIt::getTouchIt()->getDataFolder()."config.yml")){
    		file_put_contents(TouchIt::getTouchIt()->getDataFolder()."config.yml", stream_get_contents(TouchIt::getTouchIt()->getResource("preconfig.yml")));
    	}
    	$this->data = @yaml_parse(file_get_contents(TouchIt::getTouchIt()->getDataFolder()."config.yml"));
    }
    
    /** Magic methods */
    public function __set($name, $value){
        $this->set($name, $value);
    }
    
    public function __isset($name){
        return $this->exists($name);
    }
    
    /** Method of ArrayAccess */
    public function offsetGet($offset){
        return $this->get($offset);
    }
    
    public function offsetExists($offset){
        return $this->exists($offset);
    }
    
    public function offsetUnset($offset){}//Main config only can change by user
    public function offsetSet($offset, $value){}//Main config only can change by user
}
?>
