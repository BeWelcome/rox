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

            // Get number of discussion items (if thread ID != null)
            $this->posts = 0;
            if ($this->threadId) { 
                $query = "SELECT COUNT(*) as count FROM forums_posts WHERE threadid = " . $this->threadId . " AND PostDeleted = 'NotDeleted'";
                $sql = $this->dao->query($query);
                if ($sql) {
                    $row = $sql->fetch(PDB::FETCH_OBJ);
                    $this->posts = $row->count;
                }
            }

            // Get number of votes
            $query = "SELECT COUNT(DISTINCT memberHash) AS count FROM suggestions_votes WHERE suggestionId = " . $this->id;
            $sql = $this->dao->query($query);
            if ($sql) {
                $row = $sql->fetch(PDB::FETCH_OBJ);
                $this->votes = $row->count;
            }

            // check if state should be updated
            switch($this->state) {
                case SuggestionsModel::SUGGESTIONS_DISCUSSION:
                    $laststatechanged = strtotime($this->laststatechanged);
                    // in discussion for more than 10 days?
                    if (time() - $laststatechanged > SuggestionsModel::DURATION_DISCUSSION) {
                        $this->state = SuggestionsModel::SUGGESTIONS_ADD_OPTIONS;
                        $this->update(true);
                    }
                    break;
                case SuggestionsModel::SUGGESTIONS_ADD_OPTIONS:
                    $laststatechanged = strtotime($this->laststatechanged);
                    // in addoptions for more than 20 days?
                    if (time() - $laststatechanged > SuggestionsModel::DURATION_ADDOPTIONS) {
                        if (count($this->options)) {
                            $this->state = SuggestionsModel::SUGGESTIONS_VOTING;
                        } else {
                            // no options added -> rejected
                            $this->state = SuggestionsModel::SUGGESTIONS_REJECTED;
                        }
                        $this->update(true);
                    }
                    break;
                case SuggestionsModel::SUGGESTIONS_VOTING:
                    $laststatechanged = strtotime($this->laststatechanged);
                    // voting open for more than 30 days? 
                    if (time() - $laststatechanged > SuggestionsModel::DURATION_VOTING) {
                        $this->state = SuggestionsModel::SUGGESTIONS_RANKING;
                        $this->update(true);
                    }
                    break;
            }
        }
        return $status;
    }

    public function update($status = false) {
        if ($status) {
            $this->laststatechanged = date('Y-m-d');
            return parent::update();
        } else {
            // update all fields except for the status field
            // as this might have changed in the mean time
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
        $option->modified = date('Y-m-d');
        $option->modifiedBy = $this->getLoggedInMember()->id;
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