<?php
/*
Copyright (c) 2007 BeVolunteer
This file is part of BW Rox.
BW Rox is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.
BW Rox is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.
You should have received a copy of the GNU General Public License
along with this program; if not, see <http://www.gnu.org/licenses/> or
write to the Free Software Foundation, Inc., 59 Temple PlaceSuite 330,
Boston, MA 02111-1307, USA.
*/
$words = new MOD_words();
?>

<h2><?php echo $words->get('GetActive') ?></h2>
<p><?php echo $words->get('GetActiveIntro') ?></p>

<div class="subcolumns twocolumns">
			
	<div class="floatbox rounded">
		<img class="float_left" src="images/icons/tango/32x32/application-x-php.png" alt="development" />
		<h3><?php echo $words->get('GetActiveDevTitle')?></h3>
		<p class="smallinfo"><?php echo $words->get('HelpBeWelcomeDevTags') . " &#124; " . $words->get('HelpBeWelcomeContact') . ": " . $words->get('GetActiveDevContact')?></p>
		<p><?php echo $words->get('GetActiveDevText')?></p>
	</div> <!-- floatbox -->

	<div class="floatbox rounded">
		<img class="float_left" src="images/icons/tango/32x32/applications-science.png" alt="testing" />
		<h3><?php echo $words->get('GetActiveTestingTitle')?></h3>
		<p class="smallinfo"><?php echo $words->get('HelpBeWelcomeTestingTags') . " &#124; " . $words->get('HelpBeWelcomeContact') . ": " . $words->get('GetActiveTestingContact')?></p>
		<p><?php echo $words->get('GetActiveTestingText')?></p>
	</div> <!-- floatbox -->

	<div class="floatbox rounded">
		<img class="float_left" src="images/icons/tango/32x32/help-browser.png" alt="support" />
		<h3><?php echo $words->get('GetActiveSupportTitle')?></h3>
		<p class="smallinfo"><?php echo $words->get('HelpBeWelcomeSupportTags') . " &#124; " . $words->get('HelpBeWelcomeContact') . ": " . $words->get('GetActiveSupportContact')?></p>
		<p><?php echo $words->get('GetActiveSupportText')?></p>
	</div> <!-- floatbox -->
	
	<div class="floatbox rounded">
		<img class="float_left" src="images/icons/tango/32x32/system-users.png" alt="local volunteering" />
		<h3><?php echo $words->get('GetActiveLocalTitle')?></h3>
		<p class="smallinfo"><?php echo $words->get('HelpBeWelcomeLocalTags') . " &#124; " . $words->get('HelpBeWelcomeContact') . ": " . $words->get('GetActiveLocalContact')?></p>
		<p><?php echo $words->get('GetActiveLocalText')?></p>
	</div> <!-- floatbox -->

	<div class="floatbox rounded">
		<img class="float_left" src="images/icons/tango/32x32/donatek.png" alt="donation" />
		<h3><?php echo $words->get('GetActiveDonationTitle')?></h3>
		<p><?php echo $words->get('GetActiveDonationText')?></p>
		<p class="smallinfo"><?php echo $words->get('HelpBeWelcomeDonationTags') . " &#124; " . $words->get('HelpBeWelcomeContact') . ": " . $words->get('GetActiveDonationContact')?></p>
	</div> <!-- floatbox -->

	<div class="floatbox rounded">
		<img class="float_left" src="images/icons/tango/32x32/system-users.png" alt="bevolunteer" />
		<h3><?php echo $words->get('GetActiveBVTitle')?></h3>
		<p><?php echo $words->get('GetActiveBVText')?></p>
		<p class="smallinfo"><?php echo $words->get('HelpBeWelcomeBVTags') . " &#124; " . $words->get('HelpBeWelcomeContact') . ": " . $words->get('GetActiveBVContact')?></p>
	</div> <!-- floatbox -->

	<div class="floatbox rounded">
		<img class="float_left" src="images/icons/tango/32x32/applications-graphics.png" alt="design" />
		<h3><?php echo $words->get('GetActiveDesignTitle')?></h3>
		<p class="smallinfo"><?php echo $words->get('HelpBeWelcomeDesignTags') . " &#124; " . $words->get('HelpBeWelcomeContact') . ": " . $words->get('GetActiveDesignContact')?></p>
		<p><?php echo $words->get('GetActiveDesignText')?></p>
	</div> <!-- floatbox -->

	<div class="floatbox rounded">
		<img class="float_left" src="images/icons/tango/32x32/system-users.png" alt="new member bewelcome" />
		<h3><?php echo $words->get('GetActiveNMBWTitle')?></h3>
		<p class="smallinfo"><?php echo $words->get('HelpBeWelcomeNMBWTags') . " &#124; " . $words->get('HelpBeWelcomeContact') . ": " . $words->get('GetActiveNMBWContact')?></p>
		<p><?php echo $words->get('GetActiveNMBWText')?></p>
	</div> <!-- floatbox -->
	
	<div class="floatbox rounded">
		<img class="float_left" src="images/icons/tango/32x32/languages.png" alt="translate" />
		<h3><?php echo $words->get('GetActiveTranslateTitle')?></h3>
		<p class="smallinfo"><?php echo $words->get('HelpBeWelcomeTranslateTags') . " &#124; " . $words->get('HelpBeWelcomeContact') . ": " . $words->get('GetActiveTranslateContact')?></p>
		<p><?php echo $words->get('GetActiveTranslateText')?></p>
	</div> <!-- floatbox -->

	<div class="floatbox rounded">
		<img class="float_left" src="images/icons/tango/32x32/help-browser.png" alt="suggestions" />
		<h3><?php echo $words->get('GetActiveSuggestionsTitle')?></h3>
		<p class="smallinfo"><?php echo $words->get('HelpBeWelcomeSuggestionsTags') . " &#124; " . $words->get('HelpBeWelcomeContact') . ": " . $words->get('GetActiveSuggestionsContact')?></p>
		<p><?php echo $words->get('GetActiveSuggestionsText')?></p>
	</div> <!-- floatbox -->

	<div class="floatbox rounded">
		<img class="float_left" src="images/icons/tango/32x32/megaphone.png" alt="communication and pr" />
		<h3><?php echo $words->get('GetActivePRTitle')?></h3>
		<p class="smallinfo"><?php echo $words->get('HelpBeWelcomePRTags') . " &#124; " . $words->get('HelpBeWelcomeContact') . ": " . $words->get('GetActivePRContact')?></p>
		<p><?php echo $words->get('GetActivePRText')?></p>
	</div> <!-- floatbox -->

	<div class="floatbox rounded">
		<img class="float_left" src="images/icons/tango/32x32/system-users.png" alt="forum moderation" />
		<h3><?php echo $words->get('GetActiveModTitle')?></h3>
		<p class="smallinfo"><?php echo $words->get('HelpBeWelcomeModTags') . " &#124; " . $words->get('HelpBeWelcomeContact') . ": " . $words->get('GetActiveModContact')?></p>
		<p><?php echo $words->get('GetActiveModText')?></p>
	</div> <!-- floatbox -->

</div>