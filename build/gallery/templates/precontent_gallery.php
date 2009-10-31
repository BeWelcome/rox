<?php
/* 
//  This is the output above a user's gallery, the title so to say
// @author: lupochen
*/
$User = APP_User::login();
$words = new MOD_words();

$g = $gallery;
$g->user_handle = MOD_member::getUsername($g->user_id_foreign);
