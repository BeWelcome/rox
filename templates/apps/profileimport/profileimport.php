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
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, see <http://www.gnu.org/licenses/> or 
write to the Free Software Foundation, Inc., 59 Temple Place - Suite 330, 
Boston, MA  02111-1307, USA.

*/
/**
 * 
 * @author Jeroen Van Beirendonck (jeroen.vanbeirendonck@gmail.com)
 */
		
// get current request
$request = PRequest::get()->request;

/**
 * Get texts from table "words" to speak to the user.
 * @see /modules/i18n/lib/words.lib.php
 */
$words = new MOD_words();
?>


<h2>Profile import</h2>

<?php	
	if (!$User = APP_User::login()) {
	    echo '<span class="error">'.$words->getFormatted('ErrorMustBeLogged').'</span>';
	    return;
	}
	
    echo $PIMessage;
?>
