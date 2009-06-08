<?php

$member = $this->member;

$my_groups = $this->my_groups;

$lang = $this->model->get_profile_language();
$profile_language = $lang->id;
$profile_language_code = $lang->ShortCode;
$profile_language_name = $lang->Name;
$words = $this->getWords();		
//$words->setLanguage('fr');

$ww = $this->ww;
$wwsilent = $this->wwsilent;

$layoutbits = new MOD_layoutbits;
$right = new MOD_right(); 

?>
