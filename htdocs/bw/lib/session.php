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

/*
// Trying to setup session for old BW ways (session is not startet at this point)
ini_set ('session.use_trans_sid', 0);
ini_set ('session.hash_bits_per_character', 6);
ini_set ('session.hash_function', 1);
session_start();
*/

function SetupSession()
{
	if (!isset ($_GET['showtransarray'])) 
	{
		$_SESSION['TranslationArray'] = array (); // initialize $_SESSION['TranslationArray'] if not currently switching to adminwords
	}
}

?>