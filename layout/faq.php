<?php
require_once ("menus.php");

// Display Faq display the list of Faq in a certain category
function DisplayFaq($TFaq) {
	global $title;
	if (GetParam("IdFaq",0)==0) {
	   $title = ww('FaqPage');
	}
	elseif ($TFaq[0]->PageTitle!="") {
	   $title = ww($TFaq[0]->PageTitle);
	}
	else {
	   $title = ww("FaqQ_" . $TFaq[0]->QandA) :
	}
	include "header.php";

	Menu1("faq.php", ww('FaqPage')); // Displays the top menu
	Menu2($_SERVER["PHP_SELF"]); // Displays the second menu

	DisplayHeaderWithColumns(ww("Faq")); // Display the header

	$iiMax = count($TFaq);
	$LastCat = "";
	// Display the list of the questions
	for ($ii = 0; $ii < $iiMax; $ii++) {

		if ($LastCat != $TFaq[$ii]->CategoryName) {
			$LastCat = $TFaq[$ii]->CategoryName;
			echo "<br>";
			if (HasRight("Faq") > 0)
				echo "[<a href=\"faq.php?action=insert&IdCategory=", $TFaq[$ii]->IdCategory, "\">insert new faq in this category</a>]\n";
			echo " <H3 style=\"display:inline\">", ww($TFaq[$ii]->CategoryName), "</H3>";
			echo "<br>\n<ul>\n";
		}

		$Q = ww("FaqQ_" . $TFaq[$ii]->QandA);
		echo "<li>";
		if ($TFaq[$ii]->QandA == "")
			$Q = " new ";
		if (HasRight("Faq") > 0) {
			if ($TFaq[$ii]->QandA == "")
				echo " [<a href=\"faq.php?action=edit&IdFaq=", $TFaq[$ii]->id, "\">edit this new faq</a>]\n";
			else
				echo " [<a href=\"faq.php?action=edit&IdFaq=", $TFaq[$ii]->id, "\">edit</a>]\n";
		}
		echo " <a href=\"" . $_SERVER["PHP_SELF"] . "?IdFaq=", $TFaq[$ii]->id, "\">", $Q, "</a>";
		echo "</li>\n";

	}
	echo "</ul>\n";

	echo "<br>";

	// Display the list of the answers
	echo "<ul>\n";
	for ($ii = 0; $ii < $iiMax; $ii++) {
		//    echo "					<div class=\"clear\" />\n";
		$Q = ww("FaqQ_" . $TFaq[$ii]->QandA);
		$A = ww("FaqA_" . $TFaq[$ii]->QandA);
		echo "<li><strong><a name=", $TFaq[$ii]->id, "></a> ", $Q, "</strong></li>\n";
		echo "<li>", str_replace("\n", "<br>", $A), "<hr></li>\n";
	}
	echo "</ul>\n";

	include "footer.php";
} // end of DisplayFaq


// Display Faq display the list of Faq in  a wiki form
function DisplayFaqWiki($TFaq) {
	global $title;
	$title = ww('FaqPage');
	include "header.php";

	Menu1("faq.php", ww('FaqPage')); // Displays the top menu
	Menu2($_SERVER["PHP_SELF"]); // Displays the second menu

	DisplayHeaderShortUserContent(ww("Faq")); // Display the header

	$iiMax = count($TFaq);
	$LastCat = "";
	// Display the list of the questions
	for ($ii = 0; $ii < $iiMax; $ii++) {

		if ($LastCat != $TFaq[$ii]->CategoryName) {
			$LastCat = $TFaq[$ii]->CategoryName;
			echo "<br>";
			echo "'''==", ww($TFaq[$ii]->CategoryName), "=='''<br>\n";
		}

		$A = ww("FaqA_" . $TFaq[$ii]->QandA);
		$Q = ww("FaqQ_" . $TFaq[$ii]->QandA);
		echo "==", $Q, "==<br>\n";
		echo "", $A, "<br>\n";
		echo "<br>";

	}
	echo "<br>";

	include "footer.php";
} // end of DisplayFaqWiki

// Display the edit form to modify a Faq
// This is a volunteer too so many text is hardcoded (volunteers speaks english)
function DisplayEditFaq($Faq, $TCategory) {
	global $title;
	$title = ww('FaqPage');
	include "header.php";

	Menu1("faq.php", ww('FaqPage')); // Displays the top menu
	Menu2($_SERVER["PHP_SELF"]); // Displays the second menu

	DisplayHeaderShortUserContent("Editing FAQ#" . $Faq->id . " (" . $Faq->QandA . ")"); // Display the header

	echo "<center>\n<b>Beware</b> edit Faq only apply to english Faq. For other languages, use AdminWords<br><br>\n";

	echo "<form method=post action=faq.php>\n";
	echo "<table width=\"90%\">\n";
	echo "<input type=hidden Name=IdFaq value=", $Faq->id, ">\n";
	echo "<input type=hidden Name=action value=update>\n";
	echo "<tr><td colspan=2>";
	echo "Category  ";
	echo "<select Name=IdCategory>\n";
	for ($ii = 0; $ii < count($TCategory); $ii++) {
		echo "<option value=" . $TCategory[$ii]->id;
		if ($TCategory[$ii]->id == $Faq->IdCategory)
			echo " selected ";
		echo ">", ww($TCategory[$ii]->Description), "</option>\n";
	}
	echo "</select>\n";
	echo "\nStatus :<select name=Active>\n";
	echo "<option value=\"Active\" ";
	if ($Faq->Active == "Active")
		echo " selected ";
	echo ">Active</option>\n";
	echo "<option value=\"Active\" ";
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
	echo "</form>\n";
	echo "</table>\n</center>\n";

	include "footer.php";
} // end of DisplayEditForm
?>
