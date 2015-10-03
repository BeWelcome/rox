<?php


/**
 * This class is necessary to cheat on PlatformPT
 * It allows us to have our own autoload mechanism, instead of using the PT one.
 */
class Classes2
{
    private static $_sim_classes;
    
    static function set($sim_classes) {
        self::$_sim_classes = $sim_classes;
    }
    
    static function get() {
        return self::$_sim_classes;
    }
}


?>