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

// Display Faq display the list of Faq in a certain category
function DisplayFaq($TFaq) {
	global $title;
	$IdFaq=GetParam("IdFaq",0) ;

	$argv=$_SERVER["argv"] ;
	if (isset($argv[1])) {
	   $IdFaq=$argv[1] ;
	}
	
	
	if ($IdFaq==0) {
	   $title = ww('FaqPage');
	}
	elseif ($TFaq[0]->PageTitle!="") {
	   $title = ww($TFaq[0]->PageTitle);
	}
	else {
	   $title = ww("FaqQ_" . $TFaq[0]->QandA) ;
	}
	include "header.php";

	Menu1("faq.php", ww('FaqPage')); // Displays the top menu
	Menu2("faq.php", ww('GetAnswers'));
    
    echo "\n";
	echo "    <div id=\"main\">\n";
	echo "      <div id=\"teaser_bg\">\n";
	echo "      <div id=\"teaser\">\n";
	echo "        <h1>", $title, " </h1>\n";
	echo "      </div>\n";
	//menugetanswers("faq.php", $title); // Display the generic header
	echo "      </div>\n";

	// Content with just two columns
	echo "\n";
	echo "      <div id=\"col3\" class=\"twocolumns\">\n";
	echo "        <div id=\"col3_content\" class=\"clearfix\">\n";
    
	$iiMax = count($TFaq);
	$LastCat = "";
	// Display the list of the questions
	echo "<div class=\"info\">\n";
	for ($ii = 0; $ii < $iiMax; $ii++) {
    if ($LastCat != $TFaq[$ii]->CategoryName) {
			$LastCat = $TFaq[$ii]->CategoryName;
			
			if (HasRight("Faq") > 0)
				echo "[<a href=\"faq.php?action=insert&IdCategory=", $TFaq[$ii]->IdCategory, "\">insert new faq in this category</a>]\n";
			if ($IdFaq==0) {
				 if ($ii>0) echo "</ul><br/>\n";
				 echo " <h3>", ww($TFaq[$ii]->CategoryName), "</h3>\n<ul>\n";
			}
		}

		$Q = ww("FaqQ_" . $TFaq[$ii]->QandA);
		if ($IdFaq==0) echo "<li>";
		if ($TFaq[$ii]->QandA == "")
			$Q = " new ";
		if (HasRight("Faq") > 0) {
			if ($TFaq[$ii]->QandA == "")
				echo " [<a href=\"faq.php?action=edit&IdFaq=", $TFaq[$ii]->id, "\">edit this new faq</a>]\n";
			else
				echo " [<a href=\"faq.php?action=edit&IdFaq=", $TFaq[$ii]->id, "\">edit</a>]\n";
		}
//		echo " <a href=\"" . $_SERVER["PHP_SELF"] . "?IdFaq=", $TFaq[$ii]->id, "\">", $Q, "</a>";
		if ($IdFaq==0) {
		   if (IsLoggedIn()) {
  	   		  echo " <a href=\"faq.php?IdFaq=".$TFaq[$ii]->id."\">", $Q, "</a></li>\n";
		   }
		   else { // If not login provide links to specific files
			 		$ss="select code from words where code=\"FaqA_" . $TFaq[$ii]->QandA."\" and IdLanguage=".$this->_session->get("IdLanguage") ;
//					echo $ss ;
			 		$rFak=LoadRow($ss) ;
					if (empty($rFak->code)) {
		   	  	 echo " <a href=\"faq_" . $TFaq[$ii]->QandA."_en.php\">", $Q, "</a></li>\n"; // Force english if the text is not yet translated to avoid several page with the same english default text
					}
					else {
		   	  	 echo " <a href=\"faq_" . $TFaq[$ii]->QandA."_".$this->_session->get("lang").".php\">", $Q, "</a></li>\n";
					}
		   }
		}

	} // end of for $ii

	if ($IdFaq==0) echo "</ul><br/>\n";

	// Display the list of the answers
	for ($ii = 0; ($ii < $iiMax) and (IsLoggedIn() or ($IdFaq!=0)); $ii++) {
		//    echo "					<div class=\"clear\" />\n";
		if ($IdFaq==0) echo " <h3>", ww($TFaq[$ii]->CategoryName), "</h3>";
		$Q = ww("FaqQ_" . $TFaq[$ii]->QandA);
		$A = ww("FaqA_" . $TFaq[$ii]->QandA);
		if ($IdFaq==0) {
		   echo "<h4><a name=\"", $TFaq[$ii]->id, "\"></a> ", $Q, "</h4>\n";
		}
		echo "<p>", str_replace("\n", "", $A), "</p>\n";
	}
	
	if (IsAdmin()) {
	   echo "<br/><p><a href=\"faq.php?action=rebuildextraphpfiles\">rebuild extra php files</a></p>" ; 
	}

	include "footer.php";
	exit(0) ;
} // end of DisplayFaq


