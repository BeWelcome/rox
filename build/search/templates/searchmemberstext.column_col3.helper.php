<?php
/**
 * Created by PhpStorm.
 * User: raymund
 * Date: 19.01.14
 * Time: 18:39
 */

/* Make use of auto loading of the _helper file to actually get the advanced option helper methods */
include 'advancedoptions_helper.php';

function ShowAccommodation($accommodation)
{
    $words = new MOD_words($this->getSession());
    switch($accommodation) {
        case 'anytime':
            $imgSrc = 'images/icons/yesicanhost.png';
            break;
        case 'dependonrequest':
            $imgSrc = 'images/icons/maybe.png';
            break;
        case 'neverask':
            $imgSrc = 'images/icons/nosorry.png';
            break;
    }

    $altText = $words->getSilent('Accomodation_' . $accommodation);
    $imgTag = '<img src="' . $imgSrc . '" title="' . $altText . '" '
        . ' alt="' . $altText . '" />';
    return $imgTag;
}
?>