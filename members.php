<?php
require_once "lib/init.php";
require_once "layout/error.php";

switch (GetParam("action")) {

}

$limitcount=Getparam("limitcount",10); // Number of records per page

//****************************** code for paging (base on Maurizio work)
if(!isset($start_rec)) {   // This variable is set to zero for the first page
		$start_rec = 0;
}

$eu = ($start_rec -0);                
$this1 = $eu + $limitcount; 
$back_rec = $eu - $limitcount; 
$next_rec = $eu + $limitcount; 

//******************************


if (IsLoggedIn()) {
	$str = "select SQL_CACHE members.*,cities.Name as cityname,IdRegion,countries.Name as countryname,membersphotos.FilePath as photo,membersphotos.Comment from cities,countries,members left join membersphotos on membersphotos.IdMember=members.id and membersphotos.SortOrder=0 where countries.id=cities.IdCountry and cities.id=members.IdCity and status='Active' GROUP BY members.id order by members.LastLogin desc  limit $eu,".$limitcount;
	$rtot=LoadRow("select SQL_CACHE count(*) as cnt from from cities,countries,members where countries.id=cities.IdCountry and cities.id=members.IdCity and status='Active' GROUP BY members.id ";
} else {
	$str = "select SQL_CACHE members.*,cities.Name as cityname,IdRegion,countries.Name as countryname,membersphotos.FilePath as photo,membersphotos.Comment from cities,countries,memberspublicprofiles,members left join membersphotos on membersphotos.IdMember=members.id and membersphotos.SortOrder=0 where countries.id=cities.IdCountry and cities.id=members.IdCity and status='Active' and memberspublicprofiles.IdMember=members.id GROUP BY members.id order by members.LastLogin desc  limit $eu,".$limitcount; 
	$rtot=LoadRow("select SQL_CACHE count(*) as cnt from from cities,countries,members,memberspublicprofiles where countries.id=cities.IdCountry and cities.id=members.IdCity and status='Active' and memberspublicprofiles.IdMember=members.id GROUP BY members.id ";
}

$TData = array ();
$qry = mysql_query($str);

// MAU counting the max to reach TODO probable bug to fix (need additional query ?)
$nume=$rtot->cnt ;


while ($rr = mysql_fetch_object($qry)) {
	if ($rr->Comment > 0) {
		$rr->phototext = FindTrad($rr->Comment);
	} else {
		$rr->phototext = "no comment";
	}

	if ($rr->ProfileSummary > 0) {
		$rr->ProfileSummary = FindTrad($rr->ProfileSummary,true);
	} else {
		$rr->ProfileSummary = "";
	}

   $rr->regionname=getregionname($rr->IdRegion) ;
	
	array_push($TData, $rr);
}

//**************** variables for advance paging 
if(!isset($p_f)){$p_f=0;}
$p_fwd=$p_f+$limitcount;
$p_back=$p_f-$limitcount;
//**************** End of variables for advance paging 

//************ Start the buttom links with Prev and next link with page numbers /////////////////
//MAU
echo "<table align = 'center' width='50%'><tr><td  align='left' width='20%'>";
if($p_f<>0){print "<a href='$page_name?start=$p_back&p_f=$p_back'><font face='Verdana' size='2'>PREV $limitcount</font></a>"; }
echo "</td><td  align='left' width='10%'>";
//// if our variable $back is equal to 0 or more then only we will display the link to move back ////////
if($back >=0 and ($back >=$p_f)) { 
print "<a href='$page_name?start=$back&p_f=$p_f'><font face='Verdana' size='2'>PREV</font></a>"; 
} 
//////////////// Let us display the page links at  center. We will not display the current page as a link ///////////
echo "</td><td align=center width='30%'>";
for($i=$p_f;$i < $nume and $i<($p_f+$limitcount);$i=$i+$limitcount){
if($i <> $eu)
{
$i2=$i+$p_f;
echo " <a href='$page_name?start=$i&p_f=$p_f'><font face='Verdana' size='2'>$i</font></a> ";
}
else { echo "<font face='Verdana' size='4' color=red>$i</font>";}        /// Current page is not displayed as link and given font color red

}

echo "</td><td  align='right' width='10%'>";
///////////// If we are not in the last page then Next link will be displayed. Here we check that /////
if($this1 < $nume and $this1 <($p_f+$limitcount)) { 
print "<a href='$page_name?start=$next&p_f=$p_f'><font face='Verdana' size='2'>NEXT</font></a>";} 
echo "</td><td  align='right' width='20%'>";
if($p_fwd < $nume){
print "<a href='$page_name?start=$p_fwd&p_f=$p_fwd'><font face='Verdana' size='2'>NEXT $limitcount</font></a>"; 
}
echo "</td></tr></table>";

//*********************************



include "layout/members.php";
DisplayMembers($TData);
?>
