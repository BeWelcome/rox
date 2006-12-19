<?php
include "lib/dbaccess.php" ;
require_once "lib/FunctionsTools.php" ;
$title="words managment" ;
include "layout/menus.php" ;
include "layout/header.php" ;


$lang=$_SESSION['lang'] ; // save session language
$_SESSION['lang']="eng" ;$_SESSION['IdLanguage']=0 ; // force english for menu
mainmenu("adminwords.php") ;
echo "<H2>$title</H2>" ;
echo "<center>" ;


$_SESSION['lang']=$lang ; // restore session language
$rr=LoadRow("select * from languages where ShortCode='".$lang."'") ;
$ShortCode=$rr->ShortCode ;
$_SESSION['IdLanguage']=$IdLanguage=$rr->id ;

echo "<h2>Your current language is "," #",$rr->id,"(",$rr->EnglishName,",",$rr->ShortCode,")</h2>" ;
$Sentence="" ;
$code="" ;
if (isset($_GET['code'])) $code=$_GET['code'] ;
if (isset($_GET['Sentence'])) $Sentence=$_GET['Sentence'] ;
if ((isset($_GET['id']))and($_GET['id']!="")) $id=$_GET['id'] ;
if (isset($_GET['lang'])) $lang=$_GET['lang'] ;

if (isset($_POST['code'])) $code=$_POST['code'] ;
if (isset($_POST['Sentence'])) $Sentence=$_POST['Sentence'] ;
if ((isset($_POST['id']))and($_POST['id']!="")) $id=$_POST['id'] ;
if (isset($_POST['lang'])) $lang=$_POST['lang'] ;

// if it was a show translation on page request
if (isset($_GET['showtransarray'])) {
  $count=count($_SESSION['TranslationArray']) ;
	echo "<table cellpadding=3><tr bgcolor=#ffccff><th colspan=3 align=center>" ;
  echo "Translation list for <b>".$_GET['pagetotranslate']."</b>" ;
	echo "</th>" ;
	echo "<tr  bgcolor=#ffccff><th  bgcolor=#ccff99>code</th><th  bgcolor=#ccffff>english</th><th bgcolor=#ffffcc>",$rr->EnglishName,"</th>" ;
	for ($ii=0;$ii<$count;$ii++) {
	  echo "<tr>" ;
		echo "<td bgcolor=#ccff99>",$_SESSION['TranslationArray'][$ii],"</td>" ;
		$rword=LoadRow("select Sentence,updated from words where code='".$_SESSION['TranslationArray'][$ii]."' and IdLanguage=0" ) ;
		echo "<td bgcolor=#ccffff>";
		if (isset($rword->Sentence)) {
		  echo $rword->Sentence ;
		}
//		echo "<br><a href=adminwords.php?code=",$_SESSION['TranslationArray'][$ii],"&IdLanguage=0>edit</a>" ;
		echo "</td>" ;
		echo "<td bgcolor=#ffffcc>";
		$rr=LoadRow("select id as idword,updated,Sentence from words where code='".$_SESSION['TranslationArray'][$ii]."' and IdLanguage=".$IdLanguage ) ;
		if (isset($rr->Sentence)) {
		  echo $rr->Sentence ;
		}
		if (isset($rr->idword)) {
		  if (strtotime($rword->updated)>strtotime($rr->updated)) { // if obsolete
		    echo "<br><a href=adminwords.php?code=",$_SESSION['TranslationArray'][$ii],"&idword=",$rr->idword,">edit</a> " ;
			  echo "\n<table><tr><td bgcolor=#ff3333>obsolete</td></table>\n" ;
			}
			else {
		    echo "<br><a href=adminwords.php?code=",$_SESSION['TranslationArray'][$ii],"&idword=",$rr->idword,">edit</a> ",$rword->updated," ",$rr->updated ;
			}
		}
		else {
		  echo "<br><a href=adminwords.php?code=",$_SESSION['TranslationArray'][$ii],"&IdLanguage=",$IdLanguage,">"  ;
			echo "\nADD\n" ;
			echo "</a>" ;
		}
		echo "</td>" ;
	}
	
	echo "</table>" ;
}

