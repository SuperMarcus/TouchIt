<?php
namespace TouchIt\Provider;

use pocketmine\utils\Config;
use TouchIt\TouchIt;

class YAML implements Provider{
    /** @var TouchIt */
    private $plugin;

    public function __construct(TouchIt $plugin){
        $this->plugin = $plugin;
        @mkdir($plugin->getDataFolder()."data/");
    }

    public function exists($x, $y, $z, $level){
        return (bool) @file_exists($this->plugin->getDataFolder()."data/".$level."/".$x."-".$y."-".$z.".yml");
    }

    public function get($x, $y, $z, $level){
        if($this->exists($x, $y, $z, $level)){
            return @yaml_parse(Config::fixYAMLIndexes(@file_get_contents($this->plugin->getDataFolder()."data/".$level."/".$x."-".$y."-".$z.".yml")))['data'];
        }
        return [];
    }

    public function remove($x, $y, $z, $level){
        @unlink($this->plugin->getDataFolder()."data/".$level."/".$x."-".$y."-".$z.".yml");
    }

    public function create(array $data, $x, $y, $z, $level){
        @mkdir($this->plugin->getDataFolder()."data/".$level."/");
        @file_put_contents($this->plugin->getDataFolder()."data/".$level."/".$x."-".$y."-".$z.".yml", @yaml_emit([
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
        $iterator = new \DirectoryIterator($this->plugin->getDataFolder()."data/");
        $resule = [];
        foreach($iterator as $dir){
            /** @var \DirectoryIterator $dir */
            if($dir->isDir()){
                foreach($dir as $file){
                    /** @var \DirectoryIterator $file */
                    if($file->isFile() and $file->getExtension() === "yml"){
                        $resule[] = @yaml_parse(Config::fixYAMLIndexes(@file_get_contents($file->getPathname())));
                    }
                }
            }
        }
        return $resule;
    }

    public function save(){}
}