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
chdir("..") ;
require_once "lib/init.php";
$title = "Words management";
require_once "layout/menus.php";
require_once "lib/f_volunteer_boards.php" ;

function CheckRLang( $rlang )
{
  if (empty($rlang))
  {
    print_r($rlang);
    bw_error("rlang is empty.");
  }
  if (!isset($rlang->IdLanguage)||$rlang->IdLanguage<0)
  {
    print_r($rlang);echo "<br />" ;
    bw_error(" CheckRLang rlang->IdLanguage empty");
  }
  if (empty($rlang->ShortCode))
  {
    print_r($rlang);
    bw_error("rlang->ShortCode empty");
  }
}

MustLogIn(); // Needs to be logged

$lang = $_SESSION['lang']; // save session language
$_SESSION['lang'] = CV_def_lang;
$_SESSION['IdLanguage'] = 0; // force English for menu

require_once "layout/header.php";

Menu1("", "Admin Words"); // Displays the top menu

Menu2("main.php", "Admin Words"); // Displays the second menu

$_SESSION['lang'] = $lang; // restore session language
$rr = LoadRow("select * from languages where ShortCode='" . $lang . "'");
$ShortCode = $rr->ShortCode;
$_SESSION['IdLanguage'] = $IdLanguage = $rr->id;
$MenuAction  = "            <li><a href=\"http://www.bevolunteer.org/wiki/Adminwords\">Documentation</a></li>\n" ;
$MenuAction  = "            <li><a href=\"".bwlink("admin/adminwords.php")."\">Admin word</a></li>\n";
$MenuAction .= "            <li><a href=\"".bwlink("importantwords.php")."\">Important words</a></li>\n";
$MenuAction .= "            <li><a href=\"".bwlink("admin/adminwords.php?ShowLanguageStatus=". $rr->id)."\"> All in ". $rr->EnglishName. "</a></li>\n";
$MenuAction .= "            <li><a href=\"".bwlink("admin/adminwords.php?onlymissing&ShowLanguageStatus=". $rr->id)."\"> Only missing in ". $rr->EnglishName. "</a></li>\n";
$MenuAction .= "            <li><a href=\"".bwlink("admin/adminwords.php?onlyobsolete&ShowLanguageStatus=". $rr->id)."\"> Only obsolete in ". $rr->EnglishName. "</a></li>\n";
$MenuAction .= "            <li><a href=\"".bwlink("admin/adminwords.php?showstats")."\">Show stats</a></li>\n";
$MenuAction .= "            <li><a href=\"".bwlink("admin/adminwords.php?showmemcache")."\">Show memcache</a></li>\n";


function showPercentageAchieved($IdLanguage = null)
{
    $rr = LoadRow("SELECT COUNT(*) AS cnt FROM words WHERE IdLanguage=0 AND donottranslate!='yes'");
    $cnt = $rr->cnt;
    $str = "SELECT COUNT(*) AS cnt,EnglishName,TranslationPriority FROM words,languages WHERE languages.id=words.IdLanguage AND donottranslate!='yes'";
    if ($IdLanguage) {
        $str .= " AND languages.id = " . (int)$IdLanguage;
    }
    $str .= " GROUP BY words.IdLanguage ORDER BY cnt DESC";
    $qry=sql_query($str);
    echo "<table>\n";
    while ($rr=mysql_fetch_object($qry)) {
        echo "<tr><td>", $rr->EnglishName, "</td><td>\n";
        printf("%01.1f", ($rr->cnt / $cnt) * 100);
        echo  "% done</td>\n";
    }
    echo "</table>\n";
}

function showmemcache($IdLanguage = null) {
	
	echo "<h2>memcache statistics</h2>" ;
	echo "\$_SESSION[\"Param\"]->memcache=",$_SESSION["Param"]->memcache,"<br />\n" ;

	$memcache=new MemCache ;

	$memcache->connect('localhost',11211) or die ("adminword: Could not connect to memcache") ;

	$ServerStatus=$memcache->getServerStatus('localhost',11211) ;
	echo "Memcache server Status=",$ServerStatus,"<br />" ;


	

	$Stats=$memcache->getStats('maps') ;
	echo "\n<hr>Stats maps=<br/>\n" ;
	$v=var_export($Stats,true);
	echo str_replace("\n","<br>",$v) ;
	echo "<br />" ;
	
	$Stats=$memcache->getStats('items') ;
	echo "<hr>Stats items=<br/>\n" ;
	$v=var_export($Stats,true);
	$v=str_replace("\n","<br>\n",$v) ;
	$v=str_replace(" ","&nbsp;",$v) ;
	echo $v ;


	$Stats=$memcache->getStats('cachedump') ;
	echo "<hr>Stats cachedump=<br/>\n" ;
	$v=var_export($Stats,true);
	$v=str_replace("\n","<br>\n",$v) ;
	$v=str_replace(" ","&nbsp;",$v) ;
	echo $v ;

	
    require_once "layout/footer.php";
	exit(0);
	
} // end of showmemcache



