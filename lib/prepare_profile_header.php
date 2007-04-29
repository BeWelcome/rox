<?php

function prepareProfileHeader($IdMember,$wherestatus=null,$photorank=0) {

	global $_SYSHCVOL;
/*
	if ($wherestatus == null)
		$wherestatus = " and Status='Active'";

	if (HasRight("Accepter")) { // accepter right allow for reading member who are not yet active
   	   	$wherestatus = "";
	}

	// Try to load the member
	$str = "select SQL_CACHE * from members where id=" . $IdMember . $wherestatus;

	$m=LoadRow($str);

	if (!isset ($m->id)) {
	    $errcode = "ErrorNoSuchMember";
		DisplayError(ww($errcode, $IdMember));
		//		bw_error("ErrorMessage=".$ErrorMessage);
		exit (0);
	}

	// manage picture photorank (swithing from one picture to the other)

	$m->profilewarning = "";
	if ($m->Status != "Active") {
	    $m->profilewarning = "WARNING the status of " . $m->Username . " is set to " . $m->Status;
	}

	// Load photo data
    $photo = "";
	$phototext = "";
	$str = "select SQL_CACHE * from membersphotos where IdMember=" . $IdMember . " and SortOrder=" . $photorank;
	$rr = LoadRow($str);
	if (!isset ($rr->FilePath) and ($photorank > 0)) {
	    $rr = LoadRow("select SQL_CACHE * from membersphotos where IdMember=" . $IdMember . " and SortOrder=0");
	}
	if (isset ($rr->FilePath)) {
	    $photo = $rr->FilePath;
	    $phototext = FindTrad($rr->Comment);
		$photorank = $rr->SortOrder;
    	$m->IdPhoto = $rr->id;
	}
	if ($photo=="") {
	    $m->pic_sm2=$m->photo = "images/et.gif";
		if (($m->Gender=='male')and($m->HideGender=='No'))  $m->pic_sm2=$m->photo = "images/et_male.gif";
		if (($m->Gender=='female')and($m->HideGender=='No')) $m->pic_sm2=$m->photo = "images/et_female.gif";
		$m->photorank = 0;
		$m->phototext = "no picture provided";
	}
	else {
	    $m->pic_sm2=$m->photo = "http://".$_SYSHCVOL['SiteName'].$_SYSHCVOL['MainDir'].$photo;
	}
	$m->photorank = $photorank;
	$m->phototext = $phototext;
	
	$sm1=(int)$photorank-1;
	$str = "select SQL_CACHE * from membersphotos where IdMember=" . $IdMember . " and SortOrder=" . $sm1;
	$rr = LoadRow($str);
	
	if (isset ($rr->FilePath)) {
	    $m->pic_sm1= "http://".$_SYSHCVOL['SiteName'].$_SYSHCVOL['MainDir'].$rr->FilePath;
	}
	else {
	  	$str = "select SQL_CACHE * from membersphotos where IdMember=" . $IdMember . " order by SortOrder desc limit 1";
	  	$rr = LoadRow($str);
	  	if (isset ($rr->FilePath)) {
	        $m->pic_sm1= "http://".$_SYSHCVOL['SiteName'].$_SYSHCVOL['MainDir'].$rr->FilePath;
	  	}
	}
	$sm3=(int)$photorank+1;
	$str = "select SQL_CACHE * from membersphotos where IdMember=" . $IdMember . " and SortOrder=" . $sm3;
	$rr = LoadRow($str);
	if (isset ($rr->FilePath)) {
	    $m->pic_sm3= $rr->FilePath;
	}
	else {
	  	$str = "select SQL_CACHE * from membersphotos where IdMember=" . $IdMember . " and SortOrder=0";
	  	$rr = LoadRow($str);
	  	if (isset ($rr->FilePath)) {
	        $m->pic_sm3= $rr->FilePath;
	  	}
	}
	

	// Load geography
	if ($m->IdCity > 0) {
	    $rWhere = LoadRow("select SQL_CACHE cities.IdCountry as IdCountry,cities.Name as cityname,cities.id as IdCity,countries.Name as countryname,IdRegion from cities,countries where countries.id=cities.IdCountry and cities.id=" . $m->IdCity);
		$m->cityname = $rWhere->cityname;
		$m->countryname = $rWhere->countryname;

		$m->regionname=getregionname($rWhere->IdRegion) ;
		$m->IdRegion=$rWhere->IdRegion ;
		$m->IdCountry=$rWhere->IdCountry ;
	}

	// Load nbcomments nbtrust
	$m->NbTrust = 0;
	$m->NbComment = 0;
	$rr = LoadRow("select SQL_CACHE count(*) as cnt from comments where IdToMember=" . $m->id . " and Quality='Good'");
	if (isset ($rr->cnt))
	    $m->NbTrust = $rr->cnt;
	$rr = LoadRow("select SQL_CACHE count(*) as cnt from comments where IdToMember=" . $m->id);
	if (isset ($rr->cnt))
	    $m->NbComment = $rr->cnt;

	if ($m->LastLogin == "11/30/99 00:00:00")
	    $m->LastLogin = ww("NeverLog");
	else
		$m->LastLogin = localdate($m->LastLogin,"%d/%m/%y %Hh%M");

	// Load Age
	$m->age = fage($m->BirthDate, $m->HideBirthDate);

	// Load full name
	$m->FullName = fFullName($m);

	// Load Address data
	$rr = LoadRow("select SQL_CACHE * from addresses where IdMember=" . $m->id, " and Rank=0 limit 1");
	if (isset ($rr->id)) {
	    $m->Address = PublicReadCrypted($rr->HouseNumber, "*") . " " . PublicReadCrypted($rr->StreetName, ww("MemberDontShowStreetName"));
		$m->Zip = PublicReadCrypted($rr->Zip, ww("ZipIsCrypted"));
		$m->IdGettingThere = FindTrad($rr->IdGettingThere);
	}
	*/
    return($m);
} // end of prepareProfileHeader
?>
