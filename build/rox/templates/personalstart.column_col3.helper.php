<?php

// echo '<h3>Your messages</h3>';
/*
$inbox_widget = new MailboxWidget_Personalstart;
$inbox_widget->model = new MessagesModel;
$inbox_widget->items_per_page = 4;
*/
// $inbox_widget->render();
// echo '<a href="bw/mymessages.php">more...</a>';

$Forums = new ForumsController;
$citylatlong = $this->model->getAllCityLatLong();
$google_conf = PVars::getObj('config_google');

?>