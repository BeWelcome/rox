<?php
$member = $this->member;

$mynotes = $this->mynotes;

$lang = $this->model->get_profile_language();
$profile_language = $lang->id;
$profile_language_code = $lang->ShortCode;
$profile_language_name = $lang->Name;
$words = $this->getWords();

$layoutbits = new MOD_layoutbits;
$right = new MOD_right(); 
?>
