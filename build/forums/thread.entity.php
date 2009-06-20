<?php

/**
 * represents a single forum thread
 *
 */
class Thread extends RoxEntityBase
{

    protected $_table_name = 'forums_threads';


    public function __construct($thread_id = false)
    {
        parent::__construct();
        if (intval($thread_id))
        {
            $this->findByThreadId(intval($thread_id));
        }
    }

    /**
     * fetches a thread by threadid
     *
     * @param int $id
     * @access public
     * @return object|bool
     */
    public function findByThreadId($id)
    {
        return $this->findByWhere("threadid = '{$this->dao->escape($id)}'");
    }

    /**
     * returns array of thread votes that member has made
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
        return $this->createEntity('ThreadVote')->getVotesForThread($this);
    }

    /**
     * returns array of thread votes that member has made
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
        return $this->createEntity('ThreadVote')->getResultForThread($this);
    }

    /**
     * returns the count of positive votes for the thread
     *
     * @access public
     * @result int
     */
    public function getPositiveVoteCount()
    {
        return getPositiveForThread($this);
    }

    /**
     * returns the count of negative votes for the thread
     *
     * @access public
     * @result int
     */
    public function getNegativeVoteCount()
    {
        return getNegativeForThread($this);
    }

    /**
     * returns the count of neutral votes for the thread
     *
     * @access public
     * @result int
     */
    public function getNeutralVoteCount()
    {
        return getNeutralForThread($this);
    }
}
