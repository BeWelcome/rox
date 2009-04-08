<?php

$thumbPathMember = MOD_layoutbits::smallUserPic_userId($_SESSION['IdMember']);
//$imagePathMember = MOD_user::getImage();

$_newMessagesNumber = $this->model->getNewMessagesNumber($_SESSION['IdMember']);

if ($_newMessagesNumber > 0) {
    $_mainPageNewMessagesMessage = $words->getFormatted('MainPageNewMessages', $_newMessagesNumber);
}



$notify_widget = new NotifyMemberWidget_Personalstart;
$notify_widget->model = new NotifyModel;
$notify_widget->delegate = $this;  // delegate
$notify_widget->delegate_prefix = 'notes';
$notify_widget->items_per_page = 4;
$notify_widget->active_page = $this->active_page;
$notify_widget->visible_range = 2;

?>