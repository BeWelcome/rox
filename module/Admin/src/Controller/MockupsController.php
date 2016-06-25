<?php

namespace Rox\Admin\Controller;

use Rox\Core\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;

class MockupsController extends AbstractController
{
    public function showMockup($mockup)
    {
        $content = $this->render('@admin/mockups/'.$mockup.'.html.twig');

        return new Response($content);
    }
}
