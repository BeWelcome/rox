<?php

namespace Rox\Framework;

class RoxTwigExtension extends \Twig_Extension
{
    /** @var null|\MOD_layoutbits  */
    private $_layoutBits = null;

    public function __construct()
    {
        $this->_layoutBits = \MOD_layoutbits::get();
    }

    /**
     * Returns a list of filters.
     *
     * @return array
     */
    public function getFunctions()
    {
        return array(
            new \Twig_SimpleFunction('callback', array($this, 'callback')),
            new \Twig_SimpleFunction('ago', array($this, 'ago')),
        );
    }

    public function callback($controller, $function) {
        $roxPostHandler = new \RoxPostHandler();
        $callback = $roxPostHandler->registerCallbackMethod($controller, $function, array());
        return $callback;
    }

    public function avatar($username) {
        return $this->_layoutBits->PIC_50_50($username);
    }

    public function ago($timestamp) {
        return $this->_layoutBits->ago(strtotime($timestamp));
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