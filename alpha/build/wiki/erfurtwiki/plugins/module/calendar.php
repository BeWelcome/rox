<?php
/**
* this plugin is in european date format only at the moment.
*
* returns a calender for the current / every page,
* example:
*
*	<!php
*		#-- plugin, CSS
*		include("plugins/calendar.php");
*		include("fragments/calendar.css");
*
*		#-- show page
*		echo ewiki_page();
*
*		#-- show calendar
*		if (calendar_exists()) {
*			echo calendar();
*		}
*	!>
*
* this plugin was
* contributed_by("Carsten Senf <ewiki@csenf.de>");
* and en_ localization was added by Andy Fundinger Andy@burgiss.com
*
*/ 

$ewiki_t["en"]["MONTHS"] = "January February March April May June July August September October November December";
$ewiki_t["de"]["MONTHS"] = "Januar Februar März April Mai Juni Juli August September Oktober November Dezember";
$ewiki_t["en"]["DAYABBRVS"] = "Mo Tu We Th Fr Sa Su";
$ewiki_t["de"]["DAYABBRVS"] = "Mo Di Mi Do Fr Sa So";
$ewiki_t["en"]["CALENDARFOR"] = "Calendar for";		
$ewiki_t["de"]["CALENDARFOR"] = "Kalender für";

define("EWIKI_ACTION_CALENDAR", "calendar");
define("EWIKI_PAGE_CALENDAR", "PageCalendar");
define("EWIKI_PAGE_YEAR_CALENDAR", "PageYearCalendar");
define("EWIKI_CALENDAR_WIDTH", 4);
define("EWIKI_NAME_SEP", "_");
define("CALENDAR_NAME_SEP", EWIKI_NAME_SEP);
define("CALENDAR_PAGE_TITLE_REGEX","/^(.*?)".preg_quote(CALENDAR_NAME_SEP) ."\d{8}$/");

$ewiki_plugins["page"][EWIKI_PAGE_CALENDAR] = "ewiki_page_calendar";
$ewiki_plugins["page"][EWIKI_PAGE_YEAR_CALENDAR] = "ewiki_page_year_calendar";
$ewiki_plugins["action"][EWIKI_ACTION_CALENDAR] = "ewiki_page_calendar";
$ewiki_config["action_links"]["view"][EWIKI_ACTION_CALENDAR] = EWIKI_PAGE_CALENDAR;

function calendar() {
        ($id = $GLOBALS["ewiki_id"]) or ($id = EWIKI_PAGE_CALENDAR);
	return(ewiki_page_calendar($id, array("id"=>$id)));
}


function calendar_exists($always=false) {
	if ($always) { return(true); }

	$result=($id = $GLOBALS["ewiki_id"])
	&& ($Qresult = ewiki_db::SEARCH("id", $id.CALENDAR_NAME_SEP))
	&& ($Qresult->count());

	if (!$result || ($id==EWIKI_PAGE_CALENDAR) || ($id==EWIKI_PAGE_YEAR_CALENDAR) || (!empty($_REQUEST["year"]))) {
		return(false);
	}
	while ($row = $Qresult->get(0, 0x1037)) {
		if (ewiki_isCalendarId($row["id"])) {
			return(true);
		}
	}
	return(false);
}


function ewiki_page_calendar($id, $data=0) {

	if ($_REQUEST["year"]) {
		return(ewiki_page_year_calendar($id, $data));
	}
	else {
		return(renderCalendar($id, TRUE));
	}
}


function ewiki_page_year_calendar($id, $data=0) {

	($year = $_REQUEST['year']) or ($year = date("Y"));
	($pgname = $_REQUEST['pgname']) or ($pgname = $id);

	$prev = $year-1;
	$next = $year+1;
		
	$html = '<h2>'.ewiki_t("CALENDARFOR").' <a href="'.ewiki_script("",$pgname).'">'.$pgname."</a> - ".$year.'</h2><center><table cellpadding=\"10\">'."\n";

	for($i=1; $i<12; $i+=EWIKI_CALENDAR_WIDTH) {
		$html .= "<tr>\n";
		for($month=$i; $month<$i+EWIKI_CALENDAR_WIDTH; $month++) {
			$html .= "<td valign=\"top\">\n" . RenderCalendar($pgname, FALSE, $year, $month) . "\n</td>\n";
		}
		$html .= "</tr>\n";
	}
	
	$html .= "<tr>
		<td align=\"left\" valign=\"bottom\">".
		'<a href="'.ewiki_script(EWIKI_ACTION_CALENDAR,$pgname,'year='.$prev).'">'.
		"&lt; $prev</a>
		</td>
		<td align=\"center\" colspan=\"".(EWIKI_CALENDAR_WIDTH-2)."\"></td>".
		"<td align=\"right\" valign=\"bottom\">".
		'<a href="'.ewiki_script(EWIKI_ACTION_CALENDAR,$pgname,'year='.$next).'">'.
		"$next &gt;</a>
		</td>
		";
	$html .= "</table></center>";
	return $html;
}




