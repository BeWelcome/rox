<?php

namespace Rox\Models;


use Illuminate\Database\Eloquent\Model;

class Note extends Model
{
    public $table = 'notes';

    public $timestamps = false;

    public function notifier() {
        return $this->hasOne('Rox\Models\Member', 'id', 'IdRelMember');
    }
}