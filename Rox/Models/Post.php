<?php

namespace Rox\Models;

use Illuminate\Database\Eloquent\Model;
use Rox\Forum\Model\Translation;
use Rox\Member\Model\Member;

class Post extends Model
{
    public $table = 'forums_posts';

    public $timestamps = false;

    public function author() {
        return $this->hasOne(Member::class, 'id', 'authorid');
    }

    public function thread() {
        return $this->hasOne(Thread::class, 'id', 'threadid');
    }

    public function content() {
        return $this->hasMany(Translation::class, 'IdTrad', 'IdContent');
    }
}
