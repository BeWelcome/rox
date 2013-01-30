<?php
$words = new MOD_words();
$thumbPathMember = MOD_layoutbits::PIC_30_30($_SESSION['Username'], '',$style='float_left framed');
//$imagePathMember = MOD_user::getImage();

$_newMessagesNumber = $this->model->getNewMessagesNumber($_SESSION['IdMember']);

if ($_newMessagesNumber > 0) {
    $_mainPageNewMessagesMessage = $words->getFormatted('MainPageNewMessages', $_newMessagesNumber);
}

$LayoutBits = new MOD_layoutbits();
$ShowDonateBar = $LayoutBits->getParams('ToggleDonateBar');

$notify_widget = new NotifyMemberWidget;
$notify_widget->model = new NotifyModel;
$notify_widget->items_per_page = 4;

$inbox_widget = new MailboxWidget_Personalstart;
$inbox_widget->model = new MessagesModel;
$inbox_widget->items_per_page = 4;

$Trip = new Trip;
$TripcallbackId = $Trip->createProcess();
$editing = false;

$Places = new Places;
$Countries = $Places->getAllCountries();

?>