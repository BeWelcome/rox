<?php

namespace Rox\Member\Model;

use Illuminate\Database\Eloquent\Model;

class Comment extends Model
{
    const CREATED_AT = 'created';

    public function fromMember()
    {
        return $this->hasOne(Member::class, 'id', 'IdFromMember');
    }

    public function __isset($key)
    {
        return parent::__isset($key) || in_array($key, ['fromMember'], true);
    }
}
