<?php

namespace Rox\Member\Model;

use Illuminate\Database\Eloquent\Model;

class Trad extends Model
{
    const CREATED_AT = 'created';
    const UPDATED_AT = 'updated';

    /**
     * @var string
     */
    protected $table = 'memberstrads';

    public function member()
    {
        return $this->hasOne(Member::class, 'id', 'IdOwner');
    }

    public function __isset($key)
    {
        return parent::__isset($key) || in_array($key, ['fromMember'], true);
    }
}
