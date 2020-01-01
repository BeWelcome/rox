<?php

/**
 * represents a single forum post
 *
 */
class Post extends RoxEntityBase
{

    protected $_table_name = 'forums_posts';


    public function __construct($post_id = false)
    {
        parent::__construct();
        if (intval($post_id))
        {
            $this->findByPostId(intval($post_id));
        }
    }

    /**
     * fetches a post by postid
     *
     * @param int $id
     * @access public
     * @return object|bool
     */
    public function findByPostId($id)
    {
        return $this->findByWhere("postid = '{$this->dao->escape($id)}'");
    }

    /**
     * returns a count of how many posts a member has made
     *
     * @param object $member - Member entity
     * @access public
     * @return int
     */
    public function getMemberPostCount(Member $member)
    {
        if (!$member->isLoaded())
        {
            return 0;
        }
        return $this->countWhere("IdWriter = '{$member->getPKValue()}'");
    }
}
