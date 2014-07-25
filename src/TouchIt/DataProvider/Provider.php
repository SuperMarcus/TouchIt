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
     * Use to get signs by level
     * Only works for teleport sign
     * 
     * @param string $level
     * 
     * @return []
     */
    public function getByTarget(string $level);
    
    /**
     * Use to get sign by type
     * 
     * @param int $type
     * 
     * @return []
     */
    public function getByType(int $type);
    
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
     * This method will return the type of this sign
     * 
     * @param Sign $sign
     * 
     * @return int
     */
    public function create(Sign $sign);
    
    /** preload method */
    public function onEnable();
    public function onDisable();
}
?>
