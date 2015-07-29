<?php
namespace touchit\provider;

use pocketmine\utils\Config;
use touchit\TouchIt;

class YAML implements Provider{
    /** @var TouchIt */
    private $plugin;

    public function __construct(TouchIt $plugin){
        $this->plugin = $plugin;
        @mkdir($plugin->getDataFolder()."data/");
    }

    public function exists($x, $y, $z, $level){
        return (bool) @file_exists($this->plugin->getDataFolder()."data/".$level."-".$x."-".$y."-".$z.".yml");
    }

    public function get($x, $y, $z, $level){
        if($this->exists($x, $y, $z, $level)){
            return @yaml_parse(Config::fixYAMLIndexes(@file_get_contents($this->plugin->getDataFolder()."data/".$level."-".$x."-".$y."-".$z.".yml")))['data'];
        }
        return [];
    }

    public function remove($x, $y, $z, $level){
        @unlink($this->plugin->getDataFolder()."data/".$level."-".$x."-".$y."-".$z.".yml");
    }

    public function create(array $data, $x, $y, $z, $level){
        @mkdir($this->plugin->getDataFolder()."data/".$level."/");
        @file_put_contents($this->plugin->getDataFolder()."data/".$level."-".$x."-".$y."-".$z.".yml", @yaml_emit([
            "position" => [
                "x" => $x,
                "y" => $y,
                "z" => $z,
                "level" => $level
            ],
            "data" => $data
        ], YAML_UTF8_ENCODING));
    }

    public function getAll(){
        $resule = [];
        foreach(scandir($this->plugin->getDataFolder()."data/") as $file){
            if(substr($file, -3) === "yml"){
                $data = @yaml_parse(Config::fixYAMLIndexes(@file_get_contents($this->plugin->getDataFolder()."data/".$file)));
                if(is_array($data) and isset($data['position']) and isset($data['data'])){
                    $resule[] = $data;
                }
            }
        }
        return $resule;
    }

    public function save(){}
}