<?php
namespace TouchIt\DataProvider

use TouchIt\TouchIt

interface DataProvider{
    public function __construct(TouchIt $touchit, $path);
    public function save();
    public function __destruct();
}
?>
