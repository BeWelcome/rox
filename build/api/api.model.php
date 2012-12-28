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
        // TODO: * location longitude/latitude
        $baseURL = PVars::getObj('env')->baseuri;
        $languageId = 0;
        $memberData = new stdClass;

        // field : username : string
        $memberData->username = $member->Username;

        // field : numberOfComments : number
        $commentCounts = $member->count_comments();
        $memberData->numberOfComments = intval($commentCounts['all']);

        // field : isPublic : boolean
        $memberData->isPublic = $member->isPublic();

        // field : givenName : string
        if ($member->firstname != '') {
            $memberData->givenName = $member->firstname;
        }

        // field : middleName : string
        if ($member->secondname != '') {
            $memberData->middleName = $member->secondname;
        }

        // field : familyName : string
        if ($member->lastname != '') {
            $memberData->familyName = $member->lastname;
        }

        // field : occupation : string
        $occupation = $member->get_trad('Occupation', $languageId, true);
        if ($occupation != '') {
            $memberData->occupation = $occupation;
        }

        // address
        $address = new stdClass;

        // field : address.street : string
        if ($member->street != '') {
            $address->street = $member->street;
        }

        // field : address.house : string
        if ($member->housenumber != '') {
            $address->house = $member->housenumber;
        }

        // field : address.postcode : string
        if ($member->zip != '') {
            $address->postcode = $member->zip;
        }

        // field : address : object
        $memberData->address = $address;

        // field : age : number
        if ($member->age != 'hidden') {
            $memberData->age = $member->age;
        }

        // field : gender : string
        if ($member->Gender != '' && $member->Gender != 'IDontTell') {
            $memberData->gender = $member->Gender;
        }

        // field : summary : string
        $summary = $member->get_trad('ProfileSummary', $languageId, true);
        if ($summary != '') {
            $memberData->summary = $summary;
        }

        // field : phones : object
        $memberPhone = $member->phone;
        $memberData->phones = new stdClass;

        // field : phones.mobile : string
        if (isset($memberPhone['CellPhoneNumber'])) {
            $memberData->phones->mobile = $memberPhone['CellPhoneNumber'];
        }

        // field : phones.home : string
        if (isset($memberPhone['HomePhoneNumber'])) {
            $memberData->phones->home = $memberPhone['HomePhoneNumber'];
        }

        // field : phones.work : string
        if (isset($memberPhone['WorkPhoneNumber'])) {
            $memberData->phones->work = $memberPhone['WorkPhoneNumber'];
        }

        // field : picture : object
        // TODO: add image width and height (tricky for the full image)
        $avatarBase = $baseURL . 'avatar/' . $member->Username . '?';
        $memberData->picture = new stdClass;

        // field : picture.tiny : object
        $memberData->picture->tiny = new stdClass;

        // field : picture.tiny.url : string
        $memberData->picture->tiny->url = $avatarBase . '30_30';

        // field : picture.small : object
        $memberData->picture->small = new stdClass;

        // field : picture.small.url : string
        $memberData->picture->small->url = $avatarBase . 'xs';

        // field : picture.medium : object
        $memberData->picture->medium = new stdClass;

        // field : picture.medium.url : string
        $memberData->picture->medium->url = $avatarBase . '150';

        // field : picture.full : object
        $memberData->picture->full = new stdClass;

        // field : picture.full.url : string
        $memberData->picture->full->url = $avatarBase . '500';

        // field : languagesSpoken : array
        // TODO: add locale code (e.g. en_GB) and level (native, beginner etc.)
        $languages = array();

        foreach ($member->languages_spoken as $language) {
            $languages[] = array(
                // field : languagesSpoken[].name : string
                'name' => $language->Name
            );
        }
        $memberData->languagesSpoken = $languages;

        // field : website : string
        if ($member->WebSite != '') {
            $memberData->website = $member->WebSite;
        }

        // field : chatContacts : object
        $memberData->chatContacts = new stdClass;
        $chats = $member->get_messengers();

        // field : chatContacts.aol : string
        // field : chatContacts.google : string
        // field : chatContacts.icq : string
        // field : chatContacts.msn : string
        // field : chatContacts.other : string
        // field : chatContacts.skype : string
        // field : chatContacts.yahoo : string
        foreach($chats as $chat) {
            if ($chat['address'] != '') {
                $network = strtolower($chat['network_raw']);
                $memberData->chatContacts->$network = $chat['address'];
            }
        }

        return $memberData;
    }

}

?>