function renderCalendar($pgname, $more, $year = NULL, $month = NULL) {	

	if (preg_match(CALENDAR_PAGE_TITLE_REGEX, $pgname, $match)) {
		$pgname = $match[1];
	}

	    $month_names = explode(" ", ewiki_t("MONTHS"));
        $day_names = explode(" ", ewiki_t("DAYABBRVS"));
	
	if (!isset($year)) {
		$year = date("Y");
	}
	if (!isset($month)) {
		$month = date("n");
	}
	
	$shift = 0;
	$today_ts = mktime(0,0,0,$month,date("d"),$year); // non relative date
	$firstday_month_ts = mktime(0,0,0,$month,1,$year); // first day of the month
	$lastday_month_ts = mktime(0,0,0,$month+1,0,$year);    // last day of the month
	
	$numYear = date("Y",$firstday_month_ts);
	$numMonth = date("m",$firstday_month_ts);
	$textMonth = $month_names[(date("m", $firstday_month_ts))-1];
	$daysInMonth = date("t",$firstday_month_ts);
	
	$dayMonth_start = date("w",$firstday_month_ts);
	if ($dayMonth_start==0) { $dayMonth_start=7;} 
	
	
	$dayMonth_end = date("w",$lastday_month_ts);
	if ($dayMonth_end==0) { $dayMonth_end=7; }
	
	$ret = "";
	
        #-- start table, print month name as title
	$ret .=  '<table class="calendar caltable" cellpadding="2" cellspacing="1">'."\n";
	$ret .=  '<tr><th class="head calhead" colspan="7">';
	$ret .= ($more ? '<a href="'.ewiki_script(EWIKI_ACTION_CALENDAR,$pgname,'year='.$year).'">' : "")
		. $textMonth."&nbsp;&nbsp;".$numYear
		. ($more ? "</a>" : "");
	$ret .= "</th></tr>\n";

	#-- output days
	$ret .=  "<tr>\n";
        for ($n=0; $n<7; $n++) {
		$ret .=  '<th class="days daynames caldays">' . $day_names[$n] . '</th>';
        }	
	$ret .= "</tr>\n";
	
	#-- not-existent days
	$ret .=  "<tr>\n";
	
	for ($k=1; $k<$dayMonth_start; $k++) {
		$ret .=  "<td>&nbsp;</td>\n";
	}

	#-- pre-scan for calendar day pages
	$f = array();
	for ($i=1; $i<=$daysInMonth; $i++) {
		$f[] = $pgname.CALENDAR_NAME_SEP.$numYear.$numMonth.(strlen($i)<2 ? "0$i" : "$i");
	}
	$f = ewiki_db::FIND($f);
	
	for ($i=1; $i<=$daysInMonth; $i++) {
		$day_i_ts=mktime(0,0,0,date("n",$firstday_month_ts),$i,date("Y",$firstday_month_ts));
		$day_i = date("w",$day_i_ts);
		
		if ($day_i==0) { $day_i=7;}

		#-- calendar day page name
		$page = $pgname.CALENDAR_NAME_SEP.$numYear.$numMonth. (strlen($i)<2 ? "0$i" : "$i");

	        #-- retrieve and check rights if running in protected mode
        	if (EWIKI_PROTECTED_MODE && EWIKI_PROTECTED_MODE_HIDING && $f[$page] && !ewiki_auth($page, $uu, "view", $ring=false, $force=0)) {
	           unset($f[$page]);
	        }   

        	#-- link to calendar day page
		$link_i = '<a href="'.ewiki_script("",$page).'"'
			. ($f[$page]
				? ' class="found calpg"><b>' . $i . '</b>'
				: ' class="hide calhide">' . $i )
			. "</a>";
	
		#-- day table cell
		$day_class = (($month==date("n") && $today_ts==$day_i_ts)
			? "today caltoday" : "day calday");
		$ret .=	'<td class="' .	$day_class . '">' . $link_i . "</td>";
		
		#-- close week-row                        
		if ($day_i==7 && $i<$daysInMonth) {
				$ret .=  "</tr><tr>\n"; 
		} 
		else if ($day_i==7 && $i==$daysInMonth) {
				$ret .=  "</tr>\n";
		}
		#-- add empty day cells to the end
		else if ($i==$daysInMonth) {
				for ($h=$dayMonth_end; $h<7; $h++) { 
				$ret .=  "<td>&nbsp;</td>\n";
				 }
			$ret .=  "</tr>\n";
		}
	}
	$ret .=  "</table>\n";

	return($ret);
}


function ewiki_isCalendarId($id) {
	return(preg_match(CALENDAR_PAGE_TITLE_REGEX, $id));
}


?>