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


require_once ("menus.php");

function DisplayDonate() {
	$title = ww('donatetobewelcome');
	require_once "layout/header.php";

	Menu1("", ""); // Displays the top menu

	Menu2("donatetobewelcome.php", $title); // Displays the second menu
	
	$Menu="" ; // content of the left menu

	DisplayHeaderWithColumns($title, "", $Menu); // Display the header
	

  echo "<div class=\"info\">\n";
  echo "<p class=\"navlink\">\n";
  $link="http://www.bevolunteer.org/joomla/index.php/Donate!?Itemid=54&option=com_civicrm" ; // this is the link to the donate page, it may change
  echo ww("donateexplanation",$link) ;
  echo "</p>\n";
  
  echo "<p>" ;
?>
<form action="https://www.paypal.com/cgi-bin/webscr" method="post">
<input type="hidden" name="cmd" value="_s-xclick">
<input type="image" src="https://www.paypal.com/en_US/i/btn/x-click-butcc-donate.gif" border="0" name="submit" alt="Make payments with PayPal - it's fast, free and secure!">
<img alt="" border="0" src="https://www.paypal.com/en_US/i/scr/pixel.gif" width="1" height="1">
<input type="hidden" name="encrypted" value="-----BEGIN PKCS7-----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-----END PKCS7-----
">
</form>
<?php
  echo "</p>" ;
  
  echo "<p align=center>" ;
  echo "<center><table cellpadding=\"15\" cellspacing=\"15\" bgcolor=\"#ff9900\" width=\"80%\"><tr><th>date</th><th>Amount</th><th>comment</th><td>place the donator was at moment of donation</td></tr>\n" ;

  echo "<tr bgcolor=\"#ff9966\"><td>2007/05/05</td>" ;	
  echo "<td>€50</td>" ;	
  echo "<td>direct donation</td><td>From Belgium</td></tr>" ;
  	
  echo "<tr><td>2007/09/26</td>" ;	
  echo "<td>$5.00</td>" ;	
  echo "<td>via paypal</td><td></td></tr>" ;
  	
  echo "<tr bgcolor=\"#ff9966\"><td>2007/09/29</td>" ;	
  echo "<td>$25.00</td>" ;	
  echo "<td>via paypal</td><td></td></tr>" ;
  	
  echo "<tr><td>2007/10/04</td>" ;	
  echo "<td>$100.00</td>" ;	
  echo "<td>via paypal</td><td>From Spain</td></tr>" ;
  
  echo "<tr bgcolor=\"#ff9966\"><td>2007/10/12</td>" ;	
  echo "<td>€25</td>" ;	
  echo "<td>via paypal</td><td></td></tr>" ;
	
  echo "<tr><td>2007/10/13</td>" ;	
  echo "<td>€25</td>" ;	
  echo "<td>via paypal</td><td></td></tr>" ;
	
  echo "<tr bgcolor=\"#ff9966\"><td>2007/10/13</td>" ;	
  echo "<td>€5</td>" ;	
  echo "<td>via paypal (with a promise of similar donation each month)</td><td>from Germany</td></tr>" ;
  	
  echo "<tr><td>2007/10/14</td>" ;	
  echo "<td>€10</td>" ;	
  echo "<td>via paypal</td><td>From Germany</td></tr>" ;
	
  echo "<tr bgcolor=\"#ff9966\"><td>2007/10/22</td>" ;	
  echo "<td>€25</td>" ;	
  echo "<td>via paypal</td>" ;
  echo "<td>from US</td>" ;
  echo "</tr>" ;
	
  echo "<tr><td>2007/10/22</td>" ;	
  echo "<td>€25</td>" ;	
  echo "<td>via paypal</td>" ;
  echo "<td>from Germany</td>" ;
  echo "</tr>" ;
	

  echo "</table>" ;
  	
  echo "</center></p>" ;	
  echo "</div>\n";

	require_once "footer.php";
}
?>
