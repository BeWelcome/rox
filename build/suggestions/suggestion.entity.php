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

            // Get options count for this suggestion (visible to all members)
            $query = 'SELECT COUNT(id) as cnt FROM suggestions_options WHERE suggestionId = ' . $this->id . ' AND deleted IS NULL';
            $sql = $this->dao->query($query);
            $row = $sql->fetch(PDB::FETCH_OBJ);
            $this->optionsVisibleCount = $row->cnt;

            // Load options for this suggestion
            $optionsWhere = "";
            $optionsFactory = $entityFactory->create('SuggestionOption');
            switch ($this->state) {
            	case SuggestionsModel::SUGGESTIONS_VOTING:
            	    $optionsWhere = " AND deleted IS NULL";
                    $optionsFactory->sql_order = "RAND()";
                    break;
            	case SuggestionsModel::SUGGESTIONS_RANKING:
            	    $optionsFactory->sql_order = "`order` DESC";
            	    $optionsWhere = " AND `rank` > 2";
            	    break;
            	default:
            	    $optionsFactory->sql_order = "`deleted` ASC, `id` ASC";
            	    break;
            }
            $this->options = $optionsFactory->FindByWhereMany('suggestionId = ' . $this->id . $optionsWhere);

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
                $this->voteCount = $row->count;
            }

            // if member already voted on this suggestion get votes as well
            $member = $this->getLoggedInMember();
            if ($member) {
                $hash = hash_hmac('sha256', $member->id, $this->salt);
                $query = "SELECT * FROM suggestions_votes WHERE suggestionId = "
                            . $this->id . " AND memberHash = '" . $hash . "' ORDER BY rank DESC";
                $sql = $this->dao->query($query);
                if ($sql) {
                    $votes = array();
                    while($row = $sql->fetch(PDB::FETCH_OBJ)) {
                        $votes[$row->optionId] = $row;
                    }
                    $this->votes = $votes;
                    error_log(print_r($votes, true));
                }
            }

            // $this->ranks = $this->getRanks();

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
            // set next state change date (only needed for open suggestions)
            switch($this->state) {
            	case SuggestionsModel::SUGGESTIONS_DISCUSSION:
            	    $this->nextstatechange = date('Y-m-d', strtotime($this->laststatechanged) + SuggestionsModel::DURATION_DISCUSSION);
            	    break;
            	case SuggestionsModel::SUGGESTIONS_ADD_OPTIONS:
            	    $this->nextstatechange = date('Y-m-d', strtotime($this->laststatechanged) + SuggestionsModel::DURATION_ADDOPTIONS);
            	    break;
            	case SuggestionsModel::SUGGESTIONS_VOTING:
            	    $this->nextstatechange = date('Y-m-d', strtotime($this->laststatechanged) + SuggestionsModel::DURATION_VOTING);
            	    break;
            }
        }
        return $status;
    }

    public function update($status = false) {
        if ($status) {
            $this->laststatechanged = date('Y-m-d');
            switch ($state) {
                case SuggestionsModel::SUGGESTIONS_RANKING:
                    $this->calculateResults();
                break;
            }
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

    private function notifyVotingStarted() {
        $entityFactory = new RoxEntityFactory();
        $suggestionsTeam = $entityFactory->create('Member')->findByUsername('SuggestionsTeam');
        $text = 'Voting for the suggestion \'<a href="/suggestions/' . $this->id . '/>' . $this->summary . '</a>\' has started.<br /><br />Please cast your vote.';
        $suggestions = new SuggestionsModel();
        $postId = $suggestions->addPost($suggestionsTeam->id, $text, $this->threadId);
        $suggestions->setForumNotifications($postId, 'reply');
    }

    /**
     * Find order of options by dropping one median element at a time till
     * option wins
     *
     * returns an ordered list of the options from the first dropped to the last
     */
    private function breakTie($ranks, $count) {
        $orderedList = array();
        $highestMedianFound = false;
        while (!$highestMedianFound && ($count > 0)) {
            $medians = $this->calculateMedians($ranks, $count);
            $maxMedianCount = 0;
            $maxMedian = max($medians);
            foreach($medians as $optionId => $median) {
                if ($median == $maxMedian) {
                    $maxMedianCount++;
                    $ranks[$optionId][$median] = $ranks[$optionId][$median] - 1;
                } else {
                    // Remove option from
                    $orderedList[] = $optionId;
                    unset($ranks[$optionId]);
                }
            }
            if ($maxMedianCount == 1) {
                $highestMedianFound = true;
            }
            $count--;
        }
        // stupid hack to get the remaining optionId
        foreach($ranks as $optionId => $votes) {
            $orderedList[] = $optionId;
        }
        return $orderedList;
    }

    private function calculateMedians($ranks, $count) {
        $medians = array();
        if ($count % 2 == 1) {
            $medianIndex = intval($count/2) + 1;
        } else {
            $medianIndex = intval($count/2);
        }
        foreach($ranks as $optionId => $voteCount) {
            $median = 0;
            $upperBound = 0;
            while ($median <= 4 && ($upperBound < $medianIndex)) {
                $upperBound += $voteCount[$median + 1 ];
                $median++;
            }
            $medians[$optionId] = $median;
        }
        return $medians;
    }

    private function getRanks() {
        $query = "
                SELECT
                    optionid, rank, count(rank) as cnt
                FROM
                    suggestions_votes
                WHERE
                    suggestionid = " . $this->id . "
                GROUP BY
                    optionid, rank
                ORDER BY
                    optionid, rank
                ";
        $sql = $this->dao->query($query);
        if (!$sql) {
            return false;
        }
        $ranks = array();
        while ($row = $sql->fetch(PDB::FETCH_OBJ)) {
            if (!isset($ranks[$row->optionid])) {
                $ranks[$row->optionid] = array(
                                1 => 0,
                                2 => 0,
                                3 => 0,
                                4 => 0
                );
            }
            $ranks[$row->optionid][$row->rank] = $row->cnt;
        }
        return $ranks;
    }

    private function calculateResults() {
        $ranks = $this->getRanks();
        $count = $this->voteCount; // $count / count($ranks);

        // Calculate medians for all options
        $medians = $this->calculateMedians($ranks, $count);
        asort($medians);

        // Now break ties for the different medians
        $ties = array();
        foreach($medians as $optionId => $median) {
            if (!isset($ties[$median])) {
                $ties[$median] = array();
            }
            $ties[$median][$optionId] = $ranks[$optionId];
        }
        $orderedOptionIds = array();
        foreach($ties as $median => $tie) {
            $optionIds = $this->breakTie($tie, $count);
            $orderedOptionIds = array_merge($orderedOptionIds, $optionIds);
        }

        $count = 0;
        $countOptions = count($this->options);
        foreach($orderedOptionIds as $order => $optionId) {
            $query = "
            UPDATE
                suggestions_options
            SET
                `order` = " . $order . ",
                `rank` = " . $medians[$optionId] . "
            WHERE
                `id` = " . $optionId;
            $this->dao->query($query);
        }
        return true;
    }
}