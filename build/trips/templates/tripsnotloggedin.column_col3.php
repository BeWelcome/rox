<?php
/**
 * trip template for not logged in users
 *
 * @package trip
 * @author shevek
 * @copyright Copyright (c) 2013-2014
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License (GPL)
 */
$words = new MOD_words($this->getSession());
?>
<div>
    <h3><?=$words->get('TripYourOwnTripHeadline')?></h3>
    <p><?=$words->get('TripYourOwnTripText')?></p>
</div>
