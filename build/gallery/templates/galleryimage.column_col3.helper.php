<?php
$words = new MOD_words();
$request = PRequest::get()->request;
$gallery_ctrl = new GalleryController;
$image = $this->image;
if ($member = $this->model->getLoggedInMember())
{
    $callbackId = $gallery_ctrl->editProcess($image);
    $vars =& PPostHandler::getVars($callbackId);
}
$GalleryRight = MOD_right::get()->hasRight('Gallery');
$d = $image;
$d->user_handle = MOD_member::getUserHandle($d->user_id_foreign);
$canEdit = ($member && $member->Username == $d->user_handle) ? true : false;

if (!isset($vars['errors'])) {
    $vars['errors'] = array();
}
