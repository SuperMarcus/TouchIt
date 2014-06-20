<?php
namespace TouchIt\DataProvider;

use TouchIt\TouchIt;
use pocketmine\tile\Sign;
use pocketmine\level\Position;

interface Provider{
    public function __construct(TouchIt $touchit);     //construct
    public function getSign(Position $pos);            //To get sign from file
    public function isSign(Position $pos);             //Check sign is teleport sign
    public function removeSign(Position $pos);         //Remove the sign
    public function addSign(Sign $sign);               //Add sign
    public function close();                           //Close database or save config file
}
?>
