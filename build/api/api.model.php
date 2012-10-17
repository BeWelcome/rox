<?php

class ApiModel extends RoxModelBase
{
    public function __construct() {
        parent::__construct();
    }

    public function getMember($username) {
        $member = $this->createEntity('Member')->findByUsername($username);
        if ($member == false) {
            return false;
        } else {
            $languageId = 0;
            $occupation = $member->get_trad('Occupation', $languageId, true);
            $summary = $member->get_trad('ProfileSummary', $languageId, true);
            $numberOfComments = $member->count_comments();
            $memberData = array(
                'username' => $username,
                'firstname' => $member->firstname,
                'lastname' => $member->lastname,
                'age' => $member->age,
                'occupation' => $occupation,
                'summary' => $summary,
                'numberOfComments' => $numberOfComments,
            );
            return $memberData;
        }
    }

}

?>