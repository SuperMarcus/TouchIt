<?php
namespace TouchIt\DataProvider

use TouchIt\TouchIt

interface Provider{
    public function isLocked();
    public function lock();
    public function unlock();
    public function onEnable();
    public function onDisable();
}
?>
