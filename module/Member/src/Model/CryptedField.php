<?php

namespace Rox\Member\Model;

use Illuminate\Database\Eloquent\Model;

class CryptedField extends Model
{
    /**
     * @var string
     */
    protected $table = 'cryptedfields';

    public function getMemberCryptedValueAttribute()
    {
        return strip_tags($this->getAttributeFromArray('MemberCryptedValue'));
    }
}
