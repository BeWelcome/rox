<?php
/**
 * suggestions model class.
 *
 * @author shevek
 */
class SuggestionsModel extends RoxModelBase
{
    const DURATION_DISCUSSION = 864000; // 10 days = 10 * 24 * 60 * 60
    const DURATION_ADDOPTIONS = 1728000; // 20 days = 20 * 24 * 60 * 60
    const DURATION_OPEN = 2592000; // DURATION_DISCUSSION + DURATION_ADDOPTIONS
    const DURATION_VOTING = 2592000; // 30 days = 30 * 24 * 60 * 60

    const SUGGESTIONS_DUPLICATE = 0; // suggestion already existed and was there marked as duplicate by suggestion team
    const SUGGESTIONS_AWAIT_APPROVAL = 1; // wait for suggestion team to check
    const SUGGESTIONS_DISCUSSION = 2; // discuss the suggestion try to find solutions
    const SUGGESTIONS_ADD_OPTIONS = 4; // enter solutions into the system (10 days after start)
    const SUGGESTIONS_VOTING = 8; // allow voting (30 days after switching to discussion mode)
    const SUGGESTIONS_RANKING = 16; // Voting finished (30 days after voting started). Ranking can be done now.
    const SUGGESTIONS_REJECTED = 32; // Suggestion didn't reach the necessary level of approval (at least 'good')
    const SUGGESTIONS_IMPLEMENTING = 64; // Dev started implementing (no more ranking)
    const SUGGESTIONS_IMPLEMENTED = 128; // Dev finished implementation
    const SUGGESTIONS_DEV = 192;

    private static $STATES = array(
                    self::SUGGESTIONS_DUPLICATE => 'SuggestionsDuplicate',
                    self::SUGGESTIONS_AWAIT_APPROVAL => 'SuggestionsAwaitApproval',
                    self::SUGGESTIONS_DISCUSSION =>  'SuggestionsDiscuss',
                    self::SUGGESTIONS_ADD_OPTIONS  => 'SuggestionsAddOptions',
                    self::SUGGESTIONS_VOTING => 'SuggestionsVoting',
                    self::SUGGESTIONS_RANKING => 'SuggestionsRanking',
                    self::SUGGESTIONS_IMPLEMENTING => 'SuggestionsImplementing',
                    self::SUGGESTIONS_IMPLEMENTED => 'SuggestionsImplemented',
                    self::SUGGESTIONS_REJECTED => 'SuggestionsRejected',
    );

    public static function getStatesByArray() {
        return self::$STATES;
    }


    const DESCRIPTION_MAX_LEN = 65000;
    const SUMMARY_MAX_LEN = 160;

    public function __construct() {
        parent::__construct();
        $this->groupId = $this->getGroupId();
    }

    public static function getGroupId() {
        $config = PVars::getObj('suggestions');
        if ($config->groupid) {
            return $config->groupid;
        } else {
            // hard coded group for bewelcome (hack)
            return 1654;
        }
    }

    private function getSuggestionsQueryWhere($type) {
        $query = '';
        switch($type) {
            case self::SUGGESTIONS_DISCUSSION:
                $query = "state = " . self::SUGGESTIONS_DISCUSSION
                . " OR state = " . self::SUGGESTIONS_ADD_OPTIONS;
                $sql_order = "created ASC";
                break;
            case self::SUGGESTIONS_REJECTED:
                $query = "state = " . self::SUGGESTIONS_REJECTED
                    . " OR state = " . self::SUGGESTIONS_DUPLICATE;
                $sql_order = "state DESC, created ASC";
                break;
            case self::SUGGESTIONS_DEV:
                $query = "state = " . self::SUGGESTIONS_IMPLEMENTED
                    . " OR state = " . self::SUGGESTIONS_IMPLEMENTING;
                $sql_order = "state ASC, created ASC";
                break;
            default:
                $query = "state = " . $type;
                $sql_order = "created ASC";
                break;
        }
        return array($query, $sql_order);
    }

    public function getSuggestionsCount($type) {
        if (!is_numeric($type) && !is_int($type)) {
            return -1;
        }
        list($where, $order) = $this->getSuggestionsQueryWhere($type);
        $query = "SELECT COUNT(*) FROM suggestions WHERE " . $where;
        $sql = $this->dao->query($query);
        if (!$sql) {
            return false;
        }
        $row = $sql->fetch(PDB::FETCH_NUM);
        return $row[0];
    }

