<?php
namespace TouchIt\Thread;

use TouchIt\TouchIt;
use TouchIt\Exchange\SignData;
use TouchIt\Thread\Worker;

class AutoUpdater extends Worker implements Countable{
    public $signs = [];
    
    public function count(){
        return count($this->signs);
    }
    
    public function addSign(SignData $sign){
        $this->signs[$sign->getId()] = $sign;
    }
    
    public function hasSign($id){
        return isset($this->signs[((string) $id)]);
    }
    
    public function removeSign($id){
        
    }
    
    public function onRun(){
        if(count($this) > 0){
            
        }
    }
}
?>
