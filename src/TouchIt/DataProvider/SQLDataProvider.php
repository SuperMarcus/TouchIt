<?php
namespace TouchIt\DataProvider;

use TouchIt\TouchIt;
use TouchIt\DataProvider\Provider;

class SQLDataProvider implements Provider extends \Thread{
    /** @var \SQLite3 */
    private $database;

    /** @var TouchIt */
    private $plugin;

    private $enable;
    private $write;
    
    public function __construct(TouchIt $plugin){
        $this->plugin = $plugin;
        $this->write = [];
    }

    /**
     * Write an new log
     * @param $type
     * @param $data
     * @param $x
     * @param $y
     * @param $z
     * @param $level
     */
    public function create($type, $data, $x, $y, $z, $level){
        $this->write[] = "INSERT INTO sign VALUES ('".position2string($x, $y, $z, $level)."', ".$type.", '".json_decode($data)."')";
        $this->notify();
    }

    /**
     * Check the log exists or not
     * @param $x
     * @param $y
     * @param $z
     * @param $level
     * @return bool
     */
    public function exists($x, $y, $z, $level){
        $query = $this->database->query("SELECT position FROM sign WHERE position = '".position2string($x, $y, $z, $level)."'");
        if($query instanceof \SQLite3Result){
            while(($array = $query->fetchArray(SQLITE3_ASSOC))){
                if($array['position'] === position2string($x, $y, $z, $level)){
                    return true;
                }
            }
        }
        return false;
    }

    /**
     * Delete log from database
     * @param $x
     * @param $y
     * @param $z
     * @param $level
     */
    public function remove($x, $y, $z, $level){
        $this->write[] = "DELETE FROM sign WHERE position = '".position2string($x, $y, $z, $level)."'";
        $this->notify();
    }

    /**
     * return all the logs
     * @return array
     */
    public function getAll(){
        $resule = [];
        $query = $this->database->query("SELECT * FROM sign");
        if($query instanceof \SQLite3Result){
            while(($array = $query->fetchArray(SQLITE3_ASSOC))){
                $resule[] = ["position" => string2position($array['position']), "type" => $array['type'], "data" => $array['data']];
            }
        }
        return $resule;
    }

    /**
     * Get log from database
     * @param $x
     * @param $y
     * @param $z
     * @param $level
     * @return array|null
     */
    public function get($x, $y, $z, $level){
        $resule = null;
        $query = $this->database->query("SELECT * FROM sign WHERE position = '".position2string($x, $y, $z, $level)."'");
        if($query instanceof \SQLite3Result){
            $array = $query->fetchArray(SQLITE3_ASSOC);
            $resule = ["position" => string2position($array['position']), "type" => $array['type'], "data" => $array['data']];
        }
        return $resule;
    }

    /**
     * Call when need to load database
     */
    public function onEnable(){
    	$this->loadDataBase();
        $this->enable = true;
        $this->start(PTHREADS_INHERIT_ALL & ~PTHREADS_INHERIT_CLASSES);
    }

    /**
     * Call when need to close database
     */
    public function onDisable(){
        $this->enable = false;
        $this->join();
    }

    /**
     * Database writing thread
     * @throws \ErrorException
     */
    public function run(){
        while($this->enable){
            if(count($this->write) > 0){
                foreach($this->write as $action){
                    if(!$this->database->exec((string) $action)){
                        throw new \ErrorException("Unable to write TouchIt database. Make sure you've got enough permissions.");
                    }
                }
            }
            $this->wait();
        }
        $this->database->close();
        exit(0);
    }

    /**
     * Internal use
     */
    private function loadDataBase(){
    	if(file_exists(TouchIt::getTouchIt()->getDataFolder()."data.db")){
    		$this->database = new \SQLite3(TouchIt::getTouchIt()->getDataFolder()."data.db", SQLITE3_OPEN_READWRITE|SQLITE3_OPEN_CREATE);
    		$this->database->exec(stream_get_contents(TouchIt::getTouchIt()->getResource("database/sqlite3.sql")));
    	}else{
    		$this->database = new \SQLite3(TouchIt::getTouchIt()->getDataFolder()."data.db", SQLITE3_OPEN_READWRITE);
    	}
    }

    /**
     * Get database string by position
     * @param $x
     * @param $y
     * @param $z
     * @param $level
     * @return string
     */
    private function position2string($x, $y, $z, $level){
        return $x."-".$y."-".$z."-".$level;
    }

    /**
     * Get position by database string
     * @param $string
     * @return array
     */
    private function string2position($string){
        $array = explode("-", $string);
        return ["x" => intval($array[0]), "y" => intval($array[1]), "z" => intval($array[2]), "level" => $array[3]];
    }
}
?>
