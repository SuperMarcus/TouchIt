<?php
namespace TouchIt\DataProvider;

use TouchIt\TouchIt;
use TouchIt\DataProvider\Provider;

class CNFDataProvider implements Provider{
    private $touchit, $lock, $path;
    public $data, $file;
    
    public function __construct(TouchIt $touchit, $path){
        $this->lock = true;
        $this->touchit = $touchit;
        $this->path = $path;
        @mkdir(dirname($path));
    }
    
    public function lock(){
    	$this->lock = true;
    }
    
    public function unlock(){
    	$this->lock = false;
    }
    
    public function isLocked(){
    	return (bool) $this->lock;
    }
    
    public function onEnable(){
    	$this->unlock();
    	if(!file_exists($this->path)){
            $this->createCnf();
            return;
        }
        $this->parseCnf($this->path);
    }
    
    public function onDisable(){
    	$this->save();
    	$this->lock();
    	unset($this->file);
    	unset($this->data);
    }
    
    public function save(){
    	if($this->isLocked())return;
        $content = "#TouchIt Config file\r\n";
		foreach($this->data as $k => $v){
			if(is_bool($v) === true){
				$v = $v === true ? "on":"off";
			}elseif(is_array($v)){
				$v = implode(";", $v); 
			}
			$content .= $k."=".$v."\r\n";
		}
		@file_put_contents($this->path);
    }
    
    public function exists($key, $lower = false){
    	if($this->isLocked())return false;
    	return $lower ? isset($this->data[strtolower($key)]) : isset($this->data[$key]);
    }
    
    public function set($k, $v){
    	if($this->isLocked())return;
    	$this->data[$k] = $v;
    }
    
    public function get($k, $d = false){
    	if($this->isLocked())return $d;
    	if($this->exists($k)){
    		return $this->data[$k];
    	}
    	return $d;
    }
    
    public function setIfNotExists($k, $v){
    	if($this->isLocked())return;
    	if(!$this->exists($k)){
    		$this->data[$k] = $v;
    	}
    }
    
    public function remove($k){
    	if($this->isLocked())return;
    	if($this->exists($k)){
    		unset($this->data[$k]);
    	}
    }
    
    private function createCnf(){
    	if($this->isLocked())return;
    	$this->data = [
    		"name" => "Teleport",
            "maxPeople" => 20,
            "showCount" => true,
            "showFull" =>true,
			"allowPlayerBuild" => false,
            "allowPlayerBreak" => false,
			"opCheckByLowerName" => true,
            "autoDeleteSign" => true,
			"checkLevel" => true,
			"ticks" => 10,
			"enable" => true
    	];
    	$this->save();
    }
    
    private function parseCnf($path){
    	if($this->isLocked())return;
        $this->file = @file_get_contents($this->path);
        if(preg_match_all('/([a-zA-Z0-9\-_\.]*)=([^\r\n]*)/u', $content, $matches) > 0){ //false or 0 matches
			foreach($matches[1] as $i => $k){
				$v = trim($matches[2][$i]);
				switch(strtolower($v)){
					case "on":
					case "true":
					case "yes":
						$v = true;
						break;
					case "off":
					case "false":
					case "no":
						$v = false;
						break;
				}
				$this->data[$k] = $v;
			}
			if($this->data['showCount']){
				$this->remove("informationLine1");
				$this->remove("informationLine2");
			}else{
				$this->setIfNotExists("informationLine1", "Tap sign");
				$this->setIfNotExists("informationLine2", "to teleport");
			}
			
			$this->data['maxPeople'] = (int) $this->data['maxPeople'];
        }else{
            $this->lock = true;
            return;
        }
    }
    
    public function __toString(){
    	return print_r($this->data);
    }
    
    public function __invoke($k, $d = false){
    	return $this->get($k, $d);
    }
}
?>