    private function filterDiscussionAndAddOptions($var) {
        return ($var->state == self::SUGGESTIONS_DISCUSSION) || ($var->state == self::SUGGESTIONS_ADD_OPTIONS);
    }

    private function filterRejectedAndDuplicate($var) {
        return ($var->state == self::SUGGESTIONS_DUPLICATE) || ($var->state == self::SUGGESTIONS_REJECTED);
    }

    private function filterImplementedAndImplementing($var) {
        return ($var->state == self::SUGGESTIONS_IMPLEMENTING) || ($var->state == self::SUGGESTIONS_IMPLEMENTED);
    }

    private function filterSuggestionsVoting($var) {
        return ($var->state == self::SUGGESTIONS_VOTING);
    }

    public function getSuggestions($type, $pageno, $items) {
        if (!is_numeric($type) && !is_int($type)) {
            return false;
        }
        $temp = $this->CreateEntity('Suggestion');
        list($where, $order) = $this->getSuggestionsQueryWhere($type);
        $temp->sql_order = $order;
        $all = $temp->FindByWhereMany($where, $pageno * $items, $items);
        switch ($type) {
            case self::SUGGESTIONS_DISCUSSION:
                $filtered = array_filter($all, array($this, 'filterDiscussionAndAddOptions'));
                break;
            case self::SUGGESTIONS_REJECTED:
                $filtered = array_filter($all, array($this, 'filterRejectedAndDuplicate'));
                break;
            case self::SUGGESTIONS_DEV:
                $filtered = array_filter($all, array($this, 'filterImplementedAndImplementing'));
                break;
            case self::SUGGESTIONS_VOTING:
                $filtered = array_filter($all, array($this, 'filterVoting'));
                break;
            default:
                $filtered = $all;
                break;
        }

        return $filtered;
    }

    public function checkEditCreateSuggestionVarsOk($args) {
        $errors = array();
        $vars = $args->post;
        if (empty($vars['suggestion-summary'])) {
            $errors[] = 'SuggestionsSummaryEmpty';
        }
        if (strlen($vars['suggestion-summary']) > self::SUMMARY_MAX_LEN) {
            $errors[] = 'SuggestionsSummaryTooLong###' . self::SUMMARY_MAX_LEN . '###';
        }
        if (empty($vars['suggestion-description'])) {
            $errors[] = 'SuggestionsDescriptionEmpty';
        }
        if (strlen($vars['suggestion-description']) > self::DESCRIPTION_MAX_LEN) {
            $errors[] = 'SuggestionsDescriptionTooLong###' . self::DESCRIPTION_MAX_LEN . '###';
        }
        return $errors;
    }

    public function createSuggestion($args) {
        $suggestion = new Suggestion;
        $suggestion->summary = $args->post['suggestion-summary'];
        $suggestion->description = $args->post['suggestion-description'];
        $suggestion->state = self::SUGGESTIONS_AWAIT_APPROVAL;
        $suggestion->salt = hash_hmac('sha256', $suggestion->description, $suggestion->summary);
        $suggestion->created = date('Y-m-d');
        $suggestion->createdby = $this->getLoggedInMember()->id;
        $suggestion->insert();
        return $suggestion;
    }

    public function editSuggestion($args) {
        $suggestion = new Suggestion($args->post['suggestion-id']);
        $suggestion->summary = $args->post['suggestion-summary'];
        $suggestion->description = $args->post['suggestion-description'];
        $suggestion->modified = date('Y-m-d');
        $suggestion->modifiedby = $this->getLoggedInMember()->id;
        $suggestion->update();
        return $suggestion;
    }

