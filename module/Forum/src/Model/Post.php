<?php

namespace Rox\Forum\Model;

use Illuminate\Database\Eloquent\Model;
use Rox\Member\Model\Member;

/**
 * Class Post.
 */
class Post extends Model
{
    /**
     * @var string
     */
    public $table = 'forums_posts';

    /**
     * @var boolean
     */
    public $timestamps = false;

    public function author()
    {
        return $this->hasOne(Member::class, 'id', 'authorid');
    }

    public function thread()
    {
        return $this->hasOne(Thread::class, 'id', 'threadid');
    }

    public function content()
    {
        return $this->hasMany(Translation::class, 'IdTrad', 'IdContent');
    }
}
