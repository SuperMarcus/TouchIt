<?php
namespace TouchIt\DataProvider

use TouchIt\TouchIt
use TouchIt\DataProvider\Provider

class SQLDataProvider implements Provider{
    private $database, $main, $lock;
    
    public function __construct(TouchIt $touchit){
        $this->lock = false;
        $this->main = $touchit;
        $this->loadDataBase();
    }
    
    public function loadDataBase(){
        if(!extension_loaded("sqlite3")){
            $this->lock = true;
            return;
        }
        if($this->main->isPhar()){
            @mkdir($this->main->getFile().DIRECTORY_SEPARATOR."data".DIRECTORY_SEPARATOR);
            $this->database = new \SQLite3($this->main->getFile().DIRECTORY_SEPARATOR."data".DIRECTORY_SEPARATOR, SQLITE3_OPEN_READWRITE|SQLITE3_OPEN_CREATE);
        }else{
            @mkdir($this->main->getDataFolder());
            $this->database = new \SQLite3($this->main->getDataFolder(), SQLITE3_OPEN_READWRITE|SQLITE3_OPEN_CREATE);
        }
    }
}
?>
