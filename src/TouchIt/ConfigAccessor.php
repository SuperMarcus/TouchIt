<?php
namespace TouchIt;

use TouchIt\TouchIt;

class ConfigAccessor implements \arrayaccess{
    private $data;

    private $path;

    /** @var TouchIt */
    private $plugin;
    
    public function __construct($path, TouchIt $plugin){
        $this->plugin = $plugin;
        $this->path = $path;
    }
    
    public function exists($offset){
        return isset($this->data[$offset]);
    }

    public function getProcessUnit(){
        $callbacks = [];
        foreach($this->plugin->getTypes() as $id => $type){
            $stream = $this->plugin->getResource("callbacks/process_".strtolower($type).".callable");
            if(!$stream){
                @fclose($stream);
                throw new \ErrorException("Unable to find TouchIt process unit: 'callbacks/process_".strtolower($type).".callable' id: ".$id." Make sure you got the full version of TouchIt.");
            }
            $callbacks[$id] = @create_function('$sign, $tile, $thread_manager', @stream_get_contents($stream));
            @fclose($stream);
        }
    }
    
    public function getLang(){
        $fp = $this->plugin->getResource("language/".strtolower($this->get("Language", "english")).".lang");
        $contents = [];
        if(!$fp){
            @fclose($fp);
            $fp = $this->plugin->getResource("language/english.lang");
        }
        while(!feof($fp)){
            $line = fgets($fp);
            if($line{0} === "#" or trim($line) === "")continue;
            $pos = strpos($line, "=");
            if($pos !== false){
                $contents[trim(substr($line, 0, $pos))] = trim(substr($line, $pos + 1));
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
    	if(!file_exists($this->path)){
    		file_put_contents($this->path, stream_get_contents($this->plugin->getResource("preconfig.yml")));
    	}
    	$this->data = @yaml_parse(file_get_contents($this->path));
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