DisplayHeaderShortUserContent("Admin Words",$MenuAction,""); // Display the header
ShowLeftColumn($MenuAction,VolMenu());

UpdateVolunteer_Board("translator_board") ;
DisplayVolunteer_Board("translator_board") ; 

$scope = RightScope('Words');
$RightLevel = HasRight('Words',$lang); // Check the rights

$scope = RightScope('Words');

echo "    <div id=\"col3\"> \n";
echo "      <div id=\"col3_content\" class=\"clearfix\"> \n";
echo "          <div class=\"info\">\n";
echo "            <h2>Your current language is ", " #", $rr->id, " (", $rr->EnglishName, ", ", $rr->ShortCode, ") your scope is for $scope </h2>\n";
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
  showPercentageAchieved();
}

// if it was a show translation on page request
if (isset ($_GET['showmemcache'])) {
  showmemcache();
}
//OMG, this file is in desperate need of mysql_real_escape_string

// If it was a find word request
if ((isset ($_POST['DOACTION'])) and ($_POST['DOACTION'] == 'Find')) {
  if (!empty($_POST['lang'])) {
     $rlang = LoadRow("SELECT id AS IdLanguage,ShortCode FROM languages WHERE ShortCode='" . $_POST['lang'] . "'");
     CheckRLang( $rlang );
  }
  $where = "";

  if (!empty($_POST['code'])) {
    if ($where != "")
      $where = $where . " and ";
    $where .= " code LIKE '%" . $_POST['code'] . "%'";
  }

  if (!empty($_POST['lang'])) {
    if ($where != "")
      $where = $where . " and ";
    $where .= " IdLanguage =" . $rlang->IdLanguage;
  }

  if (!empty($_POST['Sentence'])) {
    if ($where != "")
      $where = $where . " and ";
    $where .= " Sentence LIKE '%" . $_POST['Sentence'] . "%'";
  }

  $str = "SELECT * FROM words WHERE" . $where . " ORDER BY id DESC";
  $qry = sql_query($str) or die("error " . $str);
  echo "\n<table cellspacing=4>\n";
  $coutfind = 0;
  while ($rr = mysql_fetch_object($qry)) {
    if ($countfind == 0)
      echo "<tr align=left><th>code / Sentence</th><th>Desc</th>\n";
    $countfind++;
    $rEnglish=LoadRow("select * from words where code='".$rr->code."' and IdLanguage=0");
    echo "<tr align=left style=\"font-size:11px;\"><td width=\"50%\"><a href=\"" . $_SERVER['PHP_SELF'] . "?idword=$rr->id\" style=\"font-size:12px;\">",$rr->code," (#",$rr->id,")</a>";
    echo " ",LanguageName($rr->IdLanguage);
    echo "<br />";
    echo "$rr->Sentence</td>";
    echo "<td style=\"font-size:9px; color:gray;\">", $rEnglish->Description,"</td>\n";
  }
  echo "</table>\n";
  if ($countfind == 0)
    echo "<h3><font color=red>", $where, " Not found</font></h3>\n";
   require_once "layout/footer.php";
  exit(0);
} // end of Find


if ($RightLevel < 1) {
  echo "<div class=\"info highlight\">\n";
  echo "<h2>For this you need the <strong>Words</strong> rights for lang=<strong>$lang</strong>. Your scope is: $scope</h2>";
  echo "</div>" ;
  require_once "layout/footer.php";
  exit (0);
}

