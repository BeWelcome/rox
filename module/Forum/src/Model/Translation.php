<?php

namespace Rox\Forum\Model;

use Illuminate\Database\Eloquent\Model;

/**
 * Class Translation
 */
class Translation extends Model
{
    /**
     * @var string
     */
    public $table = 'forum_trads';

    /**
     * @var boolean
     */
    public $timestamps = false;
}
