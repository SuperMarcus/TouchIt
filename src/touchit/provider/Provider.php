<?php
namespace touchit\provider;

use touchit\TouchIt;

interface Provider{
    /**
     * Initialize Provider
     * @param TouchIt $plugin
     */
    public function __construct(TouchIt $plugin);

    /**
     * Call when plugin disable or manually save
     */
    public function save();

    /**
     * @param $x
     * @param $y
     * @param $z
     * @param $level
     * @return array
     */
    public function get($x, $y, $z, $level);

    /**
     * @param array $data
     * @param $x
     * @param $y
     * @param $z
     * @param $level
     */
    public function create(array $data, $x, $y, $z, $level);

    /**
     * @param $x
     * @param $y
     * @param $z
     * @param $level
     */
    public function remove($x, $y, $z, $level);

    /**
     * @param $x
     * @param $y
     * @param $z
     * @param $level
     * @return bool
     */
    public function exists($x, $y, $z, $level);

    /**
     * @return array
     */
    public function getAll();
}