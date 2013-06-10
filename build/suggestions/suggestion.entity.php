<?php

/**
 * represents a single decision
 *
 */
class Suggestion extends RoxEntityBase
{
    protected $_table_name = 'suggestions';

    public function __construct($decisionId = false)
    {
        parent::__construct();
        if ($decisionId)
        {
            $this->findById($decisionId);
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
            // Add results etc. later
            if ($this->modifiedby) {
                $this->modifier = $entityFactory->create('Member', $this->modifiedby);
            }
            // Load options for this suggestion
            $options = array();
            $query = "SELECT * FROM suggestions_options WHERE suggestionId = " . $this->id . " ORDER BY RAND()";
            $sql = $this->dao->query($query);
            if ($sql) {
                while ($row = $sql->fetch(PDB::FETCH_OBJ)) {
                    $options[] = $row;
                }
            }
            $this->options = $options;
            $query = "SELECT COUNT(DISTINCT memberHash) AS count FROM suggestions_votes WHERE suggestionId = " . $this->id;
            error_log($query);
            $sql = $this->dao->query($query);
            if ($sql) {
                $row = $sql->fetch(PDB::FETCH_OBJ);
                $this->votes = $row->count;
            }
        }
        return $status;
    }
}