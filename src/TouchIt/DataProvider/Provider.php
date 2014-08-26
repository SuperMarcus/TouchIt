<?php
namespace TouchIt\DataProvider;

use TouchIt\TouchIt;
use pocketmine\level\Position;
use pocketmine\tile\Sign;

abstract class Provider implements \Thread{
    /**
     * Initialize method
     * @param TouchIt $plugin
     */
    abstract public function __construct(TouchIt $plugin);

    /**
     * Add a new sign
     * @param $type
     * @param $data
     * @param $x
     * @param $y
     * @param $z
     * @param $level
     */
    abstract public function create($type, $data, $x, $y, $z, $level);

    /**
     * Check the sign exists or not
     * @param $x
     * @param $y
     * @param $z
     * @param $level
     * @return bool
     */
    abstract public function exists($x, $y, $z, $level);

    /**
     * Remove a sign
     * @param $x
     * @param $y
     * @param $z
     * @param $level
     */
    abstract public function remove($x, $y, $z, $level);

    /**
     * Get all the sign
     * @return array
     */
    abstract public function getAll();

    /**
     * Get sign from provider
     * @param $x
     * @param $y
     * @param $z
     * @param $level
     * @return array
     */
    abstract public function get($x, $y, $z, $level);

    /**
     * Internal method
     */
    abstract public function onEnable();

    /**
     * Internal method
     */
    abstract public function onDisable();
}
?>
