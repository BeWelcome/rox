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

    /**
     * returns array of post votes that member has made
     *
     * @access public
     * @return array
     */
    public function getVotes()
    {
        if (!$this->isLoaded())
        {
            return array();
        }
        return $this->createEntity('PostVote')->getVotesForPost($this);
    }

    /**
     * returns array of post votes that member has made
     *
     * @access public
     * @return array
     */
    public function getVoteResult()
    {
        if (!$this->isLoaded())
        {
            return array();
        }
        return $this->createEntity('PostVote')->getResultForPost($this);
    }

    /**
     * returns the count of positive votes for the post
     *
     * @access public
     * @result int
     */
    public function getPositiveVoteCount()
    {
        return getPositiveForPost($this);
    }

    /**
     * returns the count of negative votes for the post
     *
     * @access public
     * @result int
     */
    public function getNegativeVoteCount()
    {
        return getNegativeForPost($this);
    }

    /**
     * returns the count of neutral votes for the post
     *
     * @access public
     * @result int
     */
    public function getNeutralVoteCount()
    {
        return getNeutralForPost($this);
    }
}