// If it was a find word request
if ((isset($_POST['submit']))and($_POST['submit']=='Find')) {
  $rlang=LoadRow("select id as IdLanguage,ShortCode from languages where ShortCode='".$_POST['lang']."'") ;
  $where="" ;
  if ($_POST['code']!="") {
	  if ($where!="") $where=$where." and " ;
	  $where.=" code like '%".$_POST['code']."%'" ;
	} 
  if ($_POST['lang']!="") {
	  if ($where!="") $where=$where." and " ;
	  $where.=" IdLanguage =".$rlang->IdLanguage ;
	} 
  if ($_POST['Sentence']!="") {
	  if ($where!="") $where=$where." and " ;
	  $where.=" Sentence like '%".$_POST['Sentence']."%'" ;
	} 
	
	$str="select * from words where".$where." order by id desc" ;
	echo "str=$str<br>" ;
	$qry=sql_query($str) or die("error ".$str) ;
	echo "\n<table>\n" ;
	echo "<tr align=left><th>id</th><th>code</th><th>Sentence</th><th>langue</th>\n" ;
	while ($rr=mysql_fetch_object($qry)) {
	  echo "<tr align=left><td><a href=\"".$_SERVER['PHP_SELF']."?idword=$rr->id\">$rr->id</a>" ;
		echo "<td>$rr->code</td>" ;
		echo "<td>$rr->Sentence</td>" ;
		echo "<td>$rr->IdLanguage</td>\n" ;
	}
	echo "</table>\n" ;
//  include "layout/footer.php" ;
}


// If it was a request for insert or update
if ((isset($_POST['submit']))and($_POST['submit']=="submit")and ($_POST['Sentence']!="")) {
  if (isset($_POST['lang'])) {
	   if (is_numeric($_POST['lang'])) 
       $rlang=LoadRow("select id as IdLanguage ,ShortCode from languages where id=".$_POST['lang']) ;
		 else
       $rlang=LoadRow("select id as IdLanguage ,ShortCode from languages where ShortCode='".$_POST['lang']."'") ;
	}
	else {
    $rlang=LoadRow("select id as IdLanguage ,ShortCode from languages where id='".$_SESSION['IdLanguage']."'") ;
	}
  $rw=LoadRow("select * from words where IdLanguage=".$rlang->IdLanguage." and code='".$_POST['code']."'") ;
	if ($rw) $id=$rw->id ;
  if ( (isset($id)) and ($id>0) ) {
	  $rw=LoadRow("select * from words where id=".$id) ;
		
    MakeRevision($id,"words") ; // create revision

		$str="update words set code='".$_POST['code']."',ShortCode='".$rlang->ShortCode."',IdLanguage=".$rlang->IdLanguage.",Sentence='".addslashes($_POST['Sentence'])."',updated=now() where id=$id" ;
		$qry=sql_query($str) ;
		if ($qry) {
		  echo "update of <b>$code</b> successful<br>" ;
		}
		else {
		  echo "failed for <b>$str</b><br>" ;
		}
	}
	else {
		$str="insert into words(code,ShortCode,IdLanguage,Sentence,updated) values('".$_POST['code']."','".$rlang->ShortCode."',".$rlang->IdLanguage.",'".addslashes($_POST['Sentence'])."',now())" ;
		$qry=sql_query($str) ;
		if ($qry) {
		  echo "<b>$code</b> added successfully<br>" ;
		}
		else {
		  echo "failed for <b>$str</b><br>" ;
		}
	}
}


  if (isset($_GET['idword'])) $idword=$_GET['idword'] ;

	$SentenceEnglish="" ;
  if ((isset($idword)) and ($idword>0)) {
	  $rr=LoadRow("select * from words where id=".$idword) ;
		$code=$rr->code ;
		$lang=$rr->ShortCode ;
		$Sentence=$rr->Sentence ;
	}
	if ($code!="") {
	  $rEnglish=LoadRow("select Sentence from words where code='".$code."' and IdLanguage=0") ;
		if (isset($rEnglish->Sentence)) {
	    $SentenceEnglish="<i>".$rEnglish->Sentence."</i><br>" ;
		}
	}
	
  echo "<br><form method=post>" ;
  echo "<table width=90%>" ;
  echo "<tr>" ;
  echo "<td>code :</td><td><input name=code value=\"$code\">" ;
  if (isset($_GET['idword'])) echo " (idword=$idword)";
	echo "</td>" ;
  echo "<tr><td colspan=2>&nbsp;</td>" ;
  echo "<tr>" ;
  echo "<td width=15%>Sentence :</td><td>",$SentenceEnglish ;
	echo "<textarea name=Sentence cols=60 rows=4>",$Sentence,"</textarea></td>" ;
  echo "<tr><td colspan=2>&nbsp;</td>" ;
  echo "<tr>" ;
  echo "<td>langue :</td><td><input name=lang value=\"$lang\"></td>" ;
  echo "<tr><td colspan=2>&nbsp;</td>" ;
  echo "<tr>" ;
  echo "<td colspan=2 align=center><input type=submit name=submit value='submit'> <input type=submit name=submit value='Find'></td>" ;
  echo "</form>" ;


echo "</center>" ;

include "layout/footer.php" ;
?>
