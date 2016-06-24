<?php

namespace Rox\Member\Model;

use Rox\Auth\Model\Right;
use Rox\Core\Model\AbstractModel;

/**
 * @property Right $right
 */
class MemberRight extends AbstractModel
{
    /**
     * @var string
     */
    protected $table = 'rightsvolunteers';

    public function right()
    {
        return $this->hasOne(Right::class, 'id', 'IdRight');
    }
}
