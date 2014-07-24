<?php
namespace TouchIt;

use TouchIt\TouchIt;

class ConfigAccessor implements arrayaccess{
    private $data;
    
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
    
    public function exists($offset){
        return isset($this->data[$offset]);
    }
    
    public function get($offset, $default = null){
        if($this->exists($offset))return $this->data[$offset];
        else return $default;
    }
    
    public function set($offset, $value){
        $this->data[$offset] = $value;
    }
    
    public function remove($offset){
        unset($this->data[$offset]);
    }
    
    private function analyzeFile(){
    	if(!file_exists(TouchIt::getTouchIt()->getDataFolder()."config.yml")){
    		file_put_contents(TouchIt::getTouchIt()->getDataFolder()."config.yml", stream_get_contents(TouchIt::getTouchIt()->getResource("preconfig.yml")));
    	}
    	$this->data = @yaml_parse(file_get_contents(TouchIt::getTouchIt()->getDataFolder()."config.yml"));
    }
    
    /** Magic methods */
    public function __get($name){
        return $this->get($name);
    }
    
    public function __set($name, $value){
        $this->set($name, $value);
    }
    
    public function __isset($name){
        return $this->exists($name);
    }
    
    public function __unset($name){
        return $this->remove($name);
    }
    
    /** Method of ArrayAccess */
    public function offsetGet($offset){
        return $this->get($offset);
    }
    
    public function offsetUnset($offset){
        $this->remove($offset);
    }
    
    public function offsetSet($offset, $value){
        $this->set($offset, $value);
    }
    
    public function offsetExists($offset){
        return $this->exists($offset);
    }
}
?>
