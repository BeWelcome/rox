<?php

namespace Rox\Auth\Model;

use Rox\Core\Model\AbstractModel;

class VolunteerRight extends AbstractModel
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
