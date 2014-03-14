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
write to the Free Software Foundation, Inc., 59 Temple PlaceSuite 330, 
Boston, MA  02111-1307, USA.

*/
$words = new MOD_words();
$R = MOD_right::get();
$hasRight = $R->hasRight('Treasurer');
$i18n = new MOD_i18n('date.php');
    echo "<div class=\"bw-row\">",$words->get("DonationPublicListSummary"),"</div>";
    echo "<table cellpadding=\"15\" cellspacing=\"15\" style=\"width:100%\">" ;
    echo "<tr><th>",$words->get("DateDonation"),"</th><th>",$words->get("AmountDonation"),"</th><th>",$words->get("CommentDonation"),"</th><th>",$words->get("PlaceDonation"),"</th>" ;
    if ($hasRight) echo "<th>For treasurer eyes only</th>" ;
    echo "</tr>\n" ;
    $max=count($TDonationArray) ;

    for ($ii=0;$ii<$max;$ii++) {
    $info_styles = array(0 => "class=\"blank\"", 1 => "class=\"highlight\"");
        static $iii = 0;
        $T=$TDonationArray[$ii] ;
        $string = $info_styles[($iii++%2)]; // this displays the <tr>
    if (isset($_SESSION["IdMember"]) and ($T->IdMember==$_SESSION["IdMember"])) {
        $string .= "bgcolor=\"yellow\""; 
    }
        echo "<tr ",$string," align=left><td>",date("y/m/d",strtotime($T->created)),"</td>" ;
        echo "<td>" ;
        printf ("%s %3.2f",$T->Money,$T->Amount) ;
        echo "</td>" ;
        echo "<td>",$T->SystemComment,"</td>" ;
        echo "<td>",$T->CountryName,"</td>" ;
        echo "</td>" ;
         if ($hasRight) {
            $m = MOD_member::getMember_userId($T->IdMember);
            if ($m) {
                echo "<td>",$m->getUsername()," ",$T->referencepaypal,"</td>" ;
            }
         }
        echo "</tr>\n" ;
    }
    echo "</table>" ;


?>
