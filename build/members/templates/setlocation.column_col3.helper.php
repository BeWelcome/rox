<?php
// Overwrite SetLocation-Geo-Info with GeoVars-Session (used for non-js users), afterwards unset it again.
$callback_tag = $this->layoutkit->formkit->setPostCallback('MembersController', 'setLocationCallback');
$member = $this->member;
$vars = $this->vars;
$words = $this->getWords();
?>