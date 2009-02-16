<?php

$member = $this->member;

$lang = $this->model->get_profile_language();
$profile_language = $lang->id;
$profile_language_code = $lang->ShortCode;

$words = $this->getWords();
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
$languages = $member->get_profile_languages(); 
$occupation = $member->get_trad("Occupation", $profile_language);        

?>
