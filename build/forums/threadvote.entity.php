<?php

/**
 * represents a single vote for a forum thread
 *
 */
class ThreadVote extends RoxEntityBase
{

    protected $_table_name = 'forums_threads_votes';

    protected $_validations = array('Vote');

    public function __construct($vote_id = false)
    {
        parent::__construct();
        if (intval($vote_id))
        {
            $this->findById(intval($vote_id));
        }
    }

    /**
     * fetches all votes for a thread
     *
     * @param object $thread
     * @access public
     * @return array
     */
    public function getVotesForThread(Thread $thread)
    {
        if (!$thread->isLoaded())
        {
            return array();
        }
        return $this->findByWhereMany("Idthread = '{$thread->getPKValue()}'");
    }

    /**
     * fetches all thread votes for a member
     *
     * @param object $member
     * @access public
     * @return array
     */
    public function getVotesForMember(Member $member)
    {
        if (!$member->isLoaded())
        {
            return array();
        }
        return $this->findByWhereMany("IdMember = '{$member->getPKValue()}'");
    }

    /**
     * returns array of positive, negative and neutral votes
     *
     * @param object $thread
     * @access public
     * @return array
     */
    public function getResultForThread(Thread $thread)
    {
        if (!$thread->isLoaded())
        {
            return array();
        }
        if (!($result = $this->dao->query("SELECT SUM(positive) AS positive, SUM(negative) AS negative, SUM(neutral) AS neutral FROM (SELECT (CASE WHEN Vote = 'positive' THEN 1 ELSE 0 END) AS positive, (CASE WHEN Vote = 'negative' THEN 1 ELSE 0 END) AS negative, (CASE WHEN Vote = 'neutral' THEN 1 ELSE 0 END) AS neutral FROM {$this->getTableName()} WHERE IdThread = '{$thread->getPKValue()}') AS temp")))
        {
            return array();
        }
        $data = $result->fetch();
        return array('positive' => $data['positive'], 'negative' => $data['negative'], 'neutral' => $data['neutral']);

    }

    /**
     * returns the number of positive votes for the thread
     *
     * @param object $thread
     * @access public
     * @return array
     */
    public function getPositiveForThread(Thread $thread)
    {
        if (!$thread->isLoaded())
        {
            return array();
        }
        list($positive, $negative, $neutral) = $this->getResultForThread($thread);
        return $positive;
    }

    /**
     * returns the number of negative votes for the thread
     *
     * @param object $thread
     * @access public
     * @return int
     */
    public function getNegativeForThread(Thread $thread)
    {
        if (!$thread->isLoaded())
        {
            return array();
        }
        list($positive, $negative, $neutral) = $this->getResultForThread($thread);
        return $negative;
    }

    /**
     * returns the number of neutral votes for the thread
     *
     * @param object $thread
     * @access public
     * @return int
     */
    public function getNeutralForThread(Thread $thread)
    {
        if (!$thread->isLoaded())
        {
            return array();
        }
        list($positive, $negative, $neutral) = $this->getResultForThread($thread);
        return $neutral;
    }

    /**
     * returns the vote that a given member made for a given thread
     *
     * @param object $thread
     * @param object $member
     * @access public
     * @return object|bool
     */
    public function findVote(Thread $thread, Member $member)
    {
        if (!$thread->isLoaded() || !$member->isLoaded())
        {
            return false;
        }
        return $this->findByWhere("IdThread = '{$thread->getPKValue()}' AND IdMember = '{$member->getPKValue()}'");
    }

    /**
     * sets a vote for a given member
     *
     * @param object $thread
     * @param object $member
     * @param string $vote
     * @access public
     * @return bool
     */
    public function castVote(Thread $thread, Member $member, $vote = null)
    {
        if ($this->isLoaded() || !$thread->isLoaded() || !$member->isLoaded())
        {
            return false;
        }
        $this->IdThread = $thread->getPKValue();
        $this->IdMember = $member->getPKValue();
        $this->Vote = ((!empty($vote)) ? $vote : 'neutral');
        $this->created = date('Y-m-d H:i:s');
        return $this->insert();
    }

    /**
     * updates a vote for a given member
     *
     * @param string $vote
     * @access public
     * @return bool
     */
    public function updateVote($vote = null)
    {
        if (!$this->isLoaded())
        {
            return false;
        }
        $this->Vote = ((!empty($vote)) ? $vote : 'neutral');
        $this->updated = date('Y-m-d H:i:s');
        return $this->update();
    }
}
