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
    <p class="note"><i class="fa fa-check"></i>&nbsp; &nbsp; {$words->getFormatted('GalleryImageDeleted')}: <i>{$d->title}</i></p>
HTML;
}
else
{
    echo <<<HTML
    <p class="warning"><i class="fa fa-cancel"></i>&nbsp; &nbsp; {$words->getFormatted('GalleryImageNotDeleted')}: <i>{$d->title}</i></p>
HTML;
}
