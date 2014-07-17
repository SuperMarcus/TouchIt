<?php
namespace TouchIt\Pool;

use TouchIt\Pool\Worker;

class Pool implements Countable, Traversable{
    private $workers;
    
    public function count(){
        return count($this->workers);
    }
}
?>
