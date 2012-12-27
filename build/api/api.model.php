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
            return $member;
        }
    }

    public static function getMemberData($member) {
        $memberData = new stdClass;
        $languageId = 0;
        $occupation = $member->get_trad('Occupation', $languageId, true);
        $summary = $member->get_trad('ProfileSummary', $languageId, true);
        $commentCounts = $member->count_comments();
        $memberPhone = $member->phone;
        $phones = new stdClass;
        if (isset($memberPhone['CellPhoneNumber'])) {
            $phones->mobile = $memberPhone['CellPhoneNumber'];
        }
        if (isset($memberPhone['HomePhoneNumber'])) {
            $phones->home = $memberPhone['HomePhoneNumber'];
        }
        if (isset($memberPhone['WorkPhoneNumber'])) {
            $phones->work = $memberPhone['WorkPhoneNumber'];
        }
        $memberData->username = $member->Username;
        $memberData->numberOfComments = $commentCounts['all'];
        $memberData->isPublic = $member->isPublic();
        if ($member->firstname != '') {
            $memberData->givenName = $member->firstname;
        }
        if ($member->secondname != '') {
            $memberData->middleName = $member->secondname;
        }
        if ($member->lastname != '') {
            $memberData->familyName = $member->lastname;
        }
        if ($occupation != '') {
            $memberData->occupation = $occupation;
        }
        if ($member->street != '') {
            $memberData->addressStreet = $member->street;
        }
        if ($member->housenumber != '') {
            $memberData->addressHouse = $member->housenumber;
        }
        if ($member->zip != '') {
            $memberData->addressPostcode = $member->zip;
        }
        if ($member->age != 'hidden') {
            $memberData->age = $member->age;
        }
        if ($member->Gender != '' && $member->Gender != 'IDontTell') {
            $memberData->gender = $member->Gender;
        }
        if ($summary != '') {
            $memberData->summary = $summary;
        }
        $memberData->phones = $phones;
        return $memberData;
    }

}

?>