<?php
/**
 * API model class.
 *
 * @author Meinhard Benn <meinhard@bewelcome.org>
 */
class ApiModel extends RoxModelBase
{
    /**
     * Default constructor.
     */
    public function __construct() {
        parent::__construct();
    }

    /**
     * Get active member by username.
     *
     * @param string $username Username of member.
     * @return Member|false Member entity object or false if member not found
     *                      or not 'Active'.
     */
    public function getMember($username) {
        $member = $this->createEntity('Member')->findByUsername($username);
        if ($member != false && $member->Status == 'Active') {
            return $member;
        }
        return false;
    }

    /**
     * Get all data for a member.
     *
     * This method is a huge monolith, but should be seen as sort of an
     * export filter and sanitiser.
     *
     * Each data field has a documentation comment of this format:
     *   field : <fieldName> : <fieldType> : <occurrance> : <comment>
     *
     * These comments will be used later to render the API documentation.
     *
     * fieldType: Matches the type in JavaScript, so for example "number" for
     * both integer and float.
     *
     * occurance: Can be either "always" or "optional". Fields labelled as
     * "always" will be included in each response, "optional" fields will only
     * be included if they are visible and have content.
     *
     * @param Member $member Member entity object.
     * @return object Object containing member data as properties.
     */
    public static function getMemberData(Member $member) {
        // TODO: avoid translation links in ago() when in translate mode
        // TODO: allow viewing of profile translations
        $baseURL = PVars::getObj('env')->baseuri;
        $languageId = 0;
        $memberData = new stdClass;

        // field : username : string : always
        $memberData->username = $member->Username;

        // field : isPublic : boolean : always
        $memberData->isPublic = $member->isPublic();

        // field : givenName : string : optional
        if ($member->firstname != '') {
            $memberData->givenName = $member->firstname;
        }

        // field : middleName : string : optional
        if ($member->secondname != '') {
            $memberData->middleName = $member->secondname;
        }

        // field : familyName : string : optional
        if ($member->lastname != '') {
            $memberData->familyName = $member->lastname;
        }

        // field : profileURL : string : always
        $memberData->profileURL = $baseURL . 'members/' . $member->Username;

        // field : signUpDate : string : always : Format: YYYY-MM-DD
        $memberData->signUpDate = date('Y-m-d', strtotime($member->created));

        // field : lastLogin : string : optional : Format: YYYY-MM-DD hh:mm:ss
        // TODO: make disclosure configurable in user prefs
        $memberData->lastLogin = $member->LastLogin;

        // field : lastLoginTimestamp : number : optional : Unix timestamp
        $memberData->lastLoginTimestamp = strtotime($member->LastLogin);

        // field : lastLoginFuzzy : string : optional : Fuzzy time like "3 hours ago"
        $memberData->lastLoginFuzzy =
            MOD_layoutbits::ago(strtotime($member->LastLogin));

        // field : numberOfComments : number : optional
        $commentCounts = $member->count_comments();
        $memberData->numberOfComments = intval($commentCounts['all']);

        // field : numberOfContacts : number : optional
        $memberData->numberOfContacts = count($member->relations);

        // field : numberOfImages : number : optional
        $memberData->numberOfImages = $member->getGalleryItemsCount();

        // field : numberOfForumPosts : number : optional
        $memberData->numberOfForumPosts = $member->forums_posts_count();

        // field : numberOfBlogPosts : number : optional
        $blogModel = new Blog();
        $blogPosts = $blogModel->getRecentPostIt($member->id);
        $memberData->numberOfBlogPosts = $blogPosts->numRows();

        // field : numberOfTrips : number : optional
        $tripsData = $member->getTripsArray();
        $memberData->numberOfTrips = count($tripsData[1]);

        // field : numberOfGroupMemberships : number : optional
        $memberData->numberOfGroupMemberships = count($member->getGroups());

        // field : occupation : string : optional
        $occupation = $member->get_trad('Occupation', $languageId, true);
        if ($occupation != '') {
            $memberData->occupation = $occupation;
        }

        // field : address : object : always
        $memberData->address = new stdClass;

        // field : address.street : string : optional
        if ($member->street != '') {
            $memberData->address->street = $member->street;
        }

        // field : address.house : string : optional
        if ($member->housenumber != '') {
            $memberData->address->house = $member->housenumber;
        }

        // field : address.postcode : string : optional
        if ($member->zip != '') {
            $memberData->address->postcode = $member->zip;
        }

        // field : location : object : always
        $memberData->location = new stdClass;

        // field : location.city : string : always
        $memberData->location->cityName = $member->city;

        // field : location.cityGeonamesId : number : always
        $memberData->location->cityGeonamesId = intval($member->IdCity);

        // field : location.cityLatitude : number : always
        // field : location.cityLongitude : number : always
        $city = $member->createEntity('Geo')->findById($member->IdCity);
        if (gettype($city) == 'object') {
            $memberData->location->cityLatitude = floatVal($city->latitude);
            $memberData->location->cityLongitude = floatVal($city->longitude);
        } else {
            $memberData->location->cityLatitude = 0;
            $memberData->location->cityLongitude = 0;
        }

        // field : location.regionName : string : always
        $memberData->location->regionName = $member->region;

        // field : location.countryName : string : always
        $memberData->location->countryName = $member->country;

        // field : location.countryCode : string : always : ISO 3166-1 alpha-2
        $memberData->location->countryCode = $member->countryCode;

        // field : age : number : optional
        if ($member->age != 'hidden') {
            $memberData->age = $member->age;
        }

        // field : gender : string : optional : Values can be female/male/other
        if ($member->Gender != '' && $member->HideGender == 'No') {
            if ($member->Gender == 'female') {
                $memberData->gender = 'female';
            } else if ($member->Gender == 'male') {
                $memberData->gender = 'male';
            } else {
                $memberData->gender = 'other';
            }
        }

        // field : summary : string : optional
        $summary = $member->get_trad('ProfileSummary', $languageId, true);
        if ($summary != '') {
            $memberData->summary = $summary;
        }

        // field : phones : object : always
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

        // field : picture : object : always
        // TODO: add image width and height (tricky for the full image)
        $avatarBase = $baseURL . 'members/avatar/' . $member->Username . '?';
        $memberData->picture = new stdClass;

        // field : picture.tiny : object : always
        $memberData->picture->tiny = new stdClass;

        // field : picture.tiny.url : string : always
        $memberData->picture->tiny->url = $avatarBase . '30_30';

        // field : picture.small : object : always
        $memberData->picture->small = new stdClass;

        // field : picture.small.url : string : always
        $memberData->picture->small->url = $avatarBase . 'xs';

        // field : picture.medium : object : always
        $memberData->picture->medium = new stdClass;

        // field : picture.medium.url : string : always
        $memberData->picture->medium->url = $avatarBase . '150';

        // field : picture.full : object : always
        $memberData->picture->full = new stdClass;

        // field : picture.full.url : string : always
        $memberData->picture->full->url = $avatarBase . '500';

        // field : languagesSpoken : array : always
        // TODO: add ISO 639-1 code (e.g. "en", needs to be added to database)
        //       and level (native/beginner/..)
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

        // field : chatContacts : object : always
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

        // accommodation, using temporary short variable name
        $acc = new stdClass;

        // field : accommodation.offered : string : always : Values can be yes/no/maybe
        if ($member->Accomodation == 'anytime') {
            $acc->offered = 'yes';
        } else if ($member->Accomodation == 'neverask') {
            $acc->offered = 'no';
        } else if ($member->Accomodation == 'dependonrequest') {
            $acc->offered = 'maybe';
        }

        // field : accommodation.numberOfGuests : number : always
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

        // field : accommodation.offersGuidedTour : boolean : always
        if (in_array('guidedtour', $offers)) {
            $acc->offersGuidedTour = true;
        } else {
            $acc->offersGuidedTour = false;
        }

        // field : accommodation.offersDinner : boolean : always
        if (in_array('dinner', $offers)) {
            $acc->offersDinner = true;
        } else {
            $acc->offersDinner = false;
        }

        // field : accommodation.wheelchairAccessible : boolean : always
        if (in_array('CanHostWeelChair', $offers)) {
            $acc->wheelchairAccessible = true;
        } else {
            $acc->wheelchairAccessible = false;
        }

        // prepare restrictions boolean fields
        $restrictions = explode(',', $member->Restrictions);

        // field : accommodation.noSmoking : boolean : always
        if (in_array('NoSmoker', $restrictions)) {
            $acc->noSmoking = true;
        } else {
            $acc->noSmoking = false;
        }

        // field : accommodation.noAlcohol : boolean : always
        if (in_array('NoAlchool', $restrictions)) {
            $acc->noAlcohol = true;
        } else {
            $acc->noAlcohol = false;
        }

        // field : accommodation.noOtherDrugs : boolean : always
        if (in_array('NoDrugs', $restrictions)) {
            $acc->noOtherDrugs = true;
        } else {
            $acc->noOtherDrugs = false;
        }

        // field : accommodation : object : always
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
