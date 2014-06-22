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
        $query = $database->query("SELECT id FROM sign;");
        $this->data['id'] = [];
        while($id = $query->fetchArray(SQLITE3_ASSOC)){
            $this->data['id'][] = $query;
        }
        $this->data['count'] = count($this->data['id']);
        $this->data['now'] = 0;
    }
    
    public function getNext(){
        if((++$this->data['now']) >= $this->data['count'])return false;
        return new SignData($this->database->query("SELECT * FROM sign WHERE id = ".$this->data['now'].";"), $this->database);
    }
}
?>
