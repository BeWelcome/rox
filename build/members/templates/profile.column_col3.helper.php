<?php

$member = $this->member;
//print_r($this->model->get_profile_language());
//just to showcase the language selection method below while the
//profile language switch isn't ready for action 
//not sure if non-english profile should be shown as default in production
//$profile_language = $_SESSION['IdLanguage'];
$lang = $this->model->get_profile_language();
$profile_language = $lang->id;
$profile_language_code = $lang->ShortCode;
$words = $this->getWords();		
//$words->setLanguage('fr');

$messengers = $member->messengers();
$website = $member->WebSite;

$groups = $member->get_group_memberships();

// Prepare sections:
// -ProfileTravelExperience
$sections->ProfileTravelExperience = 
    $member->get_trad("PastTrips", $profile_language) +
    $member->get_trad("PlannedTrips", $profile_language)
    ;
// -ProfileInterests
$sections->ProfileInterests = 
    $member->get_trad("Hobbies", $profile_language) +
    $member->get_trad("Organizations", $profile_language)
    ;
// -ProfileGroups
$sections->ProfileGroups = 
    $groups;
    ;
// -ProfileInterests
$sections->ProfileInterests = 
    $member->get_trad("Hobbies", $profile_language) +
    $member->get_trad("Organizations", $profile_language)
    ;
?>
