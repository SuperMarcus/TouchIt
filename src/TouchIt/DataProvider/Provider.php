<?php
namespace TouchIt\DataProvider

use TouchIt\TouchIt
use pocketmine\level\Position;
use pocketmine\tile\Sign;

interface Provider{
    /**
     * Use to get sign from database or filesystem
     * 
     * @param Position $pos
     * 
     * @return null|array
     */
    public function get(Position $pos);
    
    /**
     * Use to get all the sign from database or filesystem
     * 
     * @return []
     */
    public function getAll();
    
    /**
     * Use to remove sign from database or filesystem
     * 
     * @param Position $pos
     */
    public function remove(Position $pos);
    
    /**
     * Use to check sign
     * 
     * @param Position $pos
     * 
     * @return bool
     */
    public function exists(Position $pos);
    
    /**
     * Use to add sign to database or filesystem
     * 
     * @param Sign $sign
     */
    public function create(Sign $sign);
    
    /** Load method */
    public function onEnable();
    public function onDisable();
}
?>
