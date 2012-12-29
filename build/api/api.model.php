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

        // accommodation
        $acc = new stdClass;

        // field : accommodation.offered : string : yes/no/maybe
        if ($member->Accomodation == 'anytime') {
            $acc->offered = 'yes';
        } else if ($member->Accomodation == 'neverask') {
            $acc->offered = 'no';
        } else if ($member->Accomodation == 'dependonrequest') {
            $acc->offered = 'maybe';
        }

        // field : accommodation.numberOfGuests : number
        $acc->numberOfGuests = intval($member->MaxGuest);

        // field : accommodation.lengthOfStay : string
        $lengthOfStay =
            $member->get_trad('MaxLenghtOfStay', $languageId, true);
        if ($lengthOfStay != '') {
            $acc->lengthOfStay = $lengthOfStay;
        }

        // field : accommodation.livingWith : string
        $livingWith =
            $member->get_trad('ILiveWith', $languageId, true);
        if ($livingWith != '') {
            $acc->livingWith = $livingWith;
        }

        // field : accommodation.pleaseBring : string
        $pleaseBring =
            $member->get_trad('PleaseBring', $languageId, true);
        if ($pleaseBring != '') {
            $acc->pleaseBring = $pleaseBring;
        }

        // field : accommodation.offerForGuests : string
        $offerForGuests =
            $member->get_trad('OfferGuests', $languageId, true);
        if ($offerForGuests != '') {
            $acc->offerForGuests = $offerForGuests;
        }

        // field : accommodation.offerForHosts : string
        $offerForHosts =
            $member->get_trad('OfferHosts', $languageId, true);
        if ($offerForHosts != '') {
            $acc->offerForHosts = $offerForHosts;
        }

        // field : accommodation.publicTransport : string
        $publicTransport =
            $member->get_trad('PublicTransport', $languageId, true);
        if ($publicTransport != '') {
            $acc->publicTransport = $publicTransport;
        }

        // field : accommodation.otherRestrictions : string
        $otherRestrictions =
            $member->get_trad('OtherRestrictions', $languageId, true);
        if ($otherRestrictions != '') {
            $acc->otherRestrictions = $otherRestrictions;
        }

        // field : accommodation.additionalInfo : string
        $additionalInfo =
            $member->get_trad('AdditionalAccomodationInfo', $languageId, true);
        if ($additionalInfo != '') {
            $acc->additionalInfo = $additionalInfo;
        }

        // prepare offers boolean fields
        $offers = explode(',', $member->TypicOffer);

        // field : accommodation.offersGuidedTour : boolean
        if (in_array('guidedtour', $offers)) {
            $acc->offersGuidedTour = true;
        } else {
            $acc->offersGuidedTour = false;
        }

        // field : accommodation.offersDinner : boolean
        if (in_array('dinner', $offers)) {
            $acc->offersDinner = true;
        } else {
            $acc->offersDinner = false;
        }

        // field : accommodation.wheelchairAccessible : boolean
        if (in_array('CanHostWeelChair', $offers)) {
            $acc->wheelchairAccessible = true;
        } else {
            $acc->wheelchairAccessible = false;
        }

        // prepare restrictions boolean fields
        $restrictions = explode(',', $member->Restrictions);

        // field : accommodation.noSmoking : boolean
        if (in_array('NoSmoker', $restrictions)) {
            $acc->noSmoking = true;
        } else {
            $acc->noSmoking = false;
        }

        // field : accommodation.noAlcohol : boolean
        if (in_array('NoAlchool', $restrictions)) {
            $acc->noAlcohol = true;
        } else {
            $acc->noAlcohol = false;
        }

        // field : accommodation.noOtherDrugs : boolean
        if (in_array('NoDrugs', $restrictions)) {
            $acc->noOtherDrugs = true;
        } else {
            $acc->noOtherDrugs = false;
        }

        // field : accommodation : object
        $memberData->accommodation = $acc;

        return $memberData;
    }

}

?>