// Display Faq display the list of Faq in  a wiki form
function DisplayFaqWiki($TFaq) {
	global $title;
	$title = ww('FaqPage');
	include "header.php";

	Menu1("faq.php", ww('FaqPage')); // Displays the top menu
	Menu2("aboutus.php", ww('GetAnswers')); // Displays the second menu

   echo "\n";
	echo "    <div id=\"main\">\n";
	echo "      <div id=\"teaser_bg\">\n";
	echo "      <div id=\"teaser\">\n";
	echo "        <h1>", $title, " </h1>\n";
	echo "      </div>\n";
	//menugetanswers("faq.php", $title); // Display the generic header
	echo "      </div>\n";

	// Content with just two columns
	echo "\n";
	echo "      <div id=\"col3\" class=\"twocolumns\">\n";
	echo "        <div id=\"col3_content\" class=\"clearfix\">\n";

	$iiMax = count($TFaq);
	$LastCat = "";
	// Display the list of the questions

	for ($ii = 0; $ii < $iiMax; $ii++) {

		if ($LastCat != $TFaq[$ii]->CategoryName) {
			$LastCat = $TFaq[$ii]->CategoryName;
			echo "<br/>";
			echo "'''==", ww($TFaq[$ii]->CategoryName), "=='''<br/>\n";
		}

		$A = ww("FaqA_" . $TFaq[$ii]->QandA);
		$Q = ww("FaqQ_" . $TFaq[$ii]->QandA);
		echo "==", $Q, "==<br/>\n";
		echo "", $A, "<br/>\n";
		echo "<br/>";

	}
	echo "<br/>";

	include "footer.php";
} // end of DisplayFaqWiki

// Display the edit form to modify a Faq
// This is a volunteer too so many text is hardcoded (volunteers speaks english)
function DisplayEditFaq($Faq, $TCategory) {
	global $title;
	$title = ww('FaqPage');
	include "header.php";

	Menu1("faq.php", ww('FaqPage')); // Displays the top menu
	Menu2("aboutus.php", ww('GetAnswers')); // Displays the second menu

   echo "\n";
	echo "    <div id=\"main\">\n";
	echo "      <div id=\"teaser_bg\">\n";
	echo "      <div id=\"teaser\">\n";
	echo "        <h1>Editing FAQ#", $Faq->id , " (" , $Faq->QandA , ") </h1>\n";
	echo "      </div>\n";
	//menugetanswers("faq.php", $title); // Display the generic header
	echo "      </div>\n";

	// Content with just two columns
	echo "\n";
	echo "      <div id=\"col3\" class=\"twocolumns\">\n";
	echo "        <div id=\"col3_content\" class=\"clearfix\">\n";

	echo "<center>\n<b>Beware</b> edit Faq only apply to english Faq. For other languages, use AdminWords<br/><br/>\n";

	echo "<form method=post action=faq.php>\n";
	echo "<table width=\"90%\">\n";
	echo "<input type=hidden Name=\"IdFaq\" value=", $Faq->id, ">\n";
	echo "<input type=hidden Name=action value=update>\n";
	echo "<tr><td colspan=2>";
	echo "Category  ";
	echo "<select Name=\"IdCategory\">\n";
	for ($ii = 0; $ii < count($TCategory); $ii++) {
		echo "<option value=" . $TCategory[$ii]->id;
		if ($TCategory[$ii]->id == $Faq->IdCategory)
			echo " selected ";
		echo ">", ww($TCategory[$ii]->Description), "</option>\n";
	}
	echo "</select>\n";
	echo "\nStatus :<select name=\"Status\">\n";
	echo "<option value=\"Active\" ";
	if ($Faq->Active == "Active")
		echo " selected ";
	echo ">Active</option>\n";
	echo "<option value=\"Not Active\" ";
	if ($Faq->Active == "Not Active")
		echo " selected ";
	echo ">Not Active</otpion>\n";
	echo "</select>\n";
	echo "</td>\n";
	echo "<tr><td>";
	if ($Faq->QandA == "")
		echo "You must create a name for this Faq like <i>AbuseCaseWhatToDo</i>  -->";
	else
		echo "Faq associated root word ";
	echo "</td><td><input type=text size=30 name=QandA value=\"", $Faq->QandA, "\">";
	echo " SortOrder <input name=SortOrder Value=\"" . $Faq->SortOrder . "\" type=text size=1>";
	echo "</td>\n";
	echo "<tr><td>Question</td><td>";
	echo "<textarea cols=60 rows=1 name=Question>";
	if ($Faq->QandA == "")
		echo "  - to complete - ";
	else
		echo (wwinlang("FaqQ_" . $Faq->QandA, 0));
	echo "</textarea></td>\n";

	echo "<tr><td>Answer</td><td>";
	echo "<textarea cols=60 rows=6 name=Answer>";
	if ($Faq->QandA == "")
		echo "  - to complete - ";
	else
		echo (wwinlang("FaqA_" . $Faq->QandA, 0));
	echo "</textarea></td>\n";
	echo "<tr><td colspan=2 align=center><input type=submit value=update></td>\n";
	echo '<tr><td>What kind of change is this?</td>
            <td><input type="radio" name="changetype" value="minor" /> Minor change - old translations remain valid<br />
                <input type="radio" name="changetype" value="major" /> Major change - old translations are invalidated
            </td></tr>';
        
        echo "</form>\n";
	echo "</table>\n</center>\n";

	include "footer.php";
} // end of DisplayEditForm
?>
