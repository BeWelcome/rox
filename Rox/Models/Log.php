<?php

namespace Rox\Models;

use Rox\Core\Model\AbstractModel;
use Rox\Member\Model\Member;

class Log extends AbstractModel
{
    /**
     * @var bool
     */
    public $timestamps = false;

    protected $ormRelationships = [
        'member',
    ];

    /**
     * Get the member record associated with the log entry.
     */
    public function member()
    {
        return $this->hasOne(Member::class, 'id', 'IdMember');
    }

    public function IpAddressString()
    {
        return long2ip($this->IpAddress);
    }

    public function __isset($key)
    {
        return $key !== 'IpAddressString' && parent::__isset($key);
    }
}
