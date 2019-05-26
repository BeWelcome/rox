<?php

namespace App\Controller;

use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
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
     *
     * @param Request $request
     * @param string $folder
     *
     * @return Response
     * @throws InvalidArgumentException
     */
    public function requestsAndMessages(Request $request, $folder)
    {
        $page = $request->query->get('page', 1);
        $limit = $request->query->get('limit', 10);
        $sort = $request->query->get('sort', 'datesent');
        $sortDir = $request->query->get('dir', 'desc');

        if (!\in_array($sortDir, ['asc', 'desc'], true)) {
            throw new InvalidArgumentException();
        }

        $member = $this->getUser();
        $messages = $this->messageModel->getFilteredRequestsAndMessages($member, $sort, $sortDir, $page, $limit);

        return $this->handleFolderRequest($request, $folder, $messages, 'both');
    }
}
