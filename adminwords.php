<?php
require_once "lib/init.php";
$title = "words managment";
require_once ("layout/Menus.php");

MustLogIn(); // Need to be logged

$lang = $_SESSION['lang']; // save session language
$_SESSION['lang'] = "eng";
$_SESSION['IdLanguage'] = 0; // force english for menu

include "layout/header.php";

Menu1("", "Admin Words"); // Displays the top menu

Menu2("main.php", "Admin Words"); // Displays the second menu

DisplayHeaderShortUserContent($title);

$RightLevel = HasRight('Words'); // Check the rights
if ($RightLevel < 1) {
	echo "This Need the suffcient <b>Words</b> rights<br>";
	exit (0);
}

$scope = RightScope('Words');

$_SESSION['lang'] = $lang; // restore session language
$rr = LoadRow("select * from languages where ShortCode='" . $lang . "'");
$ShortCode = $rr->ShortCode;
$_SESSION['IdLanguage'] = $IdLanguage = $rr->id;

echo "<h2  style=\"display:inline\">Your current language is ", " #", $rr->id, "(", $rr->EnglishName, ",", $rr->ShortCode, ") your scope is for $scope </h2>";
echo "&nbsp;&nbsp;<a href=adminwords.php?ShowLanguageStatus=", $rr->id, "> All in ", $rr->EnglishName, "</a>";
echo "&nbsp;&nbsp;<a href=adminwords.php?onlymissing&ShowLanguageStatus=", $rr->id, "> Only missing in ", $rr->EnglishName, "</a>";
echo "&nbsp;&nbsp;<a href=adminwords.php?showstats>Show stats</a><br>";
$Sentence = "";
$code = "";
if (isset ($_GET['code']))
	$code = $_GET['code'];
if (isset ($_GET['Sentence']))
	$Sentence = $_GET['Sentence'];
if ((isset ($_GET['id'])) and ($_GET['id'] != ""))
	$id = $_GET['id'];
if (isset ($_GET['lang']))
	$lang = $_GET['lang'];

if (isset ($_POST['code']))
	$code = $_POST['code'];
if (isset ($_POST['Sentence']))
	$Sentence = $_POST['Sentence'];
if ((isset ($_POST['id'])) and ($_POST['id'] != ""))
	$id = $_POST['id'];
if (isset ($_POST['lang']))
	$lang = $_POST['lang'];

// if it was a show translation on page request
if (isset ($_GET['showstats'])) {
    $rr=LoadRow("select count(*) as cnt from words where IdLanguage=0 and donottranslate!='yes'") ;
  	$cnt=$rr->cnt ;
  	$str="select count(*) as cnt,EnglishName from words,languages where languages.id=words.IdLanguage and donottranslate!='yes' group by words.IdLanguage" ;
  	$qry=sql_query($str) ;
	echo "<table>" ;
  	while ($rr=mysql_fetch_object($qry)) {
	      echo "<tr><td>",$rr->EnglishName,"</td><td>" ;
    	  printf("%01.1f", ($rr->cnt / $cnt) * 100) ;
		  echo  "% achieved</td>";
  	}
	echo "</table>" ;
}
// if it was a show translation on page request
if (isset ($_GET['showtransarray'])) {

	$count = count($_SESSION['TranslationArray']);
	echo "\n<table cellpadding=3 width=100%><tr bgcolor=#ffccff><th colspan=3 align=center>";
	echo "Translation list for <b>" . $_GET['pagetotranslate'] . "</b>";
	echo "</th>";
	echo "<tr  bgcolor=#ffccff><th  bgcolor=#ccff99>code</th><th  bgcolor=#ccffff>english</th><th bgcolor=#ffffcc>", $rr->EnglishName, "<a href=adminwords.php?ShowLanguageStatus=", $IdLanguage, "> All</a></th>";
	for ($ii = 0; $ii < $count; $ii++) {
		echo "<tr>";
		echo "<td bgcolor=#ccff99>", $_SESSION['TranslationArray'][$ii], "</td>";
		$rword = LoadRow("select Sentence,updated from words where code='" . $_SESSION['TranslationArray'][$ii] . "' and IdLanguage=0");
		echo "<td bgcolor=#ccffff>";
		if (isset ($rword->Sentence)) {
			echo $rword->Sentence;
		}
		//		echo "<br><a href=adminwords.php?code=",$_SESSION['TranslationArray'][$ii],"&IdLanguage=0>edit</a>" ;
		echo "</td>";
		$rr = LoadRow("select id as idword,updated,Sentence from words where code='" . $_SESSION['TranslationArray'][$ii] . "' and IdLanguage=" . $IdLanguage);
		if (isset ($rr->idword)) {
			if (strtotime($rword->updated) > strtotime($rr->updated)) { // if obsolete
				echo "<td bgcolor=#ffccff>";
				if (isset ($rr->Sentence))
					echo $rr->Sentence;
				echo "<br><a href=adminwords.php?code=", $_SESSION['TranslationArray'][$ii], "&idword=", $rr->idword, ">edit</a> ";
				echo "\n<table  style=\"display:inline\"><tr><td bgcolor=#ff3333>obsolete</td></table>\n";
			} else {
				echo "<td bgcolor=#ffffcc>";
				if (isset ($rr->Sentence))
					echo $rr->Sentence;
				echo "<br><a href=adminwords.php?code=", $_SESSION['TranslationArray'][$ii], "&idword=", $rr->idword, ">edit</a> ";
			}
		} else {
			echo "<td bgcolor=white align=center>";
			echo "<br><a href=adminwords.php?code=", $_SESSION['TranslationArray'][$ii], "&IdLanguage=", $IdLanguage, ">";
			echo "\nADD\n";
			echo "</a>";
		}
		echo "</td>";
	}

	echo "</table>\n";
} // end if it was a show translation on page request

