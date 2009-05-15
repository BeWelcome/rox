<?php

//todo: base group atom on different class

/**
 * represents a single group
 *
 */
class Note extends RoxEntityBase
{
    protected $_table_name = 'notes';

    public function __construct($ini_data, $note_id = false)
    {
        parent::__construct($ini_data);
        if (intval($note_id))
        {
            $return = $this->findById(intval($note_id));
        }
    }


    /**
     * Uses an array of terms to create a create to search for groups with
     * simple or search on names for now
     *
     * @todo implement proper group search - this will wait on various db implementations
     * @param array $terms - array of strings to be used in search
     * @return mixed false or group of arrays that match any of the terms
     * @access public
     */
    public function findBySearchTerms($terms = array(), $page = 0)
    {
        if (empty($terms))
        {
            return $this->findAll($page, 10);
        }
        
        foreach ($terms as &$term)
        {
            if (is_string($term))
            {
                $term = "{$this->_table_name}.Name LIKE '%" . $this->dao->escape($term) . "%'";
            }
            else
            {
                unset($term);
            }
        }
        
        $clause = implode(' or ', $terms);

        return $this->findByWhereMany($clause, $page, 10);

    }


    /**
     * Check if a member id is connected with a group
     *
     * @param int $member_id - id of the member to check
     * @access public
     * @return bool
     */
    public function isMember($member)
    {
        if (!$this->_has_loaded)
        {
            return false;
        }

        return $this->createEntity('GroupMembership')->isMember($this, $member);
    }


    /**
     * Create a note given some input
     *
     * @param array $input - array containing IdMember, IdRelMember and Type
     * @access public
     * @return mixed Will return the insert id of the operation or false
     */
    public function createNote($input)
    {
        $idmember = $this->dao->escape($input['IdMember']);
        $type = $this->dao->escape($input['Type']);
        $idrelmember = ((!empty($input['IdRelMember'])) ? $this->dao->escape($input['IdRelMember']) : '');
        $wordcode = ((!empty($input['WordCode'])) ? $this->dao->escape($input['WordCode']) : '');
        $link = ((!empty($input['Link'])) ? $this->dao->escape($input['Link']) : '');
        $vartext = ((!empty($input['VarText'])) ? $this->dao->escape($input['VarText']) : '');

        $this->IdMember = $idmember;
        $this->IdRelMember = $idrelmember;
        $this->Type = $type;
        $this->Link = $link;
        $this->WordCode = $wordcode;
        $this->VarText = $vartext;
        return $this->insert();
    }

    /**
     * Delete a note
     * Removes a row from the notes table and unsets data in the entity so it can't be reused
     *
     * @access public
     * @return bool
     */
    public function deleteNote()
    {
        if (!$this->isLoaded())
        {
            return false;
        }

        if ($this->delete())
        {
            return true;
        }
        else
        {
            return false;
        }
    }
    

    /**
     * updates a note
     *
     * @param string $description - the description of the group
     * @param string $type - how public the group is
     * @param string $visible_posts - if the forum posts of the group should be visible or not
     * @access public
     * @return bool
     */
    public function updateNote($check = false, $type = false, $visible = false, $freetext = false)
    {
        if (!$this->isLoaded())
        {
            return false;
        }
        if (isset($check))
        {
            $this->Checked = intval($check);
        }
        if (isset($type))
        {
            $this->Type = $this->dao->escape($type);
        }
        if (isset($visible))
        {
            $this->Visible = $this->dao->escape($visible);
        }
        if (isset($freetext))
        {
            $this->FreeText = $this->dao->escape($freetext);
        }                
        return $this->update();
    }


    /**
     * returns the variable text for a note
     *
     * @access public
     * @return string
     */
    public function getVarText()
    {
        if (!$this->isLoaded() || !$this->VarText)
        {
            return '';
        }   
        return $this->VarText;
    }

    /**
     * returns the description for a group
     *
     * @access public
     * @return string
     */
    public function getIdRelMember()
    {
        if (!$this->isLoaded() || !$this->IdRelMember)
        {
            return '';
        }   
        return $this->getWords()->mTrad($this->IdRelMember);
    }
    
        /**
     * returns the full note for a group
     *
     * @access public
     * @return string
     */
    public function getText()
    {
        $words = new MOD_words();
        if (!$this->isLoaded() || !$this->WordCode)
        {
            return '';
        }   
        return $words->get($this->WordCode,$this->VarText);
    }

}

