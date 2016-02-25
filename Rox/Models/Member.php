<?php

namespace Rox\Models;

use Illuminate\Database\Eloquent\Model;
use Symfony\Component\Security\Core\Role\Role;
use Symfony\Component\Security\Core\User\EquatableInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class Member extends Model {
    public $timestamps = false;

    public function groups() {
        return $this->belongsToMany('Rox\Models\Group', 'membersgroups', 'IdMember', 'IdGroup');
    }
}