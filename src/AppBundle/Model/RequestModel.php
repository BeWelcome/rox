<?php

namespace AppBundle\Model;

use AppBundle\Entity\Message;
use AppBundle\Repository\MessageRepository;

class RequestModel extends BaseModel
{
    public function getFilteredRequests($member, $folder, $sort, $sortDir, $page = 1, $limit = 10)
    {
        /** @var MessageRepository $repository */
        $repository = $this->em->getRepository(Message::class);

        return $repository->findLatest($member, 'requests_'.$folder, $sort, $sortDir, $page, $limit);
    }
}
