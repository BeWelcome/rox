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
require_once "layout/error.php";
require_once "lib/prepare_profile_header.php";
require_once "layout/mytranslators.php";

MustLogIn();

// Find parameters
$IdMember = $this->_session->get('IdMember');
if (IsAdmin()) { // admin can alter other profiles
	$IdMember = GetParam("cid", $this->_session->get('IdMember'));
}

$m = prepareProfileHeader($IdMember,"",0); // This is the profile of the contact which is going to be used

switch (GetParam("action")) {
	case "del" :
		$str="delete from intermembertranslations where IdTranslator=".GetParam("IdTranslator")." and IdMember=".$IdMember;
		sql_query($str);
		LogStr("Removing translator <b>".fUserName(GetParam("IdTranslator"))."</b>","mytranslators");
		break;
	case "add" : // todo
		$IdTranslator=IdMember(GetParam("Username"),0);
		$IdLanguage=Getparam("IdLanguage");
		$rr=LoadRow("select id from intermembertranslations where IdTranslator=".$IdTranslator." and IdMember=".$IdMember." and IdLanguage=".$IdLanguage);
		if (!isset($rr->id) and ($IdTranslator!=0)) { // if not allready exists
		   $str="insert into intermembertranslations(IdTranslator,IdMember,IdLanguage) values(".$IdTranslator.",".$IdMember.",".$IdLanguage.")";
		   sql_query($str);
		   LogStr("Adding translator <b>".fUserName(GetParam("IdTranslator"))."</b> for language","mytranslators");
		}
		break;
}

$TData = array ();
$str = "select intermembertranslations.*,members.Username,members.ProfileSummary,cities.Name as cityname,regions.Name as regionname,countries.Name as countryname,membersphotos.FilePath as photo,membersphotos.Comment";
$str .= " from intermembertranslations,cities,countries,regions,recentvisits,members left join membersphotos on membersphotos.IdMember=members.id and membersphotos.SortOrder=0 where cities.IdRegion=regions.id and countries.id=cities.IdCountry and cities.id=members.IdCity and status='Active' and members.id=intermembertranslations.IdTranslator and intermembertranslations.IdMember=" . $IdMember . " and members.status='Active' GROUP BY members.id order by intermembertranslations.updated desc";
$qry = sql_query($str);
while ($rr = mysql_fetch_object($qry)) {
	if ($rr->ProfileSummary > 0) {
		$rr->ProfileSummary = FindTrad($rr->ProfileSummary);
	} else {
		$rr->ProfileSummary = "";
	}
	array_push($TData, $rr);
}

// Load the language the member does'nt know
$m->TLanguages = array ();
$str = "select languages.Name as Name,EnglishName,languages.id as id from languages order by Name";
$qry = mysql_query($str);
while ($rr = mysql_fetch_object($qry)) {
	array_push($m->TLanguages, $rr);
}
DisplayMyTranslators($TData,$m);
?>
