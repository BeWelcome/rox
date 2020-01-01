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
     * returns true if the thread was deleted
     *
     * @access public
     * @return bool
     */
    public function isDeleted()
    {
        return (!$this->isLoaded || $this->ThreadDeleted == 'Deleted');
    }
}
