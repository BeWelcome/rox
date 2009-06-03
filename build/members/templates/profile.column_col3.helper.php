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
$profile_language_name = $lang->Name;
$words = $this->getWords();		
//$words->setLanguage('fr');

$ww = $this->ww;
$wwsilent = $this->wwsilent;
$comments_count = $member->count_comments(); 

$layoutbits = new MOD_layoutbits;
$right = new MOD_right();

$agestr = "";
if ($member->age == "hidden") {
    $agestr .= $ww->AgeHidden;
} else {
    $agestr= $ww->AgeEqualX($layoutbits->fage_value($member->BirthDate));
}

$messengers = $member->messengers();
$website = $member->WebSite;

$groups = $member->get_group_memberships();

$languages = $member->get_profile_languages(); 
$occupation = $member->get_trad("Occupation", $profile_language);        

// Prepare sections:
// -ProfileTravelExperience
$sections->ProfileTravelExperience = 
    $member->get_trad("PastTrips", $profile_language) +
    $member->get_trad("PlannedTrips", $profile_language)
    ;
// -ProfileInterests
    $hobbies = $member->get_trad("Hobbies", $profile_language);
    $orgas = $member->get_trad("Organizations", $profile_language);
$sections->ProfileInterests = 
    $orgas
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
