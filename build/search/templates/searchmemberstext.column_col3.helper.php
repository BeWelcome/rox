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
    $words = new MOD_words();
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

function GetOfferIcons($offer)
{
    $words = new MOD_words();
    $icons = '';
    if (strstr($offer, "CanHostWeelChair")) {
        $icons .= '<img src="images/icons/wheelchairblue.png" width="22" height="22" alt="'
            . $words->getSilent('TypicOffer_CanHostWheelChair') . '" title="'
            . $words->getSilent('TypicOffer_CanHostWheelChair') . '" />';
    }
    if (strstr($offer, "dinner")) {
        $icons .= '<img src="images/icons/dinner.png" width="22" height="22" alt="'
            . $words->getSilent('TypicOffer_dinner') . '" title="'
            . $words->getSilent('TypicOffer_dinner') . '" />';
    }
    if (strstr($offer, "guidedtour")) {
        $icons .= '<img src="images/icons/guidedtour.png" width="22" height="22" alt="'
            . $words->getSilent('TypicOffer_guidedtour') . '" title="'
            . $words->getSilent('TypicOffer_guidedtour') . '" />';
    }
    return $icons;
}
?>