// if it was a show translation on page request
if (isset ($_GET['showtransarray'])) {

  $count = count($_SESSION['TranslationArray']);
  echo "\n<table cellpadding=3 width=100%><tr bgcolor='#ffccff'><th colspan=3 align=center>";
  echo "\nTranslation list for <strong>" . $_GET['pagetotranslate'] . "</strong>";
  echo "\n</th>";
  echo "\n<tr  bgcolor='#ffccff'><th bgcolor='#ccff99'>code</th><th  bgcolor='#ccffff'>English</th><th bgcolor='#ffffcc'>", $rr->EnglishName, "<a href=".bwlink("admin/adminwords.php?ShowLanguageStatus=$IdLanguage")."> All</a></th>";
  for ($ii = 0; $ii < $count; $ii++) {
    echo "<tr>";
    echo "<td bgcolor=#ccff99>", $_SESSION['TranslationArray'][$ii], "</td>";
    if (is_numeric($_SESSION['TranslationArray'][$ii])) {
       $rword = LoadRow("select Sentence,updated,donottranslate,TranslationPriority from words where id='" . $_SESSION['TranslationArray'][$ii] . "' and IdLanguage=0");
    }
    else {
       $rword = LoadRow("select Sentence,updated,donottranslate,TranslationPriority from words where code='" . $_SESSION['TranslationArray'][$ii] . "' and IdLanguage=0");
    }
    echo "<td bgcolor=#ccffff>";
    if (isset ($rword->Sentence)) {
      echo $rword->Sentence;
    }
    //    echo "<br /><a href=admin/adminwords.php?code=",$_SESSION['TranslationArray'][$ii],"&IdLanguage=0>edit</a>";
    echo "</td>";
    $rr = LoadRow("select id as idword,updated,Sentence,TranslationPriority from words where code='" . $_SESSION['TranslationArray'][$ii] . "' and IdLanguage=" . $IdLanguage);
    if (isset ($rr->idword)) {
      if (strtotime($rword->updated) > strtotime($rr->updated)) { // if obsolete
        echo "<td bgcolor=#ffccff>";
        if (isset ($rr->Sentence))
          echo $rr->Sentence;
        echo "<br /><a href=\"".bwlink("admin/adminwords.php?code=". $_SESSION['TranslationArray'][$ii]. "&idword=". $rr->idword). "\">edit</a> ";
        echo "\n<table  style=\"display:inline\"><tr><td bgcolor=#ff3333>obsolete</td></tr></table>\n";
      } else {
        echo "<td bgcolor='#ffffcc'>";
        if (isset ($rr->Sentence))
          echo $rr->Sentence;
        echo "<br /><a href=\"".bwlink("admin/adminwords.php?code=". $_SESSION['TranslationArray'][$ii]. "&idword=". $rr->idword). "\">edit</a> ";
      }
    } else {
      echo "<td bgcolor=white align=center>";
      if ($rword->donottranslate=="no") {
         echo "<br /><a href=\"".bwlink("admin/adminwords.php?code=". $_SESSION['TranslationArray'][$ii]. "&IdLanguage=". $IdLanguage). "\">";
         echo "\nADD\n";
         echo "</a>";
      }
      else {
          echo "<strong>not translatable</strong>" ;
      }
			echo "Translation priority=",$rword->TranslationPriority ;
    }
    echo "</td></tr>";
  }

  echo "</table>\n";
} // end if it was a show translation on page request

