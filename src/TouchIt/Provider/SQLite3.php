<?php
namespace TouchIt\Provider;

use TouchIt\TouchIt;
use SQLite3 as SQL;
use SQLite3Result as SQLResult;

class SQLite3 implements Provider{
    /** @var TouchIt */
    private $plugin;

    /** @var \SQLite3|null */
    private $database = null;

    /**
     * Initialize
     * @param TouchIt $plugin
     */
    public function __construct(TouchIt $plugin){
        $this->plugin = $plugin;
        $this->load();
    }

    /**
     * Call when disable;
     */
    public function save(){
        $this->database->close();
        $this->database = null;
    }

    /**
     * @param $x
     * @param $y
     * @param $z
     * @param $level
     * @return array
     */
    public function get($x, $y, $z, $level){
        $id = $this->pos2string($x, $y, $z, $level);
        $data = [];
        if($this->database instanceof SQL){
            $query = @json_decode($this->decode($this->database->querySingle("SELECT data FROM sign WHERE position = '".$id."';")), true);
            if(is_array($query)){
                $data = $query;
            }
            /*$query = $this->database->query("SELECT * FROM sign WHERE position = '".$id."';");
            if($query instanceof SQLResult){
                $query = $query->fetchArray(SQLITE3_ASSOC);
                if(is_array($query) and $query['position'] === $id){
                    $data = @json_decode($this->decode($query['data']), true);
                }
            }*/
        }
        return $data;
    }

    /**
     * @param array $data
     * @param $x
     * @param $y
     * @param $z
     * @param $level
     */
    public function create(array $data, $x, $y, $z, $level){
        $id = $this->pos2string($x, $y, $z, $level);
        $data = $this->encode(json_encode($data));
        if($this->database instanceof SQL){
            $this->database->exec("INSERT INTO sign VALUES ('".$id."', '".$data."');");
        }
    }

    /**
     * @param $x
     * @param $y
     * @param $z
     * @param $level
     */
    public function remove($x, $y, $z, $level){
        $id = $this->pos2string($x, $y, $z, $level);
        if($this->database instanceof SQL){
            $this->database->exec("DELETE FROM sign WHERE position = '".$id."';");
        }
    }

    /**
     * @param $x
     * @param $y
     * @param $z
     * @param $level
     * @return bool
     */
    public function exists($x, $y, $z, $level){
        $id = $this->pos2string($x, $y, $z, $level);
        if($this->database instanceof SQL and $this->database->querySingle("SELECT position FROM sign WHERE position = '".$id."';") === $id){
            return true;
        }
        return false;
    }

    /**
     * @return array
     */
    public function getAll(){
        $resule = [];
        if($this->database instanceof SQL){
            $query = $this->database->query("SELECT data FROM sign;");
            if($query instanceof SQLResult){
                while($data = $query->fetchArray(SQLITE3_ASSOC)){
                    $info = @json_decode($this->decode($data['data']), true);
                    if(is_array($data)){
                        $resule[] = ["data" => $info, "position" => $this->string2pos($data['position'])];
                    }
                }
            }
        }
        return $resule;
    }

    /**
     * Internal use
     */
    private function load(){
        if(file_exists($this->plugin->getDataFolder()."data.db")){
            $this->database = new SQL($this->plugin->getDataFolder()."data.db", SQLITE3_OPEN_READWRITE);
        }else{
            $this->database = new SQL($this->plugin->getDataFolder()."data.db", SQLITE3_OPEN_CREATE|SQLITE3_OPEN_READWRITE);
            $this->database->exec(stream_get_contents($this->plugin->getResource("provider/sqlite3.sql")));
        }
    }

    /**
     * Internal use
     * @param $x
     * @param $y
     * @param $z
     * @param $level
     * @return string
     */
    private function pos2string($x, $y, $z, $level){
        return $x."-".$y."-".$z."-".$level;
    }

    private function string2pos($data){
        $array = explode("-", $data);
        return ["x" => intval($array[0]), "y" => intval($array[1]), "z" => intval($array[2]), "level" => $data[3]];
    }

    /**
     * @param $data
     * @return string
     */
    private function encode($data){
        return str_replace([
            "'",
            "(",
            ")",
            ";",
            "\"",
            ":",
            "\\"
        ], [
            "%1%",
            "%2%",
            "%3%",
            "%4%",
            "%5%",
            "%6%",
            "%7%"
        ], $data);
    }

    /**
     * @param $data
     * @return string
     */
    private function decode($data){
        return str_replace([
            "%1%",
            "%2%",
            "%3%",
            "%4%",
            "%5%",
            "%6%",
            "%7%"
        ], [
            "'",
            "(",
            ")",
            ";",
            "\"",
            ":",
            "\\"
        ], $data);
    }
}