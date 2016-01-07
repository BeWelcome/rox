<?php

namespace Rox\Admin\Queries;

use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormFactory;
use Symfony\Component\Routing\Router;

class AdminQueriesOverviewPage extends \Rox\Framework\TwigView
{
    /**
     * AdminLogsPage constructor.
     * @param Router $router
     * @param array|bool $parameters
     */
    public function __construct(Router $router) {
        parent::__construct($router);
        $this->setTemplate('queries/queries.html.twig', 'admin');
        $this->addLateJavascriptFile('admin/queries.js');
    }
}
