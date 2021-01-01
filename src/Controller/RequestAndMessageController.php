<?php

namespace App\Controller;

use InvalidArgumentException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class RequestAndMessageController.
 */
class RequestAndMessageController extends BaseMessageController
{
    /**
     * @Route("/both/{folder}", name="both",
     *     defaults={"folder": "inbox"})
     */
    public function requestsAndMessages(Request $request, string $folder): Response
    {
        return $this->handleFolderRequest($request, $folder, 'both');
    }
}
