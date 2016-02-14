<?php

namespace Rox\Models;

use Illuminate\Database\Eloquent\Model;

class Message extends Model {

    public $table = 'messages';

    public $timestamps = false;

    public function sender()
    {
        return $this->hasOne('Rox\Models\Member', 'id', 'IdSender');
    }
}