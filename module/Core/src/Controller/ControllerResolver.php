<?php

namespace Rox\Core\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\ControllerResolver as BaseControllerResolver;
use Symfony\Component\HttpFoundation\Request;

/**
 * Within this project, if a controller config only refers to a class name, then
 * it is intended to be an invokable class. This function adds __invoke to the
 * controller config so it doesn't need to be continually specified for each
 * controller.
 *
 * Without this, the default Symfony behaviour simply creates the class
 * (new $class()) without the ability to inject dependencies via the
 * constructor.
 */
class ControllerResolver extends BaseControllerResolver
{
    public function getController(Request $request)
    {
        if (!$controller = $request->attributes->get('_controller')) {
            return false;
        }

        if (is_string($controller) && false === strpos($controller, ':')) {
            $request->attributes->set('_controller', $controller . ':__invoke');
        }

        return parent::getController($request);
    }
}
