<?php
$words = new MOD_words();
$request = PRequest::get()->request;
$gallery_ctrl = new GalleryController;
$image = $this->image;
if ($this->model->getLoggedInMember())
{
    $callbackId = $gallery_ctrl->editProcess($image);
    $vars =& PPostHandler::getVars($callbackId);
}
$GalleryRight = MOD_right::get()->hasRight('Gallery');
$d = $image;
$d->user_handle = MOD_member::getUsername($d->user_id_foreign);
$canEdit = ($User && $User->getId() == $d->user_id_foreign) ? true : false;

if (!isset($vars['errors'])) {
    $vars['errors'] = array();
}
