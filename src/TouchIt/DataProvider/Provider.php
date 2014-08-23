<?php
namespace TouchIt\DataProvider;

use TouchIt\TouchIt;
use pocketmine\level\Position;
use pocketmine\tile\Sign;

interface Provider{
    /**
     * Get sign from provider
     * @param $x
     * @param $y
     * @param $z
     * @param $level
     * @return array
     */
    public function get($x, $y, $z, $level);

    /**
     * Get all the sign
     * @return array
     */
    public function getAll();

    /**
     * Remove a sign
     * @param $x
     * @param $y
     * @param $z
     * @param $level
     */
    public function remove($x, $y, $z, $level);

    /**
     * Check the sign exists or not
     * @param $x
     * @param $y
     * @param $z
     * @param $level
     * @return bool
     */
    public function exists($x, $y, $z, $level);

    /**
     * Add a new sign
     * @param $type
     * @param $data
     * @param $x
     * @param $y
     * @param $z
     * @param $level
     */
    public function create($type, $data, $x, $y, $z, $level);

    /**
     * Internal method
     */
    public function onEnable();

    /**
     * Internal method
     */
    public function onDisable();
}
?>
