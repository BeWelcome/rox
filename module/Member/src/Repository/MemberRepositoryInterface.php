<?php

namespace Rox\Member\Repository;

use Rox\Core\Exception\NotFoundException;
use Rox\Member\Model\Member;

interface MemberRepositoryInterface
{
    /**
     * @param $username
     *
     * @return Member
     *
     * @throws NotFoundException
     */
    public function getByUsername($username);

    /**
     * @param $id
     *
     * @return Member
     *
     * @throws NotFoundException
     */
    public function getById($id);
}
