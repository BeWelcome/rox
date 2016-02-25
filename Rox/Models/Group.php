<?php

namespace Rox\Models;

use Illuminate\Database\Eloquent\Model;

class Group extends Model
{
    public $timestamps = false;

    public function members() {
        return $this->hasManyThrough('Rox\Models\Members', 'Rox\Models\MemberGroup', 'id', 'IdGroup');
    }
}