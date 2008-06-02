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

$words = new MOD_words();
if (!empty($errormessage)) echo "<p><b>",$errormessage,"</b></p>" ;

echo "<p>",$words->getFormatted("verifymembers_explanation",$_SESSION["Username"]),"</p>" ;
echo "<p>\n<form name=\"entermembertoverify\" action=\"verifymembers/prepareverifymember\"  id=\"prepareverifymember\" method=\"post\">\n" ;
//echo "<input type=\"hidden\" name=\"",$callbackId,"\"  value=\"1\"/>" ;
echo "<table border=\"0\">\n" ;
echo "<tr><td>",$words->getFormatted("verifymembers_username_to_check"),"</td><td align=left> <input type=\"text\" name=\"username_to_verify\"></td></tr>\n" ;
echo "<tr><td>",$words->getFormatted("verifymembers_member_to_check_pw"),"</td><td align=left> <input type=\"password\"  name=\"member_to_check_pw\"> <font color=red>*</font> ",$words->getFormatted("verifymembers_passwordexp"),"</td></tr>\n" ;
echo "<tr><td colspan=2 align=center>"," <input  type=\"submit\" value=\"",$words->getFormatted("verifymembers_proceedtocheck"),"\"></td></tr>\n" ;
echo "</table>\n" ;
echo "</form>\n" ;
echo "</p>" ;