// Show a whole language status
if (isset ($_GET['ShowLanguageStatus'])) {


  $onlymissing = false;
  $onlyobsolete = false;
  if (isset ($_GET['onlymissing'])) {
    $onlymissing = true;
  } else if (isset ($_GET['onlyobsolete'])) {
    $onlyobsolete = true;
  } else {
    $r1e = LoadRow("select count(*) as cnt from words where IdLanguage=0  and donottranslate!='yes'");
    $rXX = LoadRow("select count(w1.code) as cnt from words as w1 right join words as w2 on w2.code=w1.code and w2.IdLanguage=0 and w2.donottranslate!='yes' where w1.IdLanguage=" . $IdLanguage);
    $PercentAchieved = sprintf("%01.1f", ($rXX->cnt / $r1e->cnt) * 100) . "% achieved";
  }

  //without the int any translator can do anything with the database!
  $IdLanguage = (int)$_GET['ShowLanguageStatus'];
  $ssrlang="SELECT *,id AS IdLanguage FROM languages WHERE id = " . $IdLanguage;
  //  echo "\$ssrlang=",$ssrlang,"<br />" ; ;
  $rlang = LoadRow($ssrlang);
  CheckRLang($rlang);

  showPercentageAchieved($IdLanguage);

  echo "\n<table cellpadding=3 width=100%><tr bgcolor=#ffccff><th colspan=3 align=center>\n";
  echo "Translation list for <strong>" . $rlang->EnglishName . "</strong> " . $PercentAchieved;
  echo "</th>";
  echo "<tr  bgcolor='#ffccff'><th  bgcolor=#ccff99>code</th><th  bgcolor=#ccffff>English</th><th bgcolor=#ffffcc>", $rlang->EnglishName, "</th>";
	if (($onlyobsolete)or($onlymissing)) {
  		 $qryEnglish = sql_query("select * from words where IdLanguage=0 order by TranslationPriority,id asc");
	}
	else {
  		 $qryEnglish = sql_query("select * from words where IdLanguage=0");
	}
  while ($rEnglish = mysql_fetch_object($qryEnglish)) {
    $rr = LoadRow("select id as idword,updated,Sentence,IdMember,TranslationPriority from words where code='" . $rEnglish->code . "' and IdLanguage=" . $IdLanguage);
    $rword = LoadRow("select Sentence,updated,donottranslate,TranslationPriority from words where id=" . $rEnglish->id);
    if (((isset ($rr->idword)) and ($onlymissing)) or ($rEnglish->donottranslate=='yes'))
      continue;
    if ($onlyobsolete) {
       if (!isset ($rr->idword)) continue; // skip non existing words
       if (strtotime($rword->updated) <= strtotime($rr->updated))      continue; // skip non obsolete words
    }

    echo "<tr>\n";
    echo "<td bgcolor=#ccff99>", $rEnglish->code;
    if (HasRight("Grep")) {
      echo " <a href=\"".bwlink("admin/admingrep.php?action=grep&submit=find&s2=ww&s1=" . $rEnglish->code . "&scope=layout/*;*;lib/*")."\">grep</a>";
    }
    echo "\n<br /><table  style=\"display:inline;\"><tr><td style=\"color:#3300ff;\">Last update ",fSince($rEnglish->updated)," ",fUserName($rEnglish->IdMember),"</td></table>\n";
    if ($rEnglish->Description != "") {
      echo "<p style=\"font-size:11px; color:gray;\">", $rEnglish->Description, "</p>\n";
    }
    if (IsAdmin()) {
       if ($rEnglish->donnottranslate=="yes") {
           echo "<strong>not translatable</strong>" ;
       }
       else {
           echo " translatable" ;
       }
    }
    echo "</td>\n";
    echo "<td bgcolor=#ccffff>";
    if (isset ($rword->Sentence)) {
      echo $rword->Sentence;
    }
    echo "</td>\n";
    if (isset ($rr->idword)) {
      if (strtotime($rword->updated) > strtotime($rr->updated)) { // if obsolete
        echo "<td bgcolor=#ffccff>";
        if (isset ($rr->Sentence))
          echo $rr->Sentence;
        echo "<br /><a href=\"".bwlink("admin/adminwords.php?code=". $rEnglish->code. "&idword=". $rr->idword). "\">edit</a> ";
        echo "\n<table  style=\"display:inline\"><tr><td bgcolor=#ff3333>update needed?</td></table>\n";
        echo "\n<table  style=\"display:inline;color:#3300ff;\"><tr><td>Last update ",fSince($rr->updated)," ",fUserName($rr->IdMember),"</td></table>\n";
      } else {
        echo "<td bgcolor=#ffffcc>";
        if (isset ($rr->Sentence))
          echo $rr->Sentence;
        echo "<br /><a href=\"".bwlink("admin/adminwords.php?code=". $rEnglish->code. "&idword=". $rr->idword). "\">edit</a> ";
        echo "\n<table  style=\"display:inline;color:#3300ff;\"><tr><td>Last update ",fSince($rr->updated)," ",fUserName($rr->IdMember),"</td></table>\n";
      }
    } else {
      echo "<td bgcolor=white align=center>";
      echo "<br /><a href=\"".bwlink("admin/adminwords.php?code=". $rEnglish->code. "&IdLanguage=". $IdLanguage). "\">";
      echo "\nADD\n";
      echo "</a>\n";
    }
    echo "</td>\n";
  }

  echo "</table>\n";
} // end of show a whole language

