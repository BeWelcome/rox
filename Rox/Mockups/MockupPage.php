<?php

namespace Rox\Mockups;

use Symfony\Component\Routing\Router;

class MockupPage extends \Rox\Framework\TwigView
{
    /**
     * MockupPage constructor.
     *
     * @param Router $router
     * @param        $template
     *
     * Loads the given mockup template (and adds a call to a matching js file)
     */
    public function __construct(Router $router, $template) {
        parent::__construct($router);
        $this->setTemplate($template . '.html.twig', 'mockups');
        $this->addLateJavascriptFile('mockups/' . $template . '.js', true);
    }

}
