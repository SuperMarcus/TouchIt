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

    /**
     * @param $offset
     * @return bool
     */
    public function exists($offset){
        return isset($this->data[$offset]);
    }

    /**
     * @return array
     * @throws \ErrorException
     */
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
        return $callbacks;
    }

    /**
     * @return array
     * @throws \ErrorException
     */
    public function getCheckUnit(){
        $callbacks = [];
        foreach($this->plugin->getTypes() as $type){
            $stream = $this->plugin->getResource("callbacks/check_".strtolower($type).".callable");
            if(!$stream){
                @fclose($stream);
                throw new \ErrorException("Unable to find TouchIt check unit: 'callbacks/check_".strtolower($type).".callable' id: ".$id." Make sure you got the full version of TouchIt.");
            }
            $callbacks[] = @create_function('$text, $tile, $thread_manager', @stream_get_contents($stream));
            @fclose($stream);
        }
        return $callbacks;
    }

    /**
     * @return array
     */
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

    /**
     * @param $offset
     * @param null $default
     * @return null
     */
    public function get($offset, $default = null){
        if($this->exists($offset))return $this->data[$offset];
        else return $default;
    }

    /**
     * analyze file
     */
    public function analyzeFile(){
    	if(!file_exists($this->path)){
    		file_put_contents($this->path, stream_get_contents($this->plugin->getResource("preconfig.yml")));
    	}
    	$this->data = @yaml_parse(file_get_contents($this->path));
    }
    
    /** Magic methods */

    /**
     * @param $name
     * @param $value
     */
    public function __set($name, $value){
        $this->set($name, $value);
    }

    /**
     * @param $name
     * @return bool
     */
    public function __isset($name){
        return $this->exists($name);
    }
    
    /** Method of ArrayAccess */

    /**
     * @param mixed $offset
     * @return mixed|null
     */
    public function offsetGet($offset){
        return $this->get($offset);
    }

    /**
     * @param mixed $offset
     * @return bool
     */
    public function offsetExists($offset){
        return $this->exists($offset);
    }

    /**
     * Warning: This method can do nothing! Do not use this!
     * @param mixed $offset
     */
    public function offsetUnset($offset){}//Main config only can change by user

    /**
     * Warning: This method can do nothing! Do not use this!
     * @param mixed $offset
     * @param mixed $value
     */
    public function offsetSet($offset, $value){}//Main config only can change by user
}
?>
