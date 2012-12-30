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
        $baseURL = PVars::getObj('env')->baseuri;
        $languageId = 0;
        $memberData = new stdClass;

        // field : username : string : mandatory
        $memberData->username = $member->Username;

        // field : numberOfComments : number : mandatory
        $commentCounts = $member->count_comments();
        $memberData->numberOfComments = intval($commentCounts['all']);

        // field : isPublic : boolean : mandatory
        $memberData->isPublic = $member->isPublic();

        // field : givenName : string : mandatory
        if ($member->firstname != '') {
            $memberData->givenName = $member->firstname;
        }

        // field : middleName : string : optional
        if ($member->secondname != '') {
            $memberData->middleName = $member->secondname;
        }

        // field : familyName : string : mandatory
        if ($member->lastname != '') {
            $memberData->familyName = $member->lastname;
        }

        // field : occupation : string : optional
        $occupation = $member->get_trad('Occupation', $languageId, true);
        if ($occupation != '') {
            $memberData->occupation = $occupation;
        }

        // address
        $address = new stdClass;

        // field : address.street : string : optional
        if ($member->street != '') {
            $address->street = $member->street;
        }

        // field : address.house : string : optional
        if ($member->housenumber != '') {
            $address->house = $member->housenumber;
        }

        // field : address.postcode : string : optional
        if ($member->zip != '') {
            $address->postcode = $member->zip;
        }

        // field : address : object : mandatory
        $memberData->address = $address;

        // field : location : object : mandatory
        $memberData->location = new stdClass;

        // field : location.city : string : mandatory
        $memberData->location->cityName = $member->city;

        // field : location.cityGeonamesId : number : mandatory
        $memberData->location->cityGeonamesId = intval($member->IdCity);

        // field : location.cityLatitude : number : mandatory
        // field : location.cityLongitude : number : mandatory
        $city = $member->createEntity('Geo')->findById($member->IdCity);
        if (gettype($city) == 'object') {
            $memberData->location->cityLatitude = floatVal($city->latitude);
            $memberData->location->cityLongitude = floatVal($city->longitude);
        } else {
            $memberData->location->cityLatitude = 0;
            $memberData->location->cityLongitude = 0;
        }

        // field : location.regionName : string : mandatory
        $memberData->location->regionName = $member->region;

        // field : location.countryName : string : mandatory
        $memberData->location->countryName = $member->country;

        // field : location.countryCode : string : mandatory : ISO 3166-1 alpha-2
        $memberData->location->countryCode = $member->countryCode;

        // field : age : number : optional
        if ($member->age != 'hidden') {
            $memberData->age = $member->age;
        }

        // field : gender : string : optional
        if ($member->Gender != '' && $member->Gender != 'IDontTell') {
            $memberData->gender = $member->Gender;
        }

        // field : summary : string : optional
        $summary = $member->get_trad('ProfileSummary', $languageId, true);
        if ($summary != '') {
            $memberData->summary = $summary;
        }

        // field : phones : object : mandatory
        $memberPhone = $member->phone;
        $memberData->phones = new stdClass;

        // field : phones.mobile : string : optional
        if (isset($memberPhone['CellPhoneNumber'])) {
            $memberData->phones->mobile = $memberPhone['CellPhoneNumber'];
        }

        // field : phones.home : string : optional
        if (isset($memberPhone['HomePhoneNumber'])) {
            $memberData->phones->home = $memberPhone['HomePhoneNumber'];
        }

        // field : phones.work : string : optional
        if (isset($memberPhone['WorkPhoneNumber'])) {
            $memberData->phones->work = $memberPhone['WorkPhoneNumber'];
        }

        // field : picture : object : mandatory
        // TODO: add image width and height (tricky for the full image)
        $avatarBase = $baseURL . 'avatar/' . $member->Username . '?';
        $memberData->picture = new stdClass;

        // field : picture.tiny : object : mandatory
        $memberData->picture->tiny = new stdClass;

        // field : picture.tiny.url : string : mandatory
        $memberData->picture->tiny->url = $avatarBase . '30_30';

        // field : picture.small : object : mandatory
        $memberData->picture->small = new stdClass;

        // field : picture.small.url : string : mandatory
        $memberData->picture->small->url = $avatarBase . 'xs';

        // field : picture.medium : object : mandatory
        $memberData->picture->medium = new stdClass;

        // field : picture.medium.url : string : mandatory
        $memberData->picture->medium->url = $avatarBase . '150';

        // field : picture.full : object : mandatory
        $memberData->picture->full = new stdClass;

        // field : picture.full.url : string : mandatory
        $memberData->picture->full->url = $avatarBase . '500';

        // field : languagesSpoken : array : mandatory
        // TODO: add locale code (e.g. en_GB) and level (native, beginner etc.)
        $languages = array();

        foreach ($member->languages_spoken as $language) {
            $languages[] = array(
                // field : languagesSpoken[].name : string : optional
                'name' => $language->Name
            );
        }
        $memberData->languagesSpoken = $languages;

        // field : website : string : optional
        if ($member->WebSite != '') {
            $memberData->website = $member->WebSite;
        }

        // field : chatContacts : object : mandatory
        $memberData->chatContacts = new stdClass;
        $chats = $member->get_messengers();

        // field : chatContacts.aol : string : optional
        // field : chatContacts.google : string : optional
        // field : chatContacts.icq : string : optional
        // field : chatContacts.msn : string : optional
        // field : chatContacts.other : string : optional
        // field : chatContacts.skype : string : optional
        // field : chatContacts.yahoo : string : optional
        foreach($chats as $chat) {
            if ($chat['address'] != '') {
                $network = strtolower($chat['network_raw']);
                $memberData->chatContacts->$network = $chat['address'];
            }
        }

        // accommodation
        $acc = new stdClass;

        // field : accommodation.offered : string : mandatory : Values can be yes/no/maybe
        if ($member->Accomodation == 'anytime') {
            $acc->offered = 'yes';
        } else if ($member->Accomodation == 'neverask') {
            $acc->offered = 'no';
        } else if ($member->Accomodation == 'dependonrequest') {
            $acc->offered = 'maybe';
        }

        // field : accommodation.numberOfGuests : number : mandatory
        $acc->numberOfGuests = intval($member->MaxGuest);

        // field : accommodation.lengthOfStay : string : optional
        $lengthOfStay =
            $member->get_trad('MaxLenghtOfStay', $languageId, true);
        if ($lengthOfStay != '') {
            $acc->lengthOfStay = $lengthOfStay;
        }

        // field : accommodation.livingWith : string : optional
        $livingWith =
            $member->get_trad('ILiveWith', $languageId, true);
        if ($livingWith != '') {
            $acc->livingWith = $livingWith;
        }

        // field : accommodation.pleaseBring : string : optional
        $pleaseBring =
            $member->get_trad('PleaseBring', $languageId, true);
        if ($pleaseBring != '') {
            $acc->pleaseBring = $pleaseBring;
        }

        // field : accommodation.offerForGuests : string : optional
        $offerForGuests =
            $member->get_trad('OfferGuests', $languageId, true);
        if ($offerForGuests != '') {
            $acc->offerForGuests = $offerForGuests;
        }

        // field : accommodation.offerForHosts : string : optional
        $offerForHosts =
            $member->get_trad('OfferHosts', $languageId, true);
        if ($offerForHosts != '') {
            $acc->offerForHosts = $offerForHosts;
        }

        // field : accommodation.publicTransport : string : optional
        $publicTransport =
            $member->get_trad('PublicTransport', $languageId, true);
        if ($publicTransport != '') {
            $acc->publicTransport = $publicTransport;
        }

        // field : accommodation.otherRestrictions : string : optional
        $otherRestrictions =
            $member->get_trad('OtherRestrictions', $languageId, true);
        if ($otherRestrictions != '') {
            $acc->otherRestrictions = $otherRestrictions;
        }

        // field : accommodation.additionalInfo : string : optional
        $additionalInfo =
            $member->get_trad('AdditionalAccomodationInfo', $languageId, true);
        if ($additionalInfo != '') {
            $acc->additionalInfo = $additionalInfo;
        }

        // prepare offers boolean fields
        $offers = explode(',', $member->TypicOffer);

        // field : accommodation.offersGuidedTour : boolean : mandatory
        if (in_array('guidedtour', $offers)) {
            $acc->offersGuidedTour = true;
        } else {
            $acc->offersGuidedTour = false;
        }

        // field : accommodation.offersDinner : boolean : mandatory
        if (in_array('dinner', $offers)) {
            $acc->offersDinner = true;
        } else {
            $acc->offersDinner = false;
        }

        // field : accommodation.wheelchairAccessible : boolean : mandatory
        if (in_array('CanHostWeelChair', $offers)) {
            $acc->wheelchairAccessible = true;
        } else {
            $acc->wheelchairAccessible = false;
        }

        // prepare restrictions boolean fields
        $restrictions = explode(',', $member->Restrictions);

        // field : accommodation.noSmoking : boolean : mandatory
        if (in_array('NoSmoker', $restrictions)) {
            $acc->noSmoking = true;
        } else {
            $acc->noSmoking = false;
        }

        // field : accommodation.noAlcohol : boolean : mandatory
        if (in_array('NoAlchool', $restrictions)) {
            $acc->noAlcohol = true;
        } else {
            $acc->noAlcohol = false;
        }

        // field : accommodation.noOtherDrugs : boolean : mandatory
        if (in_array('NoDrugs', $restrictions)) {
            $acc->noOtherDrugs = true;
        } else {
            $acc->noOtherDrugs = false;
        }

        // field : accommodation : object : optional
        $memberData->accommodation = $acc;

        // field : hobbies : string : optional
        $hobbies =
            $member->get_trad('Hobbies', $languageId, true);
        if ($hobbies != '') {
            $memberData->hobbies = $hobbies;
        }

        // field : favouriteBooks : string : optional
        $favouriteBooks =
            $member->get_trad('Books', $languageId, true);
        if ($favouriteBooks != '') {
            $memberData->favouriteBooks = $favouriteBooks;
        }

        // field : favouriteMusic : string : optional
        $favouriteMusic =
            $member->get_trad('Music', $languageId, true);
        if ($favouriteMusic != '') {
            $memberData->favouriteMusic = $favouriteMusic;
        }

        // field : favouriteFilms : string : optional
        $favouriteFilms =
            $member->get_trad('Movies', $languageId, true);
        if ($favouriteFilms != '') {
            $memberData->favouriteFilms = $favouriteFilms;
        }

        // field : organisations : string : optional
        $organisations =
            $member->get_trad('Organizations', $languageId, true);
        if ($organisations != '') {
            $memberData->organisations = $organisations;
        }

        // field : pastTrips : string : optional
        $pastTrips =
            $member->get_trad('PastTrips', $languageId, true);
        if ($pastTrips != '') {
            $memberData->pastTrips = $pastTrips;
        }

        // field : plannedTrips : string : optional
        $plannedTrips =
            $member->get_trad('PlannedTrips', $languageId, true);
        if ($plannedTrips != '') {
            $memberData->plannedTrips = $plannedTrips;
        }

        return $memberData;
    }

}

?>