// Show a whole language status
if (isset ($_GET['ShowLanguageStatus'])) {

	$onlymissing = false;
	if (isset ($_GET['onlymissing'])) {
		$onlymissing = true;
	} else {
		$r1e = LoadRow("select count(*) as cnt from words where IdLanguage=0  and donottranslate!='yes'");
		$rXX = LoadRow("select count(*) as cnt from words where IdLanguage=" . $IdLanguage);
		$PercentAchieved = sprintf("%01.1f", ($rXX->cnt / $r1e->cnt) * 100) . "% achieved";
	}

	$IdLanguage = $_GET['ShowLanguageStatus'];
	$rlang = LoadRow("select * from languages where id=" . $IdLanguage);
	$qryEnglish = sql_query("select * from words where IdLanguage=0");
	echo "\n<table cellpadding=3 width=100%><tr bgcolor=#ffccff><th colspan=3 align=center>";
	echo "Translation list for <b>" . $rlang->EnglishName . "</b> " . $PercentAchieved;
	echo "</th>";
	echo "<tr  bgcolor=#ffccff><th  bgcolor=#ccff99>code</th><th  bgcolor=#ccffff>english</th><th bgcolor=#ffffcc>", $rlang->EnglishName, "</th>";
	while ($rEnglish = mysql_fetch_object($qryEnglish)) {
		$rr = LoadRow("select id as idword,updated,Sentence from words where code='" . $rEnglish->code . "' and IdLanguage=" . $IdLanguage);
		if ((isset ($rr->idword)) and ($onlymissing))
			continue;

		echo "<tr>";
		echo "<td bgcolor=#ccff99>", $rEnglish->code;
		if (HasRight("Grep")) {
			echo " <a href=\"admingrep.php?action=grep&submit=find&s2=ww&s1=" . $rEnglish->code . "&scope=layout/*;*;lib/*\">grep</a>";
		}
		if ($rEnglish->Description != "") {
			echo "<p style=\"font-size:11px; color:gray;\">", $rEnglish->Description, "</p>";
		}
		echo "</td>";
		$rword = LoadRow("select Sentence,updated from words where id=" . $rEnglish->id);
		echo "<td bgcolor=#ccffff>";
		if (isset ($rword->Sentence)) {
			echo $rword->Sentence;
		}
		echo "</td>";
		if (isset ($rr->idword)) {
			if (strtotime($rword->updated) > strtotime($rr->updated)) { // if obsolete
				echo "<td bgcolor=#ffccff>";
				if (isset ($rr->Sentence))
					echo $rr->Sentence;
				echo "<br><a href=adminwords.php?code=", $rEnglish->code, "&idword=", $rr->idword, ">edit</a> ";
				echo "\n<table  style=\"display:inline\"><tr><td bgcolor=#ff3333>obsolete</td></table>\n";
			} else {
				echo "<td bgcolor=#ffffcc>";
				if (isset ($rr->Sentence))
					echo $rr->Sentence;
				echo "<br><a href=adminwords.php?code=", $rEnglish->code, "&idword=", $rr->idword, ">edit</a> ";
			}
		} else {
			echo "<td bgcolor=white align=center>";
			echo "<br><a href=adminwords.php?code=", $rEnglish->code, "&IdLanguage=", $IdLanguage, ">";
			echo "\nADD\n";
			echo "</a>";
		}
		echo "</td>";
	}

	echo "</table>\n";
} // end of show a whole language

