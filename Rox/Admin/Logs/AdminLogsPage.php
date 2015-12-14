<?php

namespace Rox\Admin\Logs;

use Symfony\Component\Routing\Router;

class AdminLogsPage extends \Rox\Framework\TwigView
{
    /**
     * AdminLogsPage constructor.
     * @param Router $router
     * @param array|bool $parameters
     */
    public function __construct(Router $router) {
        parent::__construct($router);
        $this->setTemplate('logs/logs.html.twig', 'admin');
        $this->addLateJavascriptFile('admin/logs.js', true);
    }

}
