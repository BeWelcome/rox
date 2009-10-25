<?php

/**
 * represents a single vote for a post
 *
 */
class PostVote extends RoxEntityBase
{

    protected $_table_name = 'forums_posts_votes';

    protected $_validations = array('Choice');

    public function __construct()
    {
        parent::__construct();
    }


    /**
     * fetches all post votes for a post
     *
     * @param object $post
     * @access public
     * @return array
     */
    public function getVotesForPost(Post $post)
    {
        if (!$post->isLoaded())
        {
            return array();
        }
        return $this->findByWhereMany("IdPost = '{$post->getPKValue()}'");
    }

    /**
     * fetches all votes for a member
     *
     * @param object $member
     * @access public
     * @return array
     */
    public function getVotesForMember(Member $member)
    {
        if (!$post->isLoaded())
        {
            return array();
        }
        return $this->findByWhereMany("IdContributor = '{$member->getPKValue()}'");
    }

    /**
     * returns array of positive, negative and neutral votes
     *
     * @param object $post
     * @access public
     * @return array
     */
    public function getResultForPost(Post $post)
    {
        $return = array('Yes' => 0, 'No' => 0, 'DontCare' => 0, 'DontKnow' => 0);
        if (!$post->isLoaded())
        {
            return $return;
        }
        if (!($result = $this->dao->query("SELECT SUM(yes) AS yes, SUM(no) AS no, SUM(dontcare) AS dontcare, SUM(dontknow) AS dontknow FROM (SELECT (CASE WHEN Choice = 'Yes' THEN 1 ELSE 0 END) AS yes, (CASE WHEN Choice = 'No' THEN 1 ELSE 0 END) AS no, (CASE WHEN Choice = 'DontCare' THEN 1 ELSE 0 END) AS dontcare, (CASE WHEN Choice = 'DontKnow' THEN 1 ELSE 0 END) AS dontknow FROM {$this->getTableName()} WHERE IdPost = '{$post->getPKValue()}') AS temp")))
        {
            return $return;
        }
        $data = $result->fetch();
        return array('Yes' => $data['yes'], 'No' => $data['no'], 'DontCare' => $data['dontcare'], 'DontKnow' => $data['dontknow']);

    }

    /**
     * returns the number of positive votes for the post
     *
     * @param object $post
     * @access public
     * @return array
     */
    public function getPositiveForPost(Post $post)
    {
        if (!$post->isLoaded())
        {
            return array();
        }
        list($positive, $negative, $neutral) = $this->getResultForPost($post);
        return $positive;
    }

    /**
     * returns the number of negative votes for the post
     *
     * @param object $post
     * @access public
     * @return int
     */
    public function getNegativeForPost(Post $post)
    {
        if (!$post->isLoaded())
        {
            return array();
        }
        list($positive, $negative, $neutral) = $this->getResultForPost($post);
        return $negative;
    }

    /**
     * returns the number of neutral votes for the post
     *
     * @param object $post
     * @access public
     * @return int
     */
    public function getNeutralForPost(Post $post)
    {
        if (!$post->isLoaded())
        {
            return array();
        }
        list($positive, $negative, $neutral) = $this->getResultForPost($post);
        return $neutral;
    }

    /**
     * returns the vote that a given member made for a given post
     *
     * @param object $post
     * @param object $member
     * @access public
     * @return object|bool
     */
    public function findVote(Post $post, Member $member)
    {
        if (!$post->isLoaded() || !$member->isLoaded())
        {
            return false;
        }
        return $this->findByWhere("IdPost = '{$post->getPKValue()}' AND IdContributor = '{$member->getPKValue()}'");
    }

    /**
     * sets a vote for a given member
     *
     * @param object $post
     * @param object $member
     * @param string $vote
     * @access public
     * @return bool
     */
    public function castVote(Post $post, Member $member, $vote = null)
    {
        if ($this->isLoaded() || !$post->isLoaded() || !$member->isLoaded())
        {
            return false;
        }
        $this->IdPost = $post->getPKValue();
        $this->IdContributor = $member->getPKValue();
        $this->Choice = ((in_array($vote, array('Yes','No','DontKnow','DontCare'))) ? $vote : 'DontKnow');
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
        $this->Choice = ((in_array($vote, array('Yes','No','DontKnow','DontCare'))) ? $vote : 'DontKnow');
        $this->updated = date('Y-m-d H:i:s');
        return $this->update();
    }
}
