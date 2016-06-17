<?php

namespace Rox\Start\Controller;

use Rox\Core\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class AboutController extends AbstractController
{
    public function indexAction(Request $request)
    {
        $page = $request->attributes->get('page');

        $pageTemplate = '@start/about/' . $page . '.html.twig';

        if (!$this->getEngine()->exists($pageTemplate)) {
            throw new NotFoundHttpException();
        }

        return new Response($this->render('@start/about.html.twig', [
            'pageTemplate' => $pageTemplate,
            'page' => $page,
        ]));
    }
}
