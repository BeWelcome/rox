<?php
$member = $this->member;
$lang = $this->model->get_profile_language();
$profile_language = $lang->id;
$profile_language_code = $lang->ShortCode;
$myself = $this->myself;
$words = $this->getWords();
?>