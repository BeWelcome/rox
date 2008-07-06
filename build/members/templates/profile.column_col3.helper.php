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

?>
