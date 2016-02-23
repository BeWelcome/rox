<?php

namespace Rox\Models;


use Illuminate\Database\Eloquent\Model;

class Post extends Model
{
    public $table = 'forums_posts';

    public $timestamps = false;

    public function author() {
        return $this->hasOne('Rox\Models\Member', 'id', 'authorid');
    }

    public function thread() {
        return $this->hasOne('Rox\Models\Thread', 'id', 'threadid');
    }

    public function content() {
        return $this->hasMany('Rox\Models\Translation', 'IdTrad', 'IdContent');
    }
}