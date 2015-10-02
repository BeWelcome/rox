<?php

namespace Rox\Framework;

class RoxTwigExtension extends \Twig_Extension
{
    /**
     * Returns a list of filters.
     *
     * @return array
     */
    public function getFunctions()
    {
        return array(
            new \Twig_SimpleFunction('callback', array($this, 'callback')),
        );
    }

    public function callback($controller, $function) {
        $roxPostHandler = new \RoxPostHandler();
        $callback = $roxPostHandler->registerCallbackMethod($controller, $function, array());
        return $callback;
    }

    public function avatar($username) {
        return \MOD_layoutbits::PIC_50_50($username);
    }

    /**
     * Name of this extension.
     *
     * @return string
     */
    public function getName()
    {
        return 'LayoutKit';
    }
}