<?php


class NotifyModel extends RoxModelBase
{
    private $_notes_list = 0;

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Find and return one note, using id
     *
     * @param int $note_id
     * @return mixed false or a Note entity
     */
    public function findNote($note_id)
    {
        $note = $this->createEntity('Note',$note_id);
        if ($note->isLoaded())
        {
            return $note;
        }
        else
        {
            return false;
        }
    }

    /**
     * Find and return one note, using id
     *
     * @param int $note_id
     * @return mixed false or a Note entity
     */
    public function deleteNotes($notes = array())
    {
        foreach ($notes as $note) {
            $this->createEntity('Note', $note)->deleteNote();
        }
    }


    /**
     * Find and return notes, using search terms from search page
     *
     * @param string $terms - search terms
     * @return mixed false or an array of Notes
     */
    public function findNotes($terms = '', $page = 0, $order = '')
    {

        if (!empty($order))
        {
            switch ($order)
            {
                case "nameasc":
                    $order = 'Name ASC';
                    break;
                case "namedesc":
                    $order = 'Name DESC';
                    break;
                case "membersasc":
                    $order = '(SELECT SUM(IdMember) FROM membersgroups as mg WHERE IdGroup = groups.id) ASC, Name ASC';
                    break;
                case "membersdesc":
                    $order = '(SELECT SUM(IdMember) FROM membersgroups as mg WHERE IdGroup = groups.id) DESC, Name ASC';
                    break;
                case "createdasc":
                    $order = 'created ASC, Name ASC';
                    break;
                case "createddesc":
                    $order = 'created DESC, Name ASC';
                    break;
                case "category":
                default:
                    $order = 'created DESC, Name ASC';
                    break;
            }
        }
        else
        {
            $order = 'Name ASC';
        }

        $terms_array = explode(' ', $terms);

        $note = $this->createEntity('Note');
        $note->sql_order = $order;
        return $this->_note_list = $note->findBySearchTerms($terms_array, ($page * 10));
    }


    /**
     * Find all notes
     *
     * @access public
     * @return array Returns an array of Group entity objects
     */
    public function findAllNotes($offset = 0, $limit = 0)
    {
        if ($this->_note_list != 0)
        {
            return $this->_note_list;
        }

        $note = $this->createEntity('Note');
        $note->sql_order = 'created DESC, IdMember ASC';
        if (isset($order) && $order != false)
            $note->sql_order = $order. ' ASC, IdMember ASC';
        return $this->_note_list = $note->findAll($offset, $limit);
    }


    /**
     * Get array of notes
     *
     * @access public
     * @return mixed Returns an array of Note entity objects or false if you're not logged in
     */
    public function getNotes($order = false, $where = false)
    {
        $sql = '
SELECT *
FROM
    notes
';
        if (isset($where) && $where != '') $sql .= $where;
        if (isset($order) && $order != false) $sql .= '
ORDER BY '.$order.' ASC,IdMember ASC
';
        else $sql .= '
ORDER BY created DESC,IdMember ASC
';
        return $this->bulkLookup($sql);

    }

    /**
     * Find all notes I am member of
     *
     * @access public
     * @return mixed Returns an array of Note entity objects or false if you're not logged in
     */
    public function getMyNotes()
    {
        if (!$this->session->has( 'IdMember' ))
        {
            return array();
        }
        else
        {
            return $this->getNotesForMember($this->session->get('IdMember'));
        }
    }

    public function getMemberNotes()
    {
        if (!$this->session->has( 'IdMember' )) {
            // not logged in - no messages
            return array();
        } else {
            $member_id = $this->session->get('IdMember');
            return $this->getNotes(false,'WHERE notes.IdMember = '.$member_id.' AND notes.Checked = 0');
        }
    }

    /**
     * handles deleting groups
     *
     * @param object $note - group entity to be deleted
     * @return bool
     * @access public
     */
    public function checkNote($note)
    {
        if (!is_object($note) || !$note->isLoaded())
        {
            return false;
        }
        return $note->checkNote();
    }

    /**
     * handles deleting groups
     *
     * @param object $note - group entity to be deleted
     * @return bool
     * @access public
     */
    public function createNote($note)
    {
        $noteEntity = $this->createEntity('Note');
        return $noteEntity->createNote($note);
    }


}
