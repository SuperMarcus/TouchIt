<?php
namespace TouchIt/Event;

use pocketmine\event\Event;
use TouchIt\Exchange\SignData;

class UpdateSignEvent extends PluginEvent implements Cancellable{
    private $textData, $sign;
    
    public function __construct(TouchIt $plugin, SignData $sign, $textData){
        $this->plugin = $plugin;
        $this->textData = $textData;
        $this->sign = $sign;
    }
    
    public function getSign(){
        return $this->sign;
    }
    
    public function getText(){
        return $this->textData;
    }
    
    public function setText($line1 = false, $line2 = false, $line3 = false, $line4 = false){
        $oldData = $this->textData;
        unset($this->textData);
        $this->textData = [
            (is_string($line1) or is_int($line1)) ? $oldData[0] : (string) $line1,
            (is_string($line2) or is_int($line2)) ? $oldData[1] : (string) $line2,
            (is_string($line3) or is_int($line3)) ? $oldData[2] : (string) $line3,
            (is_string($line4) or is_int($line4)) ? $oldData[3] : (string) $line4,
        ];
    }
}
?>
