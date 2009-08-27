<?php
$words = new MOD_words();
$User = APP_User::login();
$request = PRequest::get()->request;
$Gallery = new Gallery;
$image = $this->image;
if ($User) {
    $callbackId = $Gallery->editProcess($image);
    $vars =& PPostHandler::getVars($callbackId);
}
$GalleryRight = MOD_right::get()->hasRight('Gallery');
$d = $image;
$d->user_handle = MOD_member::getUsername($d->user_id_foreign);
$canEdit = ($User && $User->getId() == $d->user_id_foreign) ? true : false;

if (!isset($vars['errors'])) {
    $vars['errors'] = array();
}