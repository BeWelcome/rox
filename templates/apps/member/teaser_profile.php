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


global $_SYSHCVOL;
$words = new MOD_words();	
?>	  

	      <div id="teaser" class="clearfix"> 
          <div id="teaser_l"> 

	          <div id="pic_main"> 
	            <div id="img1">
  <?php
	if (!empty($m->IdPhoto)){
		echo "<a href=\"myphotos.php?action=viewphoto&amp;IdPhoto=".$m->IdPhoto."\" title=\"", str_replace("\r\n", " ", $m->phototext), "\">";
	}
	if (empty($m->photo)) {
	  
	  echo "<img src=\"" . DummyPict($m->Gender,$m->HideGender) . "\"  alt=\"no ProfilePicture\"/>";
	}
	else {
	  echo "<img src=\"" . $m->photo . "\"  alt=\"ProfilePicture\"/>";
	}
	if (!empty($m->IdPhoto)){
		echo "</a>";
	}
	echo "</div>\n";
    
    // TODO: Fix the photo stuff

	/*
	// --- small pictures ---
	if (!empty($m->IdPhoto)){ ?>
	<div id="pic_sm1">
				<a href="member.php?action=previouspicture&photorank=<?php echo $m->photorank ?>&cid=<?php echo $m->id ?>">

	<img name="pic_sm1" src="<?php echo $m->pic_sm1 ?>" width="30" height="30" border="0" alt="" />
	</a> 
			</div>
	    <div id="pic_sm2"> 
	       <img name="pic_sm2" src="<?php echo $m->pic_sm2 ?>" width="30" height="30" border="0" alt="" />
	    </div>
	    <div id="pic_sm3">
				<a href="member.php?action=nextpicture&photorank=<?php echo $m->photorank ?>&cid=<?php echo $m->id ?>">
	<img name="pic_sm3" src="<?php echo $m->pic_sm3 ?>" width="30" height="30" border="0" alt="" />
	</a>
				</div>
	<?php }  ?>
    */ ?>
	          </div>
	        </div>
	<?php if (HasRight("Accepter")) { // for people with right dsiplay real status of the member
	  if ($m->Status!="Active") { ?>
	  	  <br><table><tr><td bgcolor=yellow><font color=blue><b><?php echo $m->Status ?></b></font></td></table>
    <?php    }
	} // end of for people with right dsiplay real status of the member
	 if ($m->Status=="ChoiceInactive") { ?>
	  	  <br><table><tr><td bgcolor=yellow align=center>&nbsp;<br><font color=blue><b><?php echo ww("WarningTemporayInactive") ?></b></font><br>&nbsp;</td></tr></table>
	<?php }
	?>
	        <div id="teaser_r">
	          <div id="navigation-path">
	            <a href="../country"><?php echo ww("country") ?></a> &gt;
	            <a href="../country/<?php echo $m->IsoCountry ?>"><?php echo $m->countryname ?></a> &gt;
	            <a href="../country/<?php echo $m->IsoCountry,"/",$m->regionname ?>"><?php echo $m->regionname ?></a> &gt; 
	            <a href="../country/<?php echo $m->IsoCountry,"/",$m->regionname?>/<?php echo $m->cityname ?>\"><?php echo $m->cityname ?></a>
	          </div>
	          <div id="profile-info">
	            <div id="username">
	              <strong><?php echo $m->Username ?></strong><?php //FIX ME: echo $m->FullName ?><br />
	            </div>


<?php 
	if (strstr($m->Accomodation, "anytime"))
		echo "              <img src=\"images/yesicanhost.gif\" class=\"float_left\" title=\"",$words->get("CanOfferAccomodationAnytime"),"\" width=\"30\" height=\"30\" alt=\"yesicanhost\" />\n";
	if (strstr($m->Accomodation, "yesicanhost"))
		echo "              <img src=\"images/yesicanhost.gif\" class=\"float_left\" title=\"",$words->get("CanOfferAccomodation"),"\" width=\"30\" height=\"30\" alt=\"yesicanhost\" />\n";
	if (strstr($m->Accomodation, "dependonrequest"))
		echo "              <img src=\"images/dependonrequest.gif\" class=\"float_left\" title=\"",$words->get("CanOfferdependonrequest"),"\" width=\"30\" height=\"30\" alt=\"dependonrequest\" />\n";
	if (strstr($m->Accomodation, "neverask"))
		echo "              <img src=\"images/neverask.gif\" class=\"float_left\" title=\"",$words->get("CannotOfferneverask"),"\" width=\"30\" height=\"30\" alt=\"neverask\" />\n";
	if (strstr($m->Accomodation, "cannotfornow"))
		echo "              <img src=\"images/neverask.gif\" class=\"float_left\" title=\"", $words->get("CannotOfferAccomForNow"),"\" width=\"30\" height=\"30\" alt=\"neverask\" />\n"; 

// specific icon according to membes.TypicOffer
	if (strstr($m->TypicOffer, "guidedtour"))
		echo "              <img src=\"images/icon_castle.gif\" class=\"float_left\" title=\"", $words->get("TypicOffer_guidedtour"),"\" width=\"30\" height=\"30\" alt=\"icon_castle\" />\n"; 
	if (strstr($m->TypicOffer, "dinner"))
		echo "              <img src=\"images/icon_food.gif\" class=\"float_left\" title=\"", $words->get("TypicOffer_dinner"),"\" width=\"30\" height=\"30\" alt=\"icon_food\" />\n";
	if (strstr($m->TypicOffer, "CanHostWeelChair"))
		echo "              <img src=\"images/wheelchair.gif\" class=\"float_left\" title=\"", $words->get("TypicOffer_CanHostWeelChair"),"\" width=\"30\" height=\"30\" alt=\"wheelchair\" />\n";
?>
	<table>
	<tr>	
	
    <td>
<?php 
    echo "              ", $words->get("NbComments", $m->NbComment), " (", $words->get("NbTrusts", $m->NbTrust), ")<br />\n";
	if ($m->Occupation > 0)
    // TODO: FIX mTrad !
    //echo "            ",$m->age, ", " ,$words->mTrad($m->Occupation),"\n";
	// echo "                  <p><strong>", ww("Lastlogin"), "</strong>: ", $m->LastLogin, "</p>\n";
	echo "</td>";

	// translation links
    echo "<td>";
		$IdMember=$m->id;
		if ($m->CountTrad>1) { // if member has his profile translated
			echo "              ", $words->get('ProfileVersionIn'),":\n";
		    for ($ii=0;$ii<$m->CountTrad;$ii++) { // display one tab per available translation
				$Trad=$m->Trad[$ii];
				echo "              <a href=\"bw/member.php?cid=".$IdMember."&lang=".$Trad->ShortCode."\">",FlagLanguage($Trad->IdLanguage), "</a>\n";
			}
		}	
?>
    </td>
	
    </tr>
    </table>
	</div>
<?php		
	if ($_SESSION["IdMember"] == $IdMember) { // if members own profile
	echo "            <a href=\"bw/editmyprofile.php\"><span>", $words->get('EditMyProfile')," ",FlagLanguage(), "</span></a>\n";
	}
?>
	</div>
	</div>
