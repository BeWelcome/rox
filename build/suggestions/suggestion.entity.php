<?php

/**
 * represents a single suggestion
 *
 */
class Suggestion extends RoxEntityBase
{
    protected $_table_name = 'suggestions';

    public function __construct($suggestionId = false)
    {
        parent::__construct();
        if ($suggestionId)
        {
            $this->findById($suggestionId);
        }
    }

    /**
     * overloads RoxEntityBase::loadEntity to load related data
     *
     * @param array $data
     *
     * @access protected
     * @return bool
     */
    protected function loadEntity(array $data)
    {
        if ($status = parent::loadEntity($data))
        {
            $entityFactory = new RoxEntityFactory();
            $this->creator = $entityFactory->create('Member', $this->createdby);
            if ($this->modifiedby) {
                $this->modifier = $entityFactory->create('Member', $this->modifiedby);
            }

            // Load options for this suggestion
            $this->options = $entityFactory->create('SuggestionOption')->FindByWhereMany('suggestionId = ' . $this->id);

            // Get number of votes
            $query = "SELECT COUNT(DISTINCT memberHash) AS count FROM suggestions_votes WHERE suggestionId = " . $this->id;
            $sql = $this->dao->query($query);
            if ($sql) {
                $row = $sql->fetch(PDB::FETCH_OBJ);
                $this->votes = $row->count;
            }

            // get voting ended
            $query = "SELECT UNIX_TIMESTAMP(votingend) AS votingendts FROM " . $this->_table_name . " WHERE id = " . $this->id;
            if ($result = $this->dao->query($query)) {
                $timestamp = $result->fetch(PDB::FETCH_OBJ);
                $this->votingendts = $timestamp->votingendts;
            } else {
                $this->votingendts = 0;
            }
        }
        return $status;
    }

    public function update($status = false) {
        if ($status) {
            return parent::update();
        } else {
            // update all fields except for the status field
            // as this might have changed in the mean time
//             $voteStart = $this->dao->escape($this->votingstart) ? $this->dao->escape($this->votingstart) : 'NULL';
//             $voteEnd = $this->dao->escape($this->votingend) ? $this->dao->escape($this->votingend) : 'NULL';
//             $rankStart = $this->dao->escape($this->rankingstarted) ? $this->dao->escape($this->rankingstarted) : 'NULL';
//             $rankEnd = $this->dao->escape($this->rankingended) ? $this->dao->escape($this->rankingended) : 'NULL';
//             ,
//             threadId = " . $this->dao->escape($this->threadId) . ",
//             votingstart = " . $voteStart . ",
//             votingend = " . $voteEnd . ",
//             rankingstarted = " . $rankStart . ",
//             rankingended = " . $rankEnd . "
            $query = "
                UPDATE
                    suggestions
                SET
                    summary = '" . $this->dao->escape($this->summary) . "',
                    description = '" . $this->dao->escape($this->description) . "',
                    modified = NOW(),
                    modifiedby = " . $this->getLoggedInMember()->id . "
                WHERE
                    id = " . $this->id;
            $this->dao->query($query);
        }
    }

    public function addOption($summary, $description) {
        $entityFactory = new RoxEntityFactory();
        $option = $entityFactory->create('SuggestionOption');
        $option->suggestionId = $this->id;
        $option->summary = $summary;
        $option->description = $description;
        $option->created = date('Y-m-d');
        $option->createdBy = $this->getLoggedInMember()->id;
        $option->insert();
        return $option;
    }

    public function editOption($optionId, $summary, $description) {
        $entityFactory = new RoxEntityFactory();
        $option = $entityFactory->create('SuggestionOption')->findById($optionId);
        $option->summary = $summary;
        $option->description = $description;
        $option->updated = date('Y-m-d');
        $option->updatedBy = $this->getLoggedInMember()->id;
        $option->update();
    }

    public function deleteOption($optionId) {
        $entityFactory = new RoxEntityFactory();
        $option = $entityFactory->create('SuggestionOption')->findById($optionId);
        $option->deleted = date('Y-m-d');
        $option->deletedBy = $this->getLoggedInMember()->id;
        $option->update();
    }
}