<?php

$thumbPathMember = MOD_layoutbits::smallUserPic_userId($_SESSION['IdMember']);
//$imagePathMember = MOD_user::getImage();

$_newMessagesNumber = $this->model->getNewMessagesNumber($_SESSION['IdMember']);

if ($_newMessagesNumber > 0) {
    $_mainPageNewMessagesMessage = $words->getFormatted('MainPageNewMessages', $_newMessagesNumber);
}

?>