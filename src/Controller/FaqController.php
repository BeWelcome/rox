<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class FaqController extends AbstractController
{
    /**
     * @Route("/faq/", name="faq_redirect",
     *     defaults = {"path":""})
     * @Route("/faq/{path}", name="faq_all_redirect",
     *     requirements = {"path":".+"})
     *
     * @param Request $request
     * @param string $path
     * @return RedirectResponse
     */
    public function faqRedirect(Request $request, string $path)
    {
        // Path isn't used.
        $path = null;
        $pathInfo = str_replace('/faq/', '/about/faq/', $request->getPathInfo());
        return new RedirectResponse($pathInfo);
    }

}
