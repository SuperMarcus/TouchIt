<?php
namespace TouchIt\Exchange;

use TouchIt\Exchange\ExchangeInformation;
use TouchIt\Exchange\SignData;

class SignContentsData implements ExchangeInformation{
    public $data;
    public $database;
    
    public function __construct(\SQLite3 &$database){
        $this->data = [];
        &$this->database = $database;
        $query = $database->query("SELECT * FROM sign;");
        $this->data['id'] = [];
        while($id = $query->fetchArray(SQLITE3_ASSOC)){
            $this->data['query'][] = $query;
        }
        $this->data['now'] = 0;
    }
    
    public function getNext(){
        if(!isset($this->data['query'][(++$this->data['now'])]))return false;
        $now = $this->data['query'][$this->data['now']];
        return new SignData($this->database->query("SELECT * FROM sign WHERE id = ".$this->data['query'][$now['id']].";"), $this->database);
    }
    
    public function delete($ID){
        foreach($this->data['query'] as $key => $query){
            if($query['id'] = $ID){
                unset($this->data['query'][$key]);
                if($key <= $this->data['now']){
                    --$this->data['now'];
                }
                return;
            }
        }
    }
}
?>
