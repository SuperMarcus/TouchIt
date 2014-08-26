<?php
namespace TouchIt;

class UnitLoader{
    const UNIT_CHECK = 0;
    const UNIT_PROCESS = 1;
    const UNIT_TOUCH = 2;

    /** @var TouchIt */
    private $plugin;

    /** @var callable[] */
    private $units;

    public $args = array(
        0 => '$text, $tile, $thread_manager',
        1 => '$sign, $tile, $thread_manager',
        2 => '$tile, $manager'
    );

    public function __construct(TouchIt $plugin){
        $this->plugin = $plugin;
        $this->units = [];
    }

    /**
     * @param $type
     * @return callable
     * @throws \ErrorException
     */
    public function getUnits($type){
        if(defined("TouchIt\\UnitLoader::".strtoupper($type)) and isset($this->units[constant("TouchIt\\UnitLoader::".strtoupper($type))])){
            return $this->units[constant("TouchIt\\UnitLoader::".strtoupper($type))];
        }else{
            throw new \ErrorException("Unable to find units type ".$type);
        }
    }

    /**
     * This method will load TouchIt callable units
     * @throws \ErrorException
     */
    public function parseUnit(){
        $fp = $this->plugin->getResource("callbacks/unit.json");
        if(!$fp){
            throw new \ErrorException("Unable to open TouchIt unit file");
        }
        $info = @json_decode(stream_get_contents($fp), true);
        @fclose($fp);
        unset($fp);
        foreach($info as $unit){
            $this->loadCallback($unit);
        }
    }

    /**
     * Internal use
     * @param $name
     * @throws \ErrorException
     */
    private function loadCallBack($name){
        $fp = $this->plugin->getResource("callbacks/".$name);
        if(!$fp){
            throw new \ErrorException("Unable to open TouchIt callback file callbacks/".$name);
        }
        $info = @json_decode(stream_get_contents($fp), true);
        @fclose($fp);
        unset($fp);
        foreach($info['file'] as $file => $unit){
            $stream = $this->plugin->getResource("callbacks/".$info['name']."/".$file);
            if(!$stream or (0 > $unit or (count($this->args) - 1) < $unit))continue;
            $contents = stream_get_contents($stream);
            $this->units[(int) $unit][$info['type']] = create_function($this->args[$unit], $contents);
        }
    }
}