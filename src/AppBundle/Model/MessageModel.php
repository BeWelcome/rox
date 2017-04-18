<?php

namespace AppBundle\Model;

use AppBundle\Entity\Message;
use AppBundle\Repository\MessageRepository;

class MessageModel extends BaseModel
{
    public function getFilteredMessages($member, $filter, $sort, $sortDir, $page = 1, $limit = 10)
    {
        /** @var MessageRepository $repository */
        $repository = $this->em->getRepository(Message::class);

        return $repository->findLatest($member, $filter, $sort, $sortDir, $page, $limit);
    }
}