if ((isset ($_POST['DOACTION'])) and ($_POST['DOACTION'] == 'Delete')) {
	$rlang = LoadRow("select id as IdLanguage,ShortCode from languages where ShortCode='" . $_POST['lang'] . "'");

	echo "request delete for $code<br>";
	if (isset ($_POST['idword'])) {
		$rToDelete = LoadRow("select * from words where id=" . $_POST['idword']);
	} else {
		$rToDelete = LoadRow("select * from words where IdLanguage=" . $rlang->IdLanguage . " and code='" . $code . "'");
	}
	if (isset ($rToDelete->id)) {
		$str = "delete from words where id=" . $rToDelete->id;
		sql_query($str);
		$ss = "word #" . $rToDelete->id . " (" . $rToDelete->code . ") deleted";
		echo "<h2>", $ss, "</h3>";
		LogStr($ss, "AdminWord");
	}
}

// If it was a find word request
if ((isset ($_POST['DOACTION'])) and ($_POST['DOACTION'] == 'Find')) {
	$rlang = LoadRow("select id as IdLanguage,ShortCode from languages where ShortCode='" . $_POST['lang'] . "'");
	$where = "";
	if ($_POST['code'] != "") {
		if ($where != "")
			$where = $where . " and ";
		$where .= " code like '%" . $_POST['code'] . "%'";
	}
	if ($_POST['lang'] != "") {
		if ($where != "")
			$where = $where . " and ";
		$where .= " IdLanguage =" . $rlang->IdLanguage;
	}
	if ($_POST['Sentence'] != "") {
		if ($where != "")
			$where = $where . " and ";
		$where .= " Sentence like '%" . $_POST['Sentence'] . "%'";
	}

	$str = "select * from words where" . $where . " order by id desc";
	$qry = sql_query($str) or die("error " . $str);
	echo "\n<table>\n";
	$coutfind = 0;
	while ($rr = mysql_fetch_object($qry)) {
		if ($countfind == 0)
			echo "<tr align=left><th>id</th><th>code</th><th>Sentence</th><th>langue</th>\n";
		$countfind++;
		echo "<tr align=left><td><a href=\"" . $_SERVER['PHP_SELF'] . "?idword=$rr->id\">$rr->id</a>";
		echo "<td>$rr->code</td>";
		echo "<td>$rr->Sentence</td>";
		echo "<td>$rr->IdLanguage</td>\n";
	}
	echo "</table>\n";
	if ($countfind == 0)
		echo "<h3><font color=red>", $where, " Not found</font></h3>\n";
	//  include "layout/footer.php" ;
}

// If it was a request for insert or update
if ((isset ($_POST['DOACTION'])) and ($_POST['DOACTION'] == "submit") and ($_POST['Sentence'] != "") and ($_POST['lang'] != "")) {
	if (isset ($_POST['lang'])) {
		if (is_numeric($_POST['lang']))
			$rlang = LoadRow("select id as IdLanguage ,ShortCode from languages where id=" .
			$_POST['lang']);
		else
			$rlang = LoadRow("select id as IdLanguage ,ShortCode from languages where ShortCode='" .
			$_POST['lang'] . "'");
	} else {
		$rlang = LoadRow("select id as IdLanguage ,ShortCode from languages where id='" . $_SESSION['IdLanguage'] . "'");
	}
	$rw = LoadRow("select * from words where IdLanguage=" . $rlang->IdLanguage . " and code='" . $_POST['code'] . "'");
	if ($rw)
		$id = $rw->id;

	if ((HasRight("Words", $_POST['lang'])) or (HasRight("Words", "\"All\""))) { // If has rights for updating/inserting in this language

		if ((isset ($id)) and ($id > 0)) { // Update case
			$rw = LoadRow("select * from words where id=" . $id);

			MakeRevision($id, "words"); // create revision

			$descuptade = "";
			if (isset ($_POST['Description'])) { // if there is a description present it
				$descupdate = ",Description='" . addslashes($_POST['Description']) . "'";
			}
			if (isset($_POST["donottranslate"])) {
			  $donottranslate="donottranslate=".$donottranslate."," ;
			}
			$str = "update words set ".$donottranslate."code='" . $_POST['code'] . "',ShortCode='" . $rlang->ShortCode . "'" . $descupdate . ",IdLanguage=" . $rlang->IdLanguage . ",Sentence='" . addslashes($_POST['Sentence']) . "',updated=now(),IdMember=".$_SESSION['IdMember']." where id=$id";
			$qry = sql_query($str);
			if ($qry) {
				echo "update of <b>$code</b> successful<br>";
				LogStr("updating " . $code . " in " . $rlang->ShortCode, "AdminWord");

			} else {
				echo "failed for <b>$str</b><br>";
			}
		} // end of Update case
		else { // Insert case
			if (($code == "") or ($Sentence == "")) {
				echo "<h2><font color=red>can't insert if they are empty fields</font></h2>";
			} else {
				$str = "insert into words(code,ShortCode,IdLanguage,Sentence,updated,IdMember) values('" . $code . "','" . $rlang->ShortCode . "'," . $rlang->IdLanguage . ",'" . addslashes($Sentence) . "',now(),".$_SESSION['IdMember'].")";
				$qry = sql_query($str);
				if ($qry) {
					echo "<b>$code</b> added successfully<br>";
					LogStr("inserting " . $code . " in " . $rlang->ShortCode, "AdminWord");
				} else {
					echo "failed for <font color=red><b>$str</b></font><br>";
				}
			}
		} // end of insert case
	} // end of if has rights for updating/inserting in this language
	else {
		echo "You miss Right Scope for <b>", "\"" . $_POST['lang'] . "\"", "</b><br>\n";
	}
}

