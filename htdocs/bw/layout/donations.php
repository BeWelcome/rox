
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

// The donation return url as the form
http://www.bewelcome.org/bw/donations2.php?action=done&tx=0ME24142PE152304A&st=Completed&amt=5.00&cc=EUR&cm=&item_number=&sig=hYUTlSOjBeJvNqfFqc%252fZbrBA4p6c%252fe6EErVp1w18eOBR96p6hzzenPysL%252bFVPZi8YEcONFovQmYn%252b6QF%252fBYoVhGMoaQJCxBQh%252bLAlC0TdgeScs1skk0%252bpY6SyoC%252fNCV1ou69zWRrhDrtsa4SUHibLD%252f1RwGg43iaZjPhB24I6lg%253d

*/


require_once ("menus.php");

function DispDonation($TDonationArray) { // This picture display on line of donations
	   
  echo "<p align=center>" ;
  echo "<center><table cellpadding=\"15\" cellspacing=\"15\" bgcolor=\"#ff9966\" width=\"100%\">" ;
  echo "<tr><td colspan=4 align=left>",ww("DonationPublicListSummary") ,"</td>" ;
  echo "<tr><th>date</th><th>Amount</th><th>comment</th><th>place the donator was at moment of donation</th>" ;
  if (HasRight("Treasurer")) echo "<th>For treasurer eyes only</th>" ;
  echo "</tr>\n" ;
  $max=count($TDonationArray) ;
  for ($ii=0;$ii<$max;$ii++) {
  	   $T=$TDonationArray[$ii] ;
  	   echo "<tr bgcolor=\"" ;
	   if (isset($_SESSION["IdMember"]) and ($T->IdMember==$_SESSION["IdMember"])) {
	   		echo "yellow" ;
	   }
	   else {
	   		if ($ii%2) echo "#ccffff";
	   		else echo "#ffffff" ;
	   }
	   echo "\"  align=left><td>",date("y/m/d",strtotime($T->created)),"</td>" ;
	   echo "<td>" ;
	   printf ("%s %3.2f",$T->Money,$T->Amount) ;
	   echo "</td>" ;
	   echo "<td>",$T->SystemComment,"</td>" ;
	   echo "<td>",getcountryname($T->IdCountry),"</td>" ;
	   echo "</td>" ;
	   if (HasRight("Treasurer")) echo "<td>",LinkWithUsername(fUsername($T->IdMember))," ",$T->referencepaypal,"</td>" ;
	   echo "</tr>\n" ;
  }
  echo "</table>" ;
  	
  echo "</center></p>" ;	
} // end of DispDonation


function DisplayDonate($TDonation,$Message="") {
	$title = ww('donatetobewelcome');
	require_once "layout/header.php";

	Menu1("", ""); // Displays the top menu

	Menu2("donations.php", $title); // Displays the second menu
	
	$Menu="" ; // content of the left menu

	DisplayHeaderWithColumns($title, "", $Menu); // Display the header
	

  echo "<div class=\"info\">\n";

	if ($Message!="") {
 		 echo "<div class=\"info\">\n";
		 echo "<p class=\"navlink\">\n";
		 echo $Message ;
		 echo "</p><br />\n";
	}

  echo "<p>" ;
  echo ww("donateexplanation") ;
  LogStr("Entering donation page [".$Message."]","Donation") ; // This is not a security trick but just to find back the donator when he will come back
  
?>  
  <form action="https://www.paypal.com/cgi-bin/webscr" method="post">
<input type="hidden" name="cmd" value="_xclick">
<input type="hidden" name="business" value="treasurer@bevolunteer.org">
<select  name="amount">
<option value="10.00">10 €</option>
<option value="25.00" selected>25 €</option>
<option value="50.00">50 €</option>
<option value="100.00">100 €</option>
<option value="200.00">200 €</option>
</select>
<input type="hidden" name="item_name" value="BeVolunteer donation">
<input type="hidden" name="page_style" value="Primary">
<input type="hidden" name="no_shipping" value="1">
<input type="hidden" name="lc" value="<?php 
 if (isset($_SESSION["lang"]) ) {
 		switch ($_SESSION["lang"]){
					 case 'fr' :
					 			echo "FR" ;
								break ;
					 case 'de' :
					 			echo "DE" ;
								break ;
					 case 'it' :
					 			echo "IT" ;
								break ;
					 case 'esp' :
					 			echo "SP" ;
								break ;
					 default :
					 			echo "US" ;
								break ;
		} 
 }
 else {
   echo "US" ;
 }
 ?>"> 
<input type="hidden" name="return" value="http://www.bewelcome.org/bw/donations.php?action=done">
<input type="hidden" name="cancel_return" value="http://www.bewelcome.org/bw/donations.php?action=cancel">
<input type="hidden" name="cn" value="comment">
<input type="hidden" name="currency_code" value="EUR">
<input type="hidden" name="tax" value="0">
<input type="hidden" name="bn" value="PP-DonationsBF">
<input type="image" src="https://www.paypal.com/en_US/i/btn/x-click-but21.gif" border="0" name="submit" alt="<?php echo ww("PayPalDOnate_tooltip"); ?>" onmouseover="return('<?php echo ww("PayPalDOnate_tooltip"); ?>')">
<img alt="" border="0" src="https://www.paypal.com/en_US/i/scr/pixel.gif" width="1" height="1">
</form>
<?php

  

  
  DispDonation($TDonation) ;
  echo "</div>\n";

	require_once "footer.php";
}

function DisplayDonateThanks($TDonation,$Message="") {
	$title = ww('donatetobewelcome');
	require_once "layout/header.php";

	Menu1("", ""); // Displays the top menu

	Menu2("donations.php", $title); // Displays the second menu
	
	$Menu="" ; // content of the left menu

	DisplayHeaderWithColumns($title, "", $Menu); // Display the header
		
	if ($Message!="") {
 		 echo "<div class=\"info\">\n";
		 echo "<p class=\"navlink\">\n";
		 echo $Message ;
		 echo "</p>\n";
	}

  DispDonation($TDonation) ;

  echo "</div>\n";

  require_once "footer.php";
}
?>
