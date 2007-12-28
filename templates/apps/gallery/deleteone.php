<?php
$User = APP_User::login();
if ($User) {
    $Gallery = new Gallery;
    $callbackId = $Gallery->editProcess();
    $vars =& PPostHandler::getVars($callbackId);
}
$imgText = array();
$i18n = new MOD_i18n('apps/gallery/image.php');
$imgText = $i18n->getText('imgText');

$d = $image;
echo '
<h2>'.$d->title.'</h2>
<p>Image deleted</p>
';