if ((isset ($_POST['DOACTION'])) and ($_POST['DOACTION'] == 'Delete')) {
  $rlang = LoadRow("select id as IdLanguage,ShortCode,EnglishName from languages where ShortCode='" . $_POST['lang'] . "'");
  CheckRLang( $rlang );

  echo "request delete for $code<br />";
  if (isset ($_POST['idword'])) {
    $rToDelete = LoadRow("select * from words where id=" . $_POST['idword']);
  } else {
    $rToDelete = LoadRow("select * from words where IdLanguage=" . $rlang->IdLanguage . " and code='" . $code . "'");
  }
  if (isset ($rToDelete->id)) {
    $str = "delete from words where id=" . $rToDelete->id;
    sql_query($str);
    $ss = "word #" . $rToDelete->id . " (" . $rToDelete->code . ") deleted";
    echo "<h2>", $ss, "</h3>\n";
    LogStr($ss, "AdminWord");
  }
} // end of delete


// If it was a request for insert or update
if ((isset ($_POST['DOACTION'])) and (strtolower($_POST['DOACTION']) == "submit") and ($_POST['Sentence'] != "") and ($_POST['lang'] != "")) {
  if (isset ($_POST['lang'])) {
    if (is_numeric($_POST['lang']))
        $rlang = LoadRow("SELECT id AS IdLanguage, ShortCode FROM languages WHERE id=" . $_POST['lang']);
    else
      $rlang = LoadRow("SELECT id AS IdLanguage, ShortCode FROM languages WHERE ShortCode='" . $_POST['lang'] . "'");
  } else {
    $rlang = LoadRow("select id as IdLanguage ,ShortCode from languages where id='" . $_SESSION['IdLanguage'] . "'");
  }

  CheckRLang( $rlang );

  $rw = LoadRow("SELECT * FROM words WHERE IdLanguage=" . $rlang->IdLanguage . " AND code='" . $_POST['code'] . "'");
  if ($rw)
    $id = $rw->id;

  if ((HasRight("Words", $_POST['lang'])) or (HasRight("Words", "\"All\""))) { // If has rights for updating/inserting in this language

    if ((isset ($id)) and ($id > 0)) { // Update case
      $rw = LoadRow("select * from words where id=" . $id);

      MakeRevision($id, "words"); // create revision

      $descuptade = "";
      if (isset ($_POST['Description'])) { // if there is a description present it
        $descupdate = ",Description='" . mysql_real_escape_string($_POST['Description']) . "'";
      }
      if (isset($_POST["donottranslate"])) {
        $donottranslate="donottranslate='".$_POST["donottranslate"]."',";
      }
			
      if (isset($_POST["TranslationPriority"])) {
        $TranslationPriority="TranslationPriority='".$_POST["TranslationPriority"]."',";
      }
			
			
      $str = "update words set ".$donottranslate.$TranslationPriority."code='" . $_POST['code'] . "',ShortCode='" . $rlang->ShortCode . "'" . $descupdate . ",IdLanguage=" . $rlang->IdLanguage . ",Sentence='" . mysql_real_escape_string($_POST['Sentence']) . "',updated=now(),IdMember=".$_SESSION['IdMember']." where id=$id";
      $qry = sql_query($str);
      if ($qry) {
        echo "update of <strong>$code</strong> successful<br />";
        LogStr("updating " . $code . " in " . $rlang->ShortCode, "AdminWord");

      } else {
        echo "failed for <strong>$str</strong><br />";
      }
    } // end of Update case
    else { // Insert case
      if (($code == "") or ($Sentence == "")) {
        echo "<h2><font color=red>can't insert if they are empty fields</font></h2>";
      } else {
        $str = "insert into words(code,ShortCode,IdLanguage,Sentence,updated,IdMember,created) values('" . $code . "','" . $rlang->ShortCode . "'," . $rlang->IdLanguage . ",'" . mysql_real_escape_string($Sentence) . "',now(),".$_SESSION['IdMember'].",now())";
        $qry = sql_query($str);
        $IdLastWord=mysql_insert_id();
        if ($qry) {
          echo "<strong>$code</strong> added successfully  (IdWord=#$IdLastWord)<br />";
          LogStr("inserting " . $code . " in " . $rlang->ShortCode, "AdminWord");
          if (($RightLevel>=10)and (!empty($_POST["Description"])) and ($IdLanguage==0)) {
             $str = "update words set Description='".mysql_real_escape_string($_POST["Description"])."' where id=".$IdLastWord;
             sql_query($str);
          }
        } else {
          echo "failed for <font color=red><strong>$str</strong></font><br />";
        }
      }
    } // end of insert case
  } // end of if has rights for updating/inserting in this language
  else {
    echo "You miss Right Scope for <strong>", "\"" . $_POST['lang'] . "\"", "</strong><br />\n";
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
  $rEnglish = LoadRow("select Sentence,Description,donottranslate,TranslationPriority from words where code='" . $code . "' and IdLanguage=0");
  if (isset ($rEnglish->Sentence)) {
    $SentenceEnglish = "<em>" . str_replace("\n","<br />",htmlentities($rEnglish->Sentence)) . "</em><br />";
    if ($rEnglish->Description != "") {
      $SentenceEnglish .= "<table><tr><td>" . str_replace("\n","<br />",$rEnglish->Description) . "</td></table>";
    }

  }
}

?>
<form method="post">
<table class="admin" border="0"><tr>
<td class="label"><label for="code">Code:</label> </td>
<td><input name="code" id="code" value="<?php echo $code ?>">
<?php
if (isset ($_GET['idword']))
  echo " (idword=$idword)";
if ($RightLevel >= 10) { // Level 10 allow to change/set description
    echo "&nbsp;&nbsp; <select name=\"donottranslate\">";
    echo "<option value=\"no\"";
    if ($rEnglish->donottranslate=="no") echo " selected";
    echo ">translatable</option>\n";
    echo "<option value=\"yes\"";
    if ($rEnglish->donottranslate=="yes") echo " selected";
    echo ">not translatable</option>\n";
    echo "</select>\n";

    echo "&nbsp;&nbsp;Translation Priority<input type=\"text\" name=\"TranslationPriority\" value=\"".$rEnglish->TranslationPriority."\">";
} else {
  if ($rEnglish->donottranslate=="yes") echo "<span style=\"background-color: #ffff33\">Do not translate Priority=",$rEnglish->TranslationPriority."</span> " ;
}
echo "</td>\n";
echo "                </tr>\n";
$NbRow=4;
  if ($lang == CV_def_lang) {
    echo "<tr><td class=\"label\">Description: </td>\n";
    echo "<td><em>\n", $rEnglish->Description,"</em><br />";
    if ($RightLevel >= 10) { // Level 10 allow to change/set description
      echo "                    <textarea name=\"Description\" cols=\"60\" class=\"long\" rows=\"4\">", $rEnglish->Description, "</textarea>\n";
}
    echo "                  </td></tr>\n";
  } else {
echo "                <tr>\n";
echo "                  <td class=\"label\" >Description: </td>\n";
echo "                  <td><em>", str_replace("\n","<br />",$rEnglish->Description), " </em></td>\n";
echo "                </tr>\n";
}
echo "                  <td class=\"label\" >English source: </td>\n";
$tagold = array("&lt;", "&gt;");
$tagnew = array("<font color=\"#ff8800\">&lt;", "&gt;</font>");
echo "                  <td>", str_replace("\n","<br />",str_replace($tagold,$tagnew,htmlentities($rEnglish->Sentence))), " </td>\n";
?>
</tr>
<tr>
<td class="label"><label for="Sentence">Translation:</label> </td>
<td><textarea name="Sentence" id="Sentence" class="long" cols="<?php
$NbRows = 3*((substr_count($SentenceEnglish, '\n')+substr_count($SentenceEnglish, '<br />')+substr_count($SentenceEnglish, '<br />'))+1);
if (IsAdmin()) echo "60" ;
else echo "40" ;
echo "\" rows=",$NbRows,">", $Sentence, "</textarea></td>\n";
?>
  </tr>
  <tr>
    <td class="label"><label for="lang">Language:</label> </td>
    <td><input name="lang" id="lang" value="<?php echo $lang ?>"></td>
  </tr>
  <tr>
    <td colspan="2" align="center">
      <input class="button" type="submit" id="submit" name="DOACTION" value="Submit">
      <input class="button" type="submit" id="submit" name="DOACTION" value="Find">
      <input class="button" type="submit" id="submit" name="DOACTION" value="Delete" onclick="confirm('Are you sure you want to delete this?');">
    </td>
  </tr>
</table>
</form>
</div>
<?php require_once "layout/footer.php"; ?>
