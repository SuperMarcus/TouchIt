<?php
namespace TouchIt;

use TouchIt\TouchIt;

class ConfigAccessor implements arrayaccess{
    private $data;
    
    public function __construct(){
    	$this->analyzeFile();
    }
    
    public function exists($offset){
        return isset($this->data[$offset]);
    }
    
    public function get($offset, $default = null){
        if($this->exists($offset))return $this->data[$offset];
        else return $default;
    }
    
    private function analyzeFile(){
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
