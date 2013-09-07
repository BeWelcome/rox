<?php


/**
 * To be as flexible as possible,
 * our autoload uses a dynamically set callback
 * to determine wher a class is defined.
 */
class AutoloadPlug
{
    static private $_callback;

    /**
     * Set the callback to be called when a new classname is requested.
     * The callback is assumed to do some "require_once" or similar,
     * but you are free to let it do something else.
     *
     * @param callback $callback
     */
    static function setCallback($callback)
    {
        self::$_callback = $callback;
    }

    /**
     * This static method is called by the __autoload function.
     *
     * @param string $classname
     */
    static function autoload($classname)
    {
        call_user_func(self::$_callback, $classname);
    }
}


/**
 * This PHP magic function gets called
 * whenever a script uses a yet undefined classname,
 * which can be for subclassing, for constructing ("new" keyword),
 * or for calling static methods or attributes.
 *
 * @param string $classname
 */
function __autoload($classname)
{
    // Swift autoloader and rox autoloader collide; rebuild functionality here
    if (strpos($classname, 'Swift_') !== false) {
        // require the path to the swift source file

        $path = SCRIPT_BASE .'lib/misc/swift-5.0.1/lib/classes/'. str_replace('_', '/', $classname).'.php';

        if (!file_exists($path)) {
            return;
        }

        require $path;
    } else {
        AutoloadPlug::autoload($classname);
    }
}


?>