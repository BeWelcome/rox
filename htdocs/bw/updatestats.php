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

// update stats perfome a periodic update of statistics
require_once "lib/init.php";
require_once "lib/FunctionsMessages.php";
require_once "layout/error.php";

if (IsLoggedIn()) {
	if (HasRight("Beta") <= 0) {
		echo "This requires the <b>Beta</b> right";
		exit (0);
	}
	$IdTriggerer = $_SESSION['IdMember'];
} else { // case not logged
	// todo check if not logged that this script is effectively runned by the cron
	$IdTriggerer = 0; /// todo here need to set the Bot id
	$_SESSION['IdMember'] = 0;
} // not logged

// Number of member
$rr = LoadRow("SELECT COUNT(*) AS cnt FROM members WHERE Status='Active'");
$NbActiveMembers=$rr->cnt;

// Number of member with at least one positive comment
//$rr=LoadRow("SELECT COUNT(*) as cnt from members,comments where Status='Active' and members.id=comments.IdToMember and FIND_IN_SET('ITrusthim',Lenght)");
$rr = LoadRow("SELECT COUNT(DISTINCT(members.id)) AS cnt FROM members,comments WHERE Status='Active' AND members.id=comments.IdToMember AND comments.Quality='Good'");
$NbMemberWithOneTrust=$rr->cnt;

$d1=GetParam("d1",strftime("%Y-%m-%d 00:00:00",mktime(0, 0, 0, date("m")  , date("d")-1, date("Y"))));
$d2=GetParam("d2",strftime("%Y-%m-%d 00:00:00",mktime(0, 0, 0, date("m")  , date("d"), date("Y")))); 

if (empty($_SYSHCVOL['ARCH_DB'])) {
	$_SYSHCVOL['ARCH_DB']="BW_ARCH" ; // This patch will be to remove when a fix will be found for $_SYSHCVOL jy 24/5/2008
}
// Number of member who have logged
$str="SELECT COUNT(distinct(members.id)) as cnt from members right join ".$_SYSHCVOL['ARCH_DB'].".logs on  members.id=".$_SYSHCVOL['ARCH_DB'].".logs.IdMember and ".$_SYSHCVOL['ARCH_DB'].".logs.type='Login' and ".$_SYSHCVOL['ARCH_DB'].".logs.created between '$d1' and '$d2' and ".$_SYSHCVOL['ARCH_DB'].".logs.Str like 'Successful login%' ";
$rr=LoadRow($str);
$NbMemberWhoLoggedToday=$rr->cnt;

$rr=LoadRow("SELECT COUNT(*) as cnt from messages where DateSent between '$d1' and '$d2' ");
$NbMessageSent=$rr->cnt;

// Number of message read
$rr=LoadRow("SELECT COUNT(*) as cnt from messages where WhenFirstRead between '$d1' and '$d2' ");
$NbMessageRead=$rr->cnt;



if ((IsLoggedIn()) or ((isset($showstats)) and ($showstats==true))) {

?><!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">
<html>
<head>
<title>Bewelcome Statistics</title>
</head>
<body>
<?php
	echo "Nb Active Members=",$NbActiveMembers,"<br />";
	echo "Nb Members With at least one positive comment=",$NbMemberWithOneTrust,"<br />";
	echo "<br />between $d1 and $d2<br />";
	echo " Nb Members who have Logged=",$NbMemberWhoLoggedToday,"<br />";
	echo " Nb Messages Read=",$NbMessageRead,"<br />";
	echo " Nb Messages Sent=",$NbMessageSent,"<br />";
	
	
// members per countries
	$str="select countries.Name as countryname,count(*) as cnt from members,countries,cities where members.Status='Active' and members.IdCity=cities.id and cities.IdCountry=countries.id group by countries.id order by cnt desc" ;
	$qry=sql_query($str) ;
	echo "<table><tr><th>Members by countries</th><th>",$NbActiveMembers,"</th>\n" ;
	while ($rr=mysql_fetch_object($qry)) {
				echo "<tr><td>",$rr->countryname,"</td><td>",$rr->cnt," (";
    		printf("%01.1f", ($rr->cnt / $NbActiveMembers) * 100);
		  	echo  "%)</td>\n";
	}
	echo "</table><br />" ;
	
	
// Language translated
  $rr=LoadRow("SELECT COUNT(*) as cnt from words where IdLanguage=0 and donottranslate!='yes'");
  $cnt=$rr->cnt;
  $str="SELECT COUNT(*) as cnt,EnglishName from words,languages where languages.id=words.IdLanguage and donottranslate!='yes' group by words.IdLanguage order by cnt DESC";
  $qry=sql_query($str);
	echo "<table><tr><th colspan=2>Percentage of translation for the ",$cnt," words to translate</th>\n" ;
  while ($rr=mysql_fetch_object($qry)) {
	    echo "<tr><td>",$rr->EnglishName,"</td><td>\n";
    	printf("%01.1f", ($rr->cnt / $cnt) * 100);
		  echo  "% achieved</td>\n";
  }
	echo "</table>\n";
	
// Members by sex
  $str="SELECT COUNT(*) as cnt,Gender from members where Status='Active' group by Gender";
  $qry=sql_query($str);
	echo "<table><tr><th colspan=2>Members by Gender</th>\n" ;
  while ($rr=mysql_fetch_object($qry)) {
	    echo "<tr><td>",$rr->Gender,"</td><td>\n";
    	printf("%01.1f", ($rr->cnt / $NbActiveMembers) * 100);
		  echo  "%</td>\n";
  }
	echo "</table>\n";

// Members by byear
  $str="SELECT COUNT(*) as cnt,YEAR(BirthDate) as byear,(YEAR(NOW())-YEAR(BirthDate)) as age from members where Status='Active' and YEAR(BirthDate)>1920 and YEAR(BirthDate)<YEAR(NOW()) group by YEAR(BirthDate) order by byear desc";
  $qry=sql_query($str);
	echo "<table><tr><th colspan=3>Members by approximative Age</th>\n" ;
  while ($rr=mysql_fetch_object($qry)) {
	    echo "<tr><td>",$rr->byear,"</td><td>\n";
			echo $rr->age,"yo","</td><td>";
    	printf("%01.1f", ($rr->cnt / $NbActiveMembers) * 100);
		  echo  "%</td>\n";
  }
	echo "</table>\n";

	
	echo "this is just a display, stats have not been updated";
}
elseif (!isset($showstats)) {
	$str="INSERT INTO stats ( id , created , NbActiveMembers , NbMessageSent , NbMessageRead , NbMemberWithOneTrust , NbMemberWhoLoggedToday )" ;
	$str.= "VALUES (NULL ,CURRENT_TIMESTAMP , $NbActiveMembers , $NbMessageSent , $NbMessageRead , $NbMemberWithOneTrust , $NbMemberWhoLoggedToday )";
	sql_query($str);
   
}
exit(0);
?>
