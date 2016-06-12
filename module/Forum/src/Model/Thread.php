<?php

namespace Rox\Forum\Model;

use Illuminate\Database\Eloquent\Model;

/**
 * Class Thread
 */
class Thread extends Model
{
    /**
     * @var string
     */
    public $table = 'forums_threads';

    public function posts()
    {
        return $this->hasMany(Post::class, 'id', 'threadid');
    }

    public function firstPost()
    {
        return $this->hasOne(Post::class, 'id', 'first_postid');
    }

    public function lastPost()
    {
        return $this->hasOne(Post::class, 'id', 'last_postid');
    }
}
