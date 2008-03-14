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

require_once "FunctionsCrypt.php";

//------------------------------------------------------------------------------
// Local date return the local date according to preference
// parameter $tt is a timestamp
function localdate($ttparam, $formatparam = "") {
	// todo apply local offset to $tt
	$tt = strtotime($ttparam);
	$format = $formatparam;
	if ($format == "") {
		$format = "%c";
	}
	return (strftime($format, $tt));
} // end of localdate

//------------------------------------------------------------------------------
// fage return a string describing the age correcponding to date 
function fage($dd, $hidden = "No") {
	if ($hidden != "No") {
		return (ww("AgeHidden"));
	}
	return (ww("AgeEqualX", fage_value($dd)));
} // end of fage

//------------------------------------------------------------------------------
// fage_value return a  the age value corresponding to date
function fage_value($dd) {
    $pieces = explode("-",$dd);
    if(count($pieces) != 3) return 0;
    list($year,$month,$day) = $pieces;
    $year_diff = date("Y") - $year;
    $month_diff = date("m") - $month;
    $day_diff = date("d") - $day;
    if ($month_diff < 0) $year_diff--;
    elseif (($month_diff==0) && ($day_diff < 0)) $year_diff--;
    return $year_diff;
} // end of fage_value

//------------------------------------------------------------------------------
// function fFullName return the FullName of the member with a special layout if some fields are crypted 
function fFullName($m) {
	return (PublicReadCrypted($m->FirstName, "*") . " " . PublicReadCrypted($m->SecondName, "*") . " " . PublicReadCrypted($m->LastName, "*"));
} // end of fFullName
