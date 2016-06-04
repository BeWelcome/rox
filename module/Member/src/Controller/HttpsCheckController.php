<?php

namespace Rox\Member\Controller;

use DateTime;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class HttpsCheckController
 */
class HttpsCheckController
{
    public function __invoke(Request $request)
    {
        $response = new Response();

        $cookie = new Cookie('use_https', true, new DateTime('now + 1 year'));

        $response->headers->setCookie($cookie);

        $response->headers->set('Access-Control-Allow-Credentials', 'true');

        if ($origin = $request->headers->get('Origin')) {
            $response->headers->set('Access-Control-Allow-Origin', $origin);
        }

        return $response;
    }
}
