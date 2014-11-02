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

$verification_status = $member->verification_status;
if ($verification_status) $verification_text = $words->getSilent('verifymembers_'.$verification_status);

$agestr = "";
if ($member->age == "hidden") {
    $agestr .= $ww->AgeHidden;
} else {
    if ($this->passedAway) {
        $agestr= $ww->AgeEqualX($layoutbits->fage_value($member->BirthDate, substr($member->updated, 0, 10)));
    } else {
        $agestr= $ww->AgeEqualX($layoutbits->fage_value($member->BirthDate));
    }
}

$occupation = $member->get_trad("Occupation", $profile_language,true);
