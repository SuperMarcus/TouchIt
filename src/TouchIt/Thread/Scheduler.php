<?php
namespace TouchIt\thread;

class scheduler extends \Thread{
    public static $scheduler;
    
    public static function getScheduler(){
        return self::$scheduler;
    }
}
?>
