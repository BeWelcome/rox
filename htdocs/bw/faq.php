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
require_once "layout/faq.php";

# There are way too many SQL statements in here, it should be cleaned up!


$FilterActive = " and Active='Active'";
if (HasRight("Faq")) { // Don't filter if member has right to modify FAQ
	$FilterActive = "";
}
$IdFaq=GetParam("IdFaq",0) ;

$argv=$_SERVER["argv"] ;
if (isset($argv[1])) {
   $IdFaq=$argv[1] ;
}

if (isset($argv[2])) {
   $_SESSION["lang"]=$argv[2] ;
}

if (isset($argv[3])) {
   $_SESSION["IdLanguage"]=$argv[3] ;
}

switch (GetParam("action")) {
	case "logout" :
		Logout();
		exit (0);

	case "insert" :
		if (!HasRight("Faq") > 0) { // only people with suficient right can edit FAQ
			$errcode = "ErrorNeedRight"; // initialise global variable
			DisplayError(ww($errcode, "Faq"));
		}
		$str = "INSERT INTO faq(created,IdCategory,Active) VALUES(NOW()," . GetParam("IdCategory") . ",'".GetParam("Status")."')";
		sql_query($str);
		$LastInsert = mysql_insert_id();

		// Load the available faq categories  
		$TCategory = array ();
		$qry = sql_query("SELECT * FROM faqcategories ORDER BY SortOrder ASC");
		while ($rr = mysql_fetch_object($qry)) {
			array_push($TCategory, $rr);
		}

		// Load the data for the current Faq to edit
		$rr = LoadRow("SELECT faq.*,faqcategories.Description AS CategoryName FROM faq,faqcategories WHERE faq.IDCategory=faqcategories.id and faq.id=" . $LastInsert);

		DisplayEditFaq($rr, $TCategory); // call the display
		exit (0);
		break;

	case "edit" :
		if (!HasRight("Faq") > 0) { // only people with sufficient right can edit FAQ
			$errcode = "ErrorNeedRight"; // initialise global variable
			DisplayError(ww($errcode, "Faq"));
		}

		// Load the available faq categories  
		$TCategory = array ();
		$qry = sql_query("SELECT * FROM faqcategories ORDER BY SortOrder ASC");
		while ($rr = mysql_fetch_object($qry)) {
			array_push($TCategory, $rr);
		}

		// Load the data for the current Faq to edit
		$rr = LoadRow("SELECT faq.*,faqcategories.Description AS CategoryName FROM faq,faqcategories WHERE faq.IDCategory=faqcategories.id AND faq.id=" . $IdFaq);

		DisplayEditFaq($rr, $TCategory); // call the display
		exit (0);
		break;


	case "rebuildextraphpfiles" :
     	$str = "SELECT faq.*,faqcategories.Description AS CategoryName FROM faq,faqcategories  WHERE faqcategories.id=faq.IdCategory " . $FilterCategory . " ORDER BY faqcategories.SortOrder,faq.SortOrder";
		$strlang="SELECT languages.ShortCode AS lang,languages.id AS IdLanguage FROM languages,words WHERE words.Code='faq' AND languages.id=words.IdLanguage" ; 
		$qry = sql_query($str);
		$TData = array ();
		while ($rWhile = mysql_fetch_object($qry)) {
			$qrylang=sql_query($strlang) ;
			while ($rlang = mysql_fetch_object($qrylang)) {
						$_SESSION["lang"]=$rlang->lang ;
						$_SESSION["IdLanguage"]=$rlang->IdLanguage ;

			 			$ss = "SELECT code FROM words WHERE code=\"FaqA_" . $rWhile->QandA."\" AND IdLanguage=".$_SESSION["IdLanguage"] ;
			 			$rFak=LoadRow($ss) ;
						if (empty($rFak->code)) continue ;

						$fname="faq_".$rWhile->QandA."_".$rlang->lang.".php" ;
						$fp=fopen($fname,"w") ;
						if ($fp==NULL) {
			   			 die("Can't create $fname\n") ;
						}
						fwrite($fp,"<?php\n") ;
						fwrite($fp,"\$IdFaq=".$rWhile->id.";\$lang=\"".$_SESSION['lang']."\";require_once \"publicfaq.php\";\n") ;
						fwrite($fp,"?>\n") ;
						fclose($fp) ;
						echo "done for $fname<br />" ;
			}
		}
		echo "rebuilt done" ;
		exit(0);
	case "wikilist" :
     	$str = "SELECT faq.*, faqcategories.Description AS CategoryName FROM faq, faqcategories  WHERE faqcategories.id=faq.IdCategory " . $FilterCategory . $FilterActive . " ORDER BY faqcategories.SortOrder, faq.SortOrder";
		$qry = sql_query($str);
		$TData = array ();
		while ($rWhile = mysql_fetch_object($qry)) {
			array_push($TData, $rWhile);
		}
		DisplayFaqWiki($TData, $rCat); // call the layout with the selected parameters
		exit(0);
	case "update" :
		if (!HasRight("Faq") > 0) { // only people with suficient right can edit FAQ
			$errcode = "ErrorNeedRight"; // initialise global variable
			DisplayError(ww($errcode, "Faq"));
		}

		if (GetStrParam("QandA") == "") {
			echo "You must fill the word code associated with the FAQ";
			DisplayError("You must fill the word code associated with the FAQ");
			exit (0);
		}

		$Faq = LoadRow("SELECT * FROM faq WHERE id=" . $IdFaq);
		$rwq = LoadRow("SELECT * FROM words WHERE code='" . "FaqQ_" . GetStrParam("QandA") . "' and IdLanguage=0");
		$rwa = LoadRow("SELECT * FROM words WHERE code='" . "FaqA_" . GetStrParam("QandA") . "' and IdLanguage=0");

		if (!isset ($rwq->id)) {
			$str = "INSERT INTO words
                                    (code,
                                     Description,
                                     IdLanguage,
                                     ShortCode,
                                     created)
                                VALUES
                                    ('FaqQ_" . GetStrParam("QandA") . "',
                                     'This is the questiontext for a Frequently Asked Question.',
                                     0,
                                     '".$_SESSION['lang']."',
                                     NOW())";
			sql_query($str);
		}
		if (!isset ($rwa->id)) {
			$str = "INSERT INTO words
                                    (code,
                                     Description,
                                     IdLanguage,
                                     ShortCode,
                                     created)
                                VALUES
                                    ('FaqA_" . GetStrParam("QandA") . "',
                                     'This is the answertext for a Frequently Asked Question.',
                                     0,
                                     '".$_SESSION['lang']."',
                                     NOW())";
			sql_query($str);
		}

		// reload for case it was just inserted before
		$rwq = LoadRow("SELECT * FROM words WHERE code='" . "FaqQ_" . GetStrParam("QandA") . "' and IdLanguage=0");
		$rwa = LoadRow("SELECT * FROM words WHERE code='" . "FaqA_" . GetStrParam("QandA") . "' and IdLanguage=0");

		$str = "UPDATE words SET Description='" . addslashes($rwq->Description) . "',Sentence='" . GetStrParam("Question") . "' WHERE id=" . $rwq->id;
		sql_query($str);
		$str = "UPDATE words SET Description='" . addslashes($rwa->Description) . "',Sentence='" . GetStrParam("Answer") . "' WHERE id=" . $rwa->id;
		sql_query($str);

		$str = "UPDATE faq SET IdCategory=" . GetParam("IdCategory") . ",QandA='" . GetParam("QandA") . "',Active='" . GetStrParam("Status") . "',SortOrder=" . GetParam("SortOrder") . " WHERE id=" . $Faq->id;
		sql_query($str);

		LogStr("updating Faq #" . $Faq->id, "Update Faq");

		break;

}

// prepare the list

if (GetParam("IdCategory")) {
	$FilterCategory = " AND IdCategory=" . GetParam("IdCategory");
} else {
	$FilterCategory = "";
}
if ($IdFaq!=0) { // if one specific Faq is chosen
	  $str = "SELECT faq.*,faqcategories.Description AS CategoryName,PageTitle FROM faq,faqcategories  WHERE faq.id=".$IdFaq." and faqcategories.id=faq.IdCategory " . $FilterCategory . $FilterActive . " ORDER BY faqcategories.SortOrder,faq.SortOrder";
}
else {
	  $str = "SELECT faq.*,faqcategories.Description AS CategoryName,PageTitle FROM faq,faqcategories  WHERE faqcategories.id=faq.IdCategory " . $FilterCategory . $FilterActive . " ORDER BY faqcategories.SortOrder,faq.SortOrder";
}
$qry = sql_query($str);
$TData = array ();
while ($rWhile = mysql_fetch_object($qry)) {
	array_push($TData, $rWhile);
}
DisplayFaq($TData, $rCat); // call the layout with the selected parameters
?>
