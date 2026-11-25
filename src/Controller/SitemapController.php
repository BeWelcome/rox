<?php

namespace App\Controller;

use App\Service\Sitemap;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class SitemapController extends AbstractController
{
    #[Route(path: '/sitemap.xml', name: 'sitemap', methods: ['GET'])]
    public function sitemap(Request $request, Sitemap $sitemap): Response
    {
        return new Response($sitemap->getXml($request->getSchemeAndHttpHost()), Response::HTTP_OK, [
            'Content-Type' => 'application/xml; charset=UTF-8',
        ]);
    }
}
