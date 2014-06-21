<?php
namespace TouchIt\DataProvider;

use TouchIt\TouchIt;
use TouchIt\DataProvider\DataProvider;

class CNFDataProvider implements DataProvider{
    private $touchit, $lock, $path;
    public $data, $file;
    
    public function __construct(TouchIt $touchit, $path){
        $this->lock = false;
        $this->touchit = $touchit;
        $this->path = $path;
        if(!file_exists($path)){
            $this->lock = true;
            return;
        }
        $this->parseCnf($path);
    }
    
    public function save(){
        $content = "//TouchIt Config file\r\n";
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
    
    private function parseCnf($path){
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
			$this->data['maxPeople'] = (int) $this->data['maxPeople'];
        }else{
            $this->lock = true;
            return;
        }
    }
}
?>
