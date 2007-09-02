<?php
require_once "lib/init.php";
require_once "lib/FunctionsLogin.php";
require_once "layout/error.php";
require_once "layout/faq.php";


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
		Logout("main.php");
		exit (0);

	case "insert" :
		if (!HasRight("Faq") > 0) { // only people with suficient right can edit FAQ
			$errcode = "ErrorNeedRight"; // initialise global variable
			DisplayError(ww($errcode, "Faq"));
		}
		$str = "INSERT INTO faq(created,IdCategory,Active) VALUES(NOW()," . GetParam("IdCategory") . ",'".GetParamStr("Status")."')";
		sql_query($str);
		$LastInsert = mysql_insert_id();

		// Load the available faq categories  
		$TCategory = array ();
		$qry = sql_query("select * from faqcategories order by SortOrder asc");
		while ($rr = mysql_fetch_object($qry)) {
			array_push($TCategory, $rr);
		}

		// Load the data for the current Faq to edit
		$rr = LoadRow("SELECT faq.*,faqcategories.Description AS CategoryName from faq,faqcategories where faq.IDCategory=faqcategories.id and faq.id=" . $LastInsert);

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
		$qry = sql_query("select * from faqcategories order by SortOrder asc");
		while ($rr = mysql_fetch_object($qry)) {
			array_push($TCategory, $rr);
		}

		// Load the data for the current Faq to edit
		$rr = LoadRow("select faq.*,faqcategories.Description as CategoryName from faq,faqcategories where faq.IDCategory=faqcategories.id and faq.id=" . $IdFaq);

		DisplayEditFaq($rr, $TCategory); // call the display
		exit (0);
		break;


	case "rebuildextraphpfiles" :
     	$str = "select faq.*,faqcategories.Description as CategoryName from faq,faqcategories  where faqcategories.id=faq.IdCategory " . $FilterCategory . " order by faqcategories.SortOrder,faq.SortOrder";
		$strlang="select languages.ShortCode as lang,languages.id as IdLanguage from languages,words where words.Code='faq' and languages.id=words.IdLanguage" ; 
		$qry = sql_query($str);
		$TData = array ();
		while ($rWhile = mysql_fetch_object($qry)) {
			$qrylang=sql_query($strlang) ;
			while ($rlang = mysql_fetch_object($qrylang)) {
						$_SESSION["lang"]=$rlang->lang ;
						$_SESSION["IdLanguage"]=$rlang->IdLanguage ;
						$fname="faq_".$rWhile->QandA."_".$rlang->lang.".php" ;
						$fp=fopen($fname,"w") ;
						if ($fp==NULL) {
			   			 die("Can't create $fname\n") ;
						}
						fwrite($fp,"<?php\n") ;
						fwrite($fp,"require_once \"lib/init.php\";\n") ;
						fwrite($fp,"system(\"php -d session.bug_compat_42=0 /var/www/html/faq.php ".$rWhile->id." ".$_SESSION['lang']." ".$_SESSION['IdLanguage']."\") ;\n") ;
						fwrite($fp,"?>\n") ;
						fclose($fp) ;
						echo "done for $fname<br>" ;
			}
		}
		echo "rebuilt done" ;
		exit(0);
	case "wikilist" :
     	$str = "select faq.*,faqcategories.Description as CategoryName from faq,faqcategories  where faqcategories.id=faq.IdCategory " . $FilterCategory . $FilterActive . " order by faqcategories.SortOrder,faq.SortOrder";
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

		$Faq = LoadRow("select * from faq where id=" . $IdFaq);
		$rwq = LoadRow("select * from words where code='" . "FaqQ_" . GetStrParam("QandA") . "' and IdLanguage=0");
		$rwa = LoadRow("select * from words where code='" . "FaqA_" . GetStrParam("QandA") . "' and IdLanguage=0");

		if (!isset ($rwq->id)) {
			$str = "INSERT INTO words(code,Description,IdLanguage,ShortCode) values('" . "FaqQ_" . GetStrParam("QandA") . "','This is a question for a Faq',0,'".$_SESSION['lang']."')";
			sql_query($str);
		}
		if (!isset ($rwa->id)) {
			$str = "INSERT INTO words(code,Description,IdLanguage,ShortCode) values('" . "FaqA_" . GetStrParam("QandA") . "','This is an an answer for a Faq',0,'".$_SESSION['lang']."')";
			sql_query($str);
		}

		// reload for case it was just inserted before
		$rwq = LoadRow("select * from words where code='" . "FaqQ_" . GetStrParam("QandA") . "' and IdLanguage=0");
		$rwa = LoadRow("select * from words where code='" . "FaqA_" . GetStrParam("QandA") . "' and IdLanguage=0");

		$str = "update words set Description='" . addslashes($rwq->Description) . "',Sentence='" . GetStrParam("Question") . "' where id=" . $rwq->id;
		sql_query($str);
		$str = "update words set Description='" . addslashes($rwa->Description) . "',Sentence='" . GetStrParam("Answer") . "' where id=" . $rwa->id;
		sql_query($str);

		$str = "update faq set IdCategory=" . GetParam("IdCategory") . ",QandA='" . GetParam("QandA") . "',Active='" . GetStrParam("Status") . "',SortOrder=" . GetParam("SortOrder") . " where id=" . $Faq->id;
		sql_query($str);

		LogStr("updating Faq #" . $Faq->id, "Update Faq");

		break;

}

// prepare the list

if (GetParam("IdCategory")) {
	$FilterCategory = " and IdCategory=" . GetParam("IdCategory");
} else {
	$FilterCategory = "";
}
if ($IdFaq!=0) { // if one specific Faq is chosen
	  $str = "SELECT faq.*,faqcategories.Description as CategoryName,PageTitle from faq,faqcategories  where faq.id=".$IdFaq." and faqcategories.id=faq.IdCategory " . $FilterCategory . $FilterActive . " order by faqcategories.SortOrder,faq.SortOrder";
}
else {
	  $str = "SELECT faq.*,faqcategories.Description as CategoryName,PageTitle from faq,faqcategories  where faqcategories.id=faq.IdCategory " . $FilterCategory . $FilterActive . " order by faqcategories.SortOrder,faq.SortOrder";
}
$qry = sql_query($str);
$TData = array ();
while ($rWhile = mysql_fetch_object($qry)) {
	array_push($TData, $rWhile);
}
DisplayFaq($TData, $rCat); // call the layout with the selected parameters
?>
