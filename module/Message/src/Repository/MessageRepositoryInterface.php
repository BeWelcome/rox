<?php

namespace Rox\Message\Repository;

use Rox\Core\Exception\NotFoundException;
use Rox\Message\Model\Message;

interface MessageRepositoryInterface
{
    /**
     * @param $id
     *
     * @return Message
     *
     * @throws NotFoundException
     */
    public function getById($id);
}
