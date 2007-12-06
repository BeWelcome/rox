<?php

 # provides the internal (generated) page "SinceUpdatedPages"
 #
 # Carsten Senf <ewiki@csenf.de>


 define("EWIKI_PAGE_SINCEUPDATES", "SinceUpdatedPages");

 $ewiki_plugins["page"][EWIKI_PAGE_SINCEUPDATES] = "ewiki_page_since_updates";



function ewiki_page_since_updates($id=0, $data=0) {
	$orderby="lastmodified";
	$asc=-1;
	$print="%02dT %02dH %02dM %02dS";
	$title="Aktualisierung seit";
	
	$sorted = array();
	$result = ewiki_db::GETALL(array($orderby, "flags", "version"));
	
	while ($row = $result->get()) {
		if (EWIKI_DB_F_TEXT == ($row["flags"] & EWIKI_DB_F_TYPE)) {
			$sorted[$row["id"]] = $row[$orderby];
		}
	}
	
	if ($asc != 0) { arsort($sorted); }
	else { asort($sorted); }
	
	foreach ($sorted as $name => $value) {
		$x = time() - $value;
		$dy = (int)(($x) / 86400);
		$hr = (int)(($x % 86400) / 3600);
		$mn = (int)((($x % 86400) % 3600) / 60);
		$se = (($x % 86400) % 3600) % 60;
		$sorted[$name] = sprintf($print, $dy, $hr, $mn, $se);
	}
	$o .= ewiki_list_pages($sorted);
	
	return($o);
}

?>