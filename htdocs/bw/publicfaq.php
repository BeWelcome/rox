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
require_once "lib/init.php";
require_once "lib/FunctionsLogin.php";
require_once "layout/error.php";

require_once ("layout/menus.php");

// Display Faq display the list of Faq in a certain category
function DisplayPublicFaq($TFaq,$lang="en",$IdLanguage=0) {
	global $title;

   if ($TFaq->PageTitle!="") {
	   $title = ww($TFaq->PageTitle);
	}
	else {
	   $title = ww("FaqQ_" . $TFaq->QandA) ;
	}
	include "layout/header.php";

	Menu1("faq.php", ww('FaqPage')); // Displays the top menu
    
    echo "\n";
	echo "    <div id=\"main\">\n";
	echo "      <div id=\"teaser\">\n";
	echo "        <h1>", $title, " </h1>\n";
	echo "      </div>\n";

	// Content with just two columns
	echo "\n";
	echo "      <div id=\"col3\" class=\"twocolumns\">\n";
	echo "        <div id=\"col3_content\" class=\"clearfix\">\n";
    
	// Display the question and answer
	echo "<div class=\"info\">\n";
	
	$Q = wwinlang("FaqQ_" . $TFaq->QandA);
	$A = wwinlang("FaqA_" . $TFaq->QandA);
	echo "<p>", str_replace("\n", "", $A), "</p>\n";
	
	include "layout/footer.php";
	exit(0) ;
} // end of DisplayPublicFaq



if (!isset($IdFaq)) $IdFaq=GetParam("IdFaq",0) ;
if (!isset($lang)) $lang=GetStrParam("lang","en") ;
$rr = LoadRow("select SQL_CACHE id,EnglishName,ShortCode from languages where ShortCode='" . $lang."'");
$IdLanguage=$rr->id ;

// prepare the result

$rr = LoadRow("SELECT faq.*,faqcategories.Description AS CategoryName,PageTitle FROM faq,faqcategories  WHERE faq.id=".$IdFaq." and faqcategories.id=faq.IdCategory " . $FilterCategory . $FilterActive . " ORDER BY faqcategories.SortOrder,faq.SortOrder");

$_SESSION["lang"]=$lang ;
$_SESSION["IdLanguage"]=$IdLanguage ;

DisplayPublicFaq($rr,$lang,$IdLanguage); // call the layout with the selected parameters
?>
