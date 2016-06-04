<?php

namespace Rox\Models;

use Illuminate\Database\Eloquent\Model;
use Rox\Member\Model\Member;

class Note extends Model
{
    const CREATED_AT = 'created';

    public $table = 'notes';

    public function notifier()
    {
        return $this->hasOne(Member::class, 'id', 'IdRelMember');
    }
}