<?php

namespace Rox\Models;

use Illuminate\Database\Eloquent\Model;

class Log extends Model {
    public $timestamps = false;

    /**
     * Get the member record associated with the log entry.
     */
    public function member()
    {
        return $this->hasOne('Rox\Models\Member', 'id', 'IdMember');
    }

    public function IpAddressString()
    {
        return long2ip($this->IpAddress);
    }
}