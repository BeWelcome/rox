<?php
function prepare_profile_header($IdMember,$wherestatus= " and Status='Active'",$photorank=0) {
	if (HasRight("Accepter")) { // accepter right allow for reading member who are not yet active
   	   	$wherestatus = "";
	}
	// Try to load the member
	$str = "select SQL_CACHE * from members where id=" . $IdMember . $wherestatus;

	$m=LoadRow($str);

	if (!isset ($m->id)) {
	    $errcode = "ErrorNoSuchMember";
		DisplayError(ww($errcode, $IdMember));
		//		die("ErrorMessage=".$ErrorMessage) ;
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
    	$m->IdPhoto = $rr->id ;
	}
	$m->pic_sm2=$m->photo = $photo;
	$m->photorank = $photorank;
	$m->phototext = $phototext;
	
	$sm1=(int)$photorank-1 ;
	$str = "select SQL_CACHE * from membersphotos where IdMember=" . $IdMember . " and SortOrder=" . $sm1;
	$rr = LoadRow($str);
	if (isset ($rr->FilePath)) {
	    $m->pic_sm1= $rr->FilePath;
	}
	else {
	  	$str = "select SQL_CACHE * from membersphotos where IdMember=" . $IdMember . " order by SortOrder desc limit 1";
	  	$rr = LoadRow($str);
	  	if (isset ($rr->FilePath)) {
	        $m->pic_sm1= $rr->FilePath;
	  	}
	}
	
	$sm3=(int)$photorank+1 ;
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
	    $rWhere = LoadRow("select SQL_CACHE cities.Name as cityname,regions.Name as regionname,countries.Name as countryname from cities,countries,regions where cities.IdRegion=regions.id and countries.id=regions.IdCountry and cities.id=" . $m->IdCity);
		$m->cityname = $rWhere->cityname;
		$m->regionname = $rWhere->regionname;
		$m->countryname = $rWhere->countryname;
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
		$m->LastLogin = localdate($m->LastLogin);

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
    return($m) ;
} // end of prepare_profile_header
?>
