<?php

namespace Rox\Models;


use Illuminate\Database\Eloquent\Model;

class Thread extends Model
{
    public $table = 'forums_threads';

    public function posts() {
        return $this->hasMany('Rox\Models\Post', 'id', 'threadid');
    }

    public function firstPost() {
        return $this->hasOne('Rox\Models\Post', 'id', 'first_postid');
    }

    public function lastPost() {
        return $this->hasOne('Rox\Models\Post', 'id', 'last_postid');
    }
}