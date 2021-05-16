<?php

namespace App\Controller;

use App\Entity\Member;
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
        /** @var Member $member */
        $member = $this->getUser();
        list($page, $limit, $sort, $direction) = $this->getOptionsFromRequest($request);

        $requestsAndMessages = $this->requestModel->getFilteredRequestsAndMessages(
            $member,
            $folder,
            $sort,
            $direction,
            $page,
            $limit
        );

        return $this->handleFolderRequest($request, $folder, 'both', $requestsAndMessages);
    }
}
