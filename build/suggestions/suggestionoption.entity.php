<?php

/**
 * represents a single decision
 *
 */
class SuggestionOption extends RoxEntityBase
{
    const RANKING = 1;
    const IMPLENENTING = 2;
    const IMPLEMENTED = 4;

    public static function getStatesAsArray($lang = 'en') {
        $words = new MOD_words();
        return array(
            self::RANKING => $words->getBufferedInLang('SuggestionsOptionRanking', $lang),
            self::IMPLENENTING => $words->getBufferedInLang('SuggestionsOptionImplementing', $lang),
            self::IMPLEMENTED => $words->getBufferedInLang('SuggestionsOptionImplemented', $lang)
        );
    }

    protected $_table_name = 'suggestions_options';

    public function __construct($optionId = false)
    {
        parent::__construct();
        if ($optionId)
        {
            $this->findById($optionId);
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
            $this->creator = $entityFactory->create('Member', $this->createdBy);
            if ($this->modifiedBy) {
                $this->modifier = $entityFactory->create('Member', $this->modifiedBy);
            }
            if ($this->deletedBy) {
                $this->deleter = $entityFactory->create('Member', $this->deletedBy);
            }
            if ($this->mutuallyExclusiveWith) {
                if ($this->mutuallyExclusiveWith == 'None') {
                    $this->mutuallyExclusive = array();
                } else {
                    $this->mutuallyExclusive = explode(',',$this->mutuallyExclusiveWith);
                }
            } else {
                $this->mutuallyExclusive = 'All';
            }

            // fetch rank votes for this option
            $query = "
                SELECT
                    SUM(vote) as sumVotes
                FROM
                    suggestions_option_ranks
                WHERE
                    optionid = " . $this->id . "
                GROUP BY
                    optionid
                ";

            $this->rankVotes = 0;
            $sql = $this->dao->query($query);
            if ($sql) {
                $row = $sql->fetch(PDB::FETCH_OBJ);
                if ($row) {
                    $this->rankVotes = $row->sumVotes;
                }
            }

            // if member already ranked on this option get that as well
            $member = $this->getLoggedInMember();
            if ($member) {
                $hash = hash_hmac('sha256', $member->id, $this->id);
                $query = "SELECT vote FROM suggestions_option_ranks WHERE optionid = "
                    . $this->id . " AND memberHash = '" . $hash . "'";
                $sql = $this->dao->query($query);
                if ($sql) {
                    $row = $sql->fetch(PDB::FETCH_OBJ);
                    if ($row) {
                        $this->vote = $row->vote;
                    }
                }
            }
        }
        return $status;
    }
}