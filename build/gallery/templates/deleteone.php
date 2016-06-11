<?php
if ($this->model->getLoggedInMember())
{
    $Gallery = new GalleryController;
    $callbackId = $Gallery->editProcess();
    $vars =& PPostHandler::getVars($callbackId);
}
$words = new MOD_words();

$d = $image;

if ($deleted)
{ 
    echo <<<HTML
    <p class="note"><img src="images/misc/check.gif">&nbsp; &nbsp; {$words->getFormatted('GalleryImageDeleted')}: <i>{$d->title}</i></p>
HTML;
}
else
{
    echo <<<HTML
    <p class="warning"><img src="images/misc/checkfalse.gif">&nbsp; &nbsp; {$words->getFormatted('GalleryImageNotDeleted')}: <i>{$d->title}</i></p>
HTML;
}