    private function addPost($poster, $text, $threadId = false) {
        $words = $this->getWords();
        $suggestionsTeam = $this->createEntity('Member')->findByUsername('SuggestionsTeam');
        $insert = "
            INSERT INTO
                `forums_posts` (
                    `authorid`,
                    `create_time`,
                    `message`,
                    `IdWriter`,
                    `IdFirstLanguageUsed`, ";
        if ($threadId) {
            $insert .= "`threadid`, ";
        }
        $insert .= "`PostVisibility`)
            VALUES
                ('" . $this->dao->escape($poster) . "', NOW(), '" . $this->dao->escape($text) . "',
                    '" . $this->dao->escape($poster) ."', 0, ";
        if ($threadId) {
            $insert .= $threadId . ", ";
        }
        $insert .= "'MembersOnly')";

        $res = $this->dao->query($insert);
        if (!$res) {
            return false;
        }
        $postId = $res->insertId();

        // Still needed...
        $query="UPDATE `forums_posts` SET `id`=`postid` WHERE id=0" ;
        $result = $this->dao->query($query);

        $words->InsertInFTrad( $this->dao->escape($text), 'forums_posts.IdContent', $postId, $poster, -1, -1);
        return $postId;
    }

    public function approveSuggestion($id) {
        $suggestion = new Suggestion($id);
        $suggestion->state = self::SUGGESTIONS_DISCUSSION;

        $words = $this->getWords();
        $suggestionText = '<p>' . $words->getSilent('SuggestionThreadStart', '<a href="/suggestions/' . $suggestion->id . '/">', '</a>', strip_tags($suggestion->summary)) . '</p>';
        $suggestionText .= '<p>' . $suggestion->description . '</p>';
        $postId = $this->addPost($suggestion->createdby, $suggestionText);

        // Create a new thread in the suggestions group and add the id to the suggestion
        $title = $this->dao->escape(strip_tags($suggestion->summary));
        $insert = "
            INSERT INTO
                `forums_threads` (`title`, `first_postid`, `last_postid`, `geonameid`, `admincode`, `countrycode`, `continent`,`IdFirstLanguageUsed`,`IdGroup`,`ThreadVisibility`)
            VALUES
                ('" . $title . "', '" . $this->dao->escape($postId) . "', '" . $this->dao->escape($postId) . "',
                    NULL, NULL, NULL, NULL, 0, '" . $this->groupId . "', 'MembersOnly')";
        $res = $this->dao->query($insert);
        if (!$res) {
            return false;
        }
        $threadId = $res->insertId();

        // still needed...
        $query="UPDATE `forums_threads` SET `id`=`threadid` WHERE id=0" ;
        $result = $this->dao->query($query);

        $words->InsertInFTrad($title, "forums_threads.IdTitle", $threadId, $suggestion->createdby, -1, -1);
        $query = sprintf("UPDATE `forums_posts` SET `threadid` = '%d' WHERE `postid` = '%d'", $threadId, $postId);
        $result = $this->dao->query($query);

        $suggestion->approved = date('Y-m-d');
        $suggestion->threadId = $threadId;
        $suggestion->update(true);
    }

    public function markDuplicateSuggestion($id) {
        $suggestion = new Suggestion($id);
        $suggestion->state = self::SUGGESTIONS_DUPLICATE;
        $suggestion->modified = date('Y-m-d');
        $suggestion->modifiedby = $this->getLoggedInMember()->id;
        $suggestion->update(true);
    }

    public function checkAddOptionVarsOk($args) {
        $errors = array();
        $vars = $args->post;
        if (empty($vars['suggestion-option-summary'])) {
            $errors[] = 'SuggestionsOptionSummaryEmpty';
        }
        if (strlen($vars['suggestion-option-summary']) > self::SUMMARY_MAX_LEN) {
            $errors[] = 'SuggestionsOptionSummaryTooLong###' . self::SUMMARY_MAX_LEN . '###';
        }
        if (empty($vars['suggestion-option-desc'])) {
            $errors[] = 'SuggestionsOptionDescriptionEmpty';
        }
        if (strlen($vars['suggestion-option-desc']) > self::DESCRIPTION_MAX_LEN) {
            $errors[] = 'SuggestionsOptionDescriptionTooLong###' . self::DESCRIPTION_MAX_LEN . '###';
        }
        return $errors;
    }

    public function addOption($args) {
        $suggestion = new Suggestion($args->post['suggestion-id']);
        $suggestion->modified = date('Y-m-d');
        $suggestion->modifiedby = $this->getLoggedInMember()->id;

        $words = $this->getWords();
        $addOptionPostText = '<p>' . $words->getSilent('SuggestionOptionAdded', $args->post['suggestion-option-summary']) . '</p>';
        $addOptionPostText .= '<p>' . $args->post['suggestion-option-desc'] . '</p>';
        $postId = $this->addPost($suggestion->modifiedby, $addOptionPostText, $suggestion->threadId);

        $query = sprintf("UPDATE `forums_posts` SET `threadid` = '%d' WHERE `postid` = '%d'", $suggestion->threadId, $postId);
        $result = $this->dao->query($query);

        $suggestion->addOption($args->post['suggestion-option-summary'], $args->post['suggestion-option-desc']);
        $suggestion->update();

        return $suggestion;
    }

    public function editOption($args) {
        $suggestion = new Suggestion($args->post['suggestion-id']);
        $suggestion->modified = date('Y-m-d');
        $suggestion->modifiedby = $this->getLoggedInMember()->id;
        $suggestion->editOption($args->post['suggestion-option-id'], $args->post['suggestion-option-summary'], $args->post['suggestion-option-desc']);
        $suggestion->update();
        return $suggestion;
    }

    public function deleteOption($args) {
        $suggestion = new Suggestion($args->post['suggestion-id']);
        $suggestion->modified = date('Y-m-d');
        $suggestion->modifiedby = $this->getLoggedInMember()->id;
        $suggestion->deleteOption($args->post['suggestion-option-id']);
        $suggestion->update();
        return $suggestion;
    }

    public function restoreOption($optionId) {
        $option = new SuggestionOption($optionId);
        $option->modified = date('Y-m-d');
        $option->modifiedBy = $this->getLoggedInMember()->id;
        $option->deleted = null;
        $option->deletedBy = null;
        $option->update();
        return $option;
    }

    public function getVotesForLoggedInMember($suggestion) {
        $member = $this->getLoggedInMember();
        $hash = hash_hmac('sha256', $member->id, $suggestion->salt);
        $query = "SELECT * FROM suggestions_votes WHERE suggestionId = "
            . $suggestion->id . " AND memberHash = '" . $hash . "'";
        $sql = $this->dao->query($query);
        if (!$sql) {
            return false;
        }
        $votes = array();
        while($row = $sql->fetch(PDB::FETCH_OBJ)) {
            $votes[$row->optionId] = $row;
        }
        return $votes;
    }

    public function checkVoteSuggestion($vars) {
        return array();
    }

    private function filterOptions($var) {
        if (!is_string($var)) {
            return false;
        }
        $pos = strpos($var, 'option');
        if ($pos !== false) {
            return true;
        }
        return false;
    }

    public function voteForSuggestion($member, $args) {
        $optionKeys = array_filter(array_keys($args->post), array($this, 'filterOptions'));
        $suggestion = new Suggestion($args->post['suggestion-id']);
        $member = $this->getLoggedInMember();
        $hash = hash_hmac('sha256', $member->id, $suggestion->salt);

        // Initialize votes with 'poor'
        $votes = $this->getVotesForLoggedInMember($suggestion);
        if (empty($votes)) {
            foreach($suggestion->options as $option) {
                $vote = new StdClass;
                $vote->id = 0;
                $vote->suggestionId = $suggestion->id;
                $vote->optionId = $option->id;
                $vote->rank = 1;
                $vote->memberHash = $hash;
                $votes[$option->id] = $vote;
            }
        }

        foreach($optionKeys as $key) {
            $optionId = str_replace(array("option", "rank"), "", $key);
            $rank = $args->post[$key];
            $votes[$optionId]->rank = $rank;
        }

        foreach($votes as $vote) {
            if ($vote->id == 0) {
                $query = "INSERT INTO suggestions_votes SET suggestionId = " . $suggestion->id
                    . ", optionId = " . $vote->optionId . ", rank = " . $vote->rank
                    . ", memberHash = '" . $vote->memberHash . "'";
            } else {
                $query = "REPLACE INTO suggestions_votes SET id = " . $vote->id
                    . ", suggestionId = " . $vote->suggestionId . ", optionId = "
                    . $vote->optionId . ", rank = " . $vote->rank . ", memberHash = '" . $vote->memberHash . "'";
            }
            $this->dao->query($query);
        }

        return $suggestion;
    }

    public function checkChangeStateVarsOk($args) {
        $errors = array();
        return $errors;
    }

    public function changeState($args) {
        $suggestion = new Suggestion($args->post['suggestion-id']);
        $suggestion->state = $args->post['suggestion-state'];
        switch($suggestion->state) {
            case self::SUGGESTIONS_VOTING:
                $suggestion->votingstart = date('Y-m-d');
                $suggestion->votingend = date('Y-m-d', time() + 30 * 24 * 60 * 60);
                break;
        }
        $suggestion->update(true);
    }
}

?>