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
                    $optionsFactory->sql_order = "`orderHint` DESC";
                    $optionsWhere = " AND deleted IS NULL";
                    break;
                case SuggestionsModel::SUGGESTIONS_REJECTED:
                    $optionsFactory->sql_order = "`orderHint` DESC";
                    $optionsWhere = " AND deleted IS NULL";
                    break;
                case SuggestionsModel::SUGGESTIONS_IMPLEMENTED:
                    $optionsFactory->sql_order = "rank DESC, `modified` DESC";
                    $optionsWhere = " AND deleted IS NULL";
                    break;
                case SuggestionsModel::SUGGESTIONS_IMPLEMENTING:
                    $optionsFactory->sql_order = "rank DESC, `modified` DESC";
                    $optionsWhere = " AND deleted IS NULL";
                    break;
                default:
                    $optionsFactory->sql_order = "`deleted` ASC, `id` ASC";
                    break;
            }

            $this->options = $optionsFactory->FindByWhereMany('suggestionId = ' . $this->id . $optionsWhere);

            $this->exclusionsSet = false;
            foreach($this->options as $option) {
                $this->exclusionsSet |= ($option->mutuallyExclusive != 'All');
            }

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
                    $memberVotes = array();
                    while($row = $sql->fetch(PDB::FETCH_OBJ)) {
                        $memberVotes[$row->optionId] = $row;
                    }
                    $this->memberVotes = $memberVotes;
                }
            }

            $this->ranks = $this->getRanks();

            // check if state should be updated
            $laststatechanged = strtotime($this->laststatechanged);
            switch($this->state) {
                case SuggestionsModel::SUGGESTIONS_DISCUSSION:
                    // in discussion for more than 10 days?
                    if (time() - $laststatechanged > SuggestionsModel::DURATION_DISCUSSION) {
                        $this->state = SuggestionsModel::SUGGESTIONS_ADD_OPTIONS;
                        $this->update(true);
                    }
                    break;
                case SuggestionsModel::SUGGESTIONS_ADD_OPTIONS:
                    // post voting starts in five days?
                    $elapsed = time() - $laststatechanged;
                    if ( $elapsed > SuggestionsModel::DURATION_ADDOPTIONS - SuggestionsModel::DURATION_VOTING_STARTS) {
                        $flags = $this->flags;
                        if (!$flags || ($flags & 1) != 1) {
                            $this->postVotingStartsMessage();
                            if (!$flags) {
                                $this->flags = 1;
                            } else {
                                $this->flags = $this->flags | 1;
                            }
                            $this->update();
                        }
                    }
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
                    // post voting starts in five days?
                    $elapsed = time() - $laststatechanged;
                    if ( $elapsed > SuggestionsModel::DURATION_VOTING - SuggestionsModel::DURATION_VOTING_STARTS) {
                        $flags = $this->flags;
                        if (!$flags || ($flags & 2) != 2) {
                            $this->postVotingEndsMessage();
                            if (!$flags) {
                                $this->flags = 2;
                            } else {
                                $this->flags = $this->flags | 2;
                            }
                            $this->update();
                        }
                    }
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
            switch ($this->state) {
                case SuggestionsModel::SUGGESTIONS_VOTING:
                    $this->notifyVotingStarted();
                    break;
                case SuggestionsModel::SUGGESTIONS_RANKING:
                    $this->calculateResults();
                    // $this->state = SuggestionsModel::SUGGESTIONS_VOTING;
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
                    modified = NOW(),";
            // Check if flags is 0 and ensure correct value
            if ($this->flags === 0) {
                $query .= " `flags` = 0";
            } else {
                $query .= " `flags` = " . $this->dao->escape($this->flags);
            }
            $query .= ",
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
        $text = 'Voting for the suggestion \'<a href="/suggestions/' . $this->id . '/">' . $this->summary . '</a>\' has started.<br /><br />Please cast your vote.';
        $suggestions = new SuggestionsModel();
        $postId = $suggestions->addPost($suggestionsTeam->id, $text, $this->threadId);
        $suggestions->setForumNotification($postId, 'reply');
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
        $entityFactory = new RoxEntityFactory();

        $ranks = $this->getRanks();
        $count = $this->voteCount;

        // Calculate medians for all options
        $medians = $this->calculateMedians($ranks, $count);
        asort($medians);

        // Now break ties for the different medians
        // Hint: Majority judgement would only break the tie for the best rank
        // as we allow options that overlap we need to break tie for all ranks
        // to get a meaningful order
        $ties = array();
        $goodOrExcellent = array();
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
        $orderedOptionIds = array_reverse($orderedOptionIds, true);
        $rankNames = SuggestionsModel::getRanksAsArray();

        $postText = '<p>Voting for \'' . $this->summary . '\' is no longer possible.</p>';
        $postText .= '<p>Number of votes given: ' . $this->voteCount . '</p>';
        $postText .= '<p>Detailed results:</p>';
        $postText .= '<table id="votingresults"><tr><th class="description">Solution</th><th class="results" colspan="2">Results</th><th class="rank">Median</th></tr>';
        foreach($orderedOptionIds as $order => $optionId) {
            $option = $entityFactory->create('SuggestionOption')->findById($optionId);
            $option->orderHint = $order;
            $option->rank = $medians[$optionId];
            $option->update();
            if ($medians[$optionId] > 2) {
                $goodOrExcellent[$optionId] = $option;
            }
            foreach($rankNames as $rank => $rankName) {
                if ($rank == 4) {
                    $postText .= '<tr><td class="description" rowspan="4">' . $option->summary . '</td>'
                        . '<td class="resultsleft">' . $rankName . ':</td><td class="resultsright">'
                        . $ranks[$optionId][$rank] . '</td><td class="rank" rowspan="4">' . $rankNames[$medians[$optionId]]
                        . '</td></tr>';
                } else {
                    $postText .= '<tr><td class="resultsleft">' . $rankName . ':</td><td class="resultsright">'
                        . $ranks[$optionId][$rank] . '</td></tr>';
                }
            }
        }
        $postText .= '</table>';

        // Check if any option got a higher rank than 'acceptable (2)'
        if(empty($goodOrExcellent)) {
            $postText .= '<p>No solution got a better median than \'' . $rankNames[2] . '\' therefore the suggestion is rejected.</p>';
            $this->state = SuggestionsModel::SUGGESTIONS_REJECTED;
        } else {
            // Remove options that are exclusive with the winning option(s)
            $remove = array();

            foreach($goodOrExcellent as $optionId => $option) {
                if ($option->mutuallyExclusive === 'All') {
                    foreach($goodOrExcellent as $checkId => $checkOption) {
                        if ($optionId != $checkId && isset($goodOrExcellent[$checkId]) && ($option->orderHint > $checkOption->orderHint)) {
                            $remove[] = $checkId;
                        }
                    }
                } else {
                    foreach($option->mutuallyExclusive as $exclude) {
                        if (isset($goodOrExcellent[$exclude]) && ($option->orderHint > $goodOrExcellent[$exclude]->orderHint)) {
                            $remove[] = $exclude;
                        }
                    }
                }
            }
            foreach($remove as $id) {
                if (isset($goodOrExcellent[$id])) {
                    unset($goodOrExcellent[$id]);
                }
            }

            // Mention remaining options
            if (count($goodOrExcellent) == 1) {
                $option = reset($goodOrExcellent);
                $option->state = SuggestionOption::RANKING;
                $option->update();
                $postText .= '<p>The solution \'' . $option->summary . '\' won the vote and can be ranked now.</p>';
            } else {
                foreach($goodOrExcellent as $option) {
                    $postText .= '<p>The solution \'' . $option->summary . '\' reached a median above \'' .
                        $rankNames[2] . '\' and can be ranked now.</p>';
                    $option->state = SuggestionOption::RANKING;;
                    $option->update();
                }
                $postText .= '<p>All solutions not mentioned above (if any) were conflicting with a solution with a higher median.</p>';
            }
        }

        $suggestionsTeam = $entityFactory->create('Member')->findByUsername('SuggestionsTeam');
        if ($suggestionsTeam) {
            $suggestions = new SuggestionsModel();
            $postId = $suggestions->addPost($suggestionsTeam->id, $postText, $this->threadId);
            $suggestions->setForumNotification($postId, 'reply');
        }

        return true;
    }

    private function postVotingStartsMessage() {
        $entityFactory = new RoxEntityFactory();
        $suggestionsTeam = $entityFactory->create('Member')->findByUsername('SuggestionsTeam');
        $text = 'Voting for the suggestion \'<a href="/suggestions/' . $this->id . '/">' . $this->summary . '</a>\' will start on '
            . date('Y-m-d', strtotime($this->laststatechanged) + SuggestionsModel::DURATION_ADDOPTIONS) . '.<br /><br />Please have a look at the solutions and add your voice.';
        $suggestions = new SuggestionsModel();
        $postId = $suggestions->addPost($suggestionsTeam->id, $text, $this->threadId);
        $suggestions->setForumNotification($postId, 'reply');
    }

    private function postVotingEndsMessage() {
        $entityFactory = new RoxEntityFactory();
        $suggestionsTeam = $entityFactory->create('Member')->findByUsername('SuggestionsTeam');
        $text = 'Voting for the suggestion \'<a href="/suggestions/' . $this->id . '/">' . $this->summary . '</a>\' will end on '
            . date('Y-m-d', strtotime($this->laststatechanged) + SuggestionsModel::DURATION_VOTING) . '.<br /><br />if you haven\'t done so yet, please cast your vote.';
        $suggestions = new SuggestionsModel();
        $postId = $suggestions->addPost($suggestionsTeam->id, $text, $this->threadId);
        $suggestions->setForumNotification($postId, 'reply');
    }
}