if (isset ($_GET['idword']))
	$idword = $_GET['idword'];

$SentenceEnglish = "";
if ((isset ($idword)) and ($idword > 0)) {
	$rr = LoadRow("select * from words where id=" . $idword);
	$code = $rr->code;
	$lang = $rr->ShortCode;
	$Sentence = $rr->Sentence;
}
if ($code != "") {
	$rEnglish = LoadRow("select Sentence,Description from words where code='" . $code . "' and IdLanguage=0");
	if (isset ($rEnglish->Sentence)) {
		$SentenceEnglish = "<i>" . str_replace("\n","<br>",htmlentities($rEnglish->Sentence)) . "</i><br>";
		if ($rEnglish->Description != "") {
			$SentenceEnglish .= "<table><tr bgcolor=#c0c0c0><td>" . str_replace("\n","<br>",$rEnglish->Description) . "</td></table>";
		}

	}
}

echo "<br><form method=post>";
echo "<table width=90%>";
echo "<tr>";
echo "<td>code :</td><td><input name=code value=\"$code\">";
if (isset ($_GET['idword']))
	echo " (idword=$idword)";
echo "</td>";
echo "<tr><td colspan=2>&nbsp;</td>";
if ($RightLevel >= 10) { // Level 10 allow to change/set description
    echo "<tr>";
	if ($lang == "eng") {
   	   echo "<td width=15%>";
	   echo "Description :</td><td>", $SentenceEnglish;
	   echo "<textarea name=Description cols=60 rows=4 style=\"background-color: #ccccff;\">", $rEnglish->Description, "</textarea>" ;
	} 
	else {
	  echo "<td colspan=2>" ;
	}
    echo " translatable <select name=donotranslate style=\"display:inline\">" ;
    echo "<option value=yes" ;
    if ($rEnglish->donottranslate=="yes") echo " selected" ;
    echo ">yes</option>\n" ;
    echo "<option value=no" ;
    if ($rEnglish->donottranslate=="yes") echo " selected" ;
    echo ">no</option>\n" ;
    echo "</select>" ;
	echo "</td>";
}
else {
  echo "<input type=hidden name=donotranslate value=\"",$rEnglish->donottranslate,"\">" ;
}
echo "<tr>";
echo "<td width=15%>";
echo "Sentence :</td><td>", $SentenceEnglish,"<br>";
echo "<textarea name=Sentence cols=60 rows=4>", $Sentence, "</textarea></td>";
echo "<tr><td colspan=2>&nbsp;</td>";
echo "<tr>";
echo "<td>langue :</td><td><input name=lang value=\"$lang\"></td>";
echo "<tr><td colspan=2>&nbsp;</td>";
echo "<tr>";
echo "<td colspan=2 align=center><input type=submit name=DOACTION value='submit'>";
echo " <input type=submit name=DOACTION value='Find'>";
echo " \n<input type=submit name=DOACTION value=\"Delete\" onclick=\"confirm('Do you confirm this delete ?');\"></td>";
echo "</form>";

echo "</center>";
echo "					</div>"; // user-content

include "layout/footer.php";
?>
