<?php
namespace TouchIt\Provider;

use TouchIt\TouchIt;

class SQLite3 implements Provider{
    private $plugin;

    public function __construct(TouchIt $plugin){
        $this->plugin = $plugin;
    }

    public function save(){

    }

    public function get($x, $y, $z, $level){

    }

    public function create(array $data, $x, $y, $z, $level){

    }

    public function remove($x, $y, $z, $level){

    }

    public function exists($x, $y, $z, $level){

    }

    public function getAll(){

    }
}