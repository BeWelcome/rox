<?php
#  makes a pages` calendar title more readable,
#  replaces the standard _print_title with a _calendar_title
#
#  Also replaces the titles in page lists by usurping a spot in the list_pages plugin 
#  and calling whatever was there before after it has transformed the list.
#
#  Written by: Andy Fundinger
#
# the isCalendarId function from calendar.php is necessary starting with this
#  version
#
#-- glue
$ewiki_plugins["title_transform"][] = "ewiki_calendar_title_transform";
$ewiki_plugins["list_transform"][] = "ewiki_calendar_list_pages";

@define("EWIKI_NAME_SEP", "_");
@define("CALENDAR_NAME_SEP", EWIKI_NAME_SEP);
@define("CALENDAR_PAGE_TITLE_REGEX","/^(.*?)".preg_quote(CALENDAR_NAME_SEP) ."\d{8}$/");
@define("CALENDAR_PAGE_DATE_PARSE_REGEX", '#(.*)'. preg_quote(CALENDAR_NAME_SEP) .' ?(\d{4})(\d{2})(\d{2})#');
$ewiki_t["en"]['CALENDERENTRYFOR']='Calendar entry for ';
$ewiki_t["de"]['CALENDERENTRYFOR']='KalenderEintrag für ';

function ewiki_calendar_list_pages(&$lines) {
    global $ewiki_plugins;

    for($index=0;$index<count($lines);$index++){
                             //						1	  2				 													3
        $lines[$index]=preg_replace("#(.*>)(.*?".preg_quote(CALENDAR_NAME_SEP) ." ?\d{8})(.*)#e"," \"$1\".ewiki_calendar_page_title('$2').'$3'",$lines[$index]);
    }
	
   return($lines);
}

function ewiki_calendar_title_transform($id, &$title, &$go_action){

    if (ewiki_isCalendarId($id)) {
        $title=ewiki_calendar_page_title ($id);
    }
}

#-- title string replacing
function ewiki_print_calendar_title(&$html, $id, $data, $action, $split=EWIKI_SPLIT_TITLE) {
   global $ewiki_title;

   if (ewiki_isCalendarId($id)) {
     $html=str_replace(">$ewiki_title<", ">".ewiki_calendar_page_title($id,$split)."<", $html);
   }
}

function ewiki_calendar_page_title ($id='', $split=EWIKI_SPLIT_TITLE) {
   strlen($id) or ($id = $GLOBALS["ewiki_page"]);

   static $month_names;
   if (!isset($month_names)) {
      $month_names = explode(" ", ewiki_t("MONTHS"));
   }

   if(preg_match(CALENDAR_PAGE_DATE_PARSE_REGEX,$id,$dateParts) ){
   
        /*Transform Parent title using plugins */   
        $parentId=$dateParts[1];
        
        $parentTitle=$parentId;
        if ($ewiki_config["split_title"] || $split) {
            $parentTitle = ewiki_split_title($parentId);
        }
       
        #-- title mangling
        if ($pf_a = $ewiki_plugins["title_transform"]) {
            foreach ($pf_a as $pf) { $pf($parentId, $parentTitle, ''); }
        }
        
   	$title=ewiki_t("CALENDERENTRYFOR").$parentTitle.": ". $month_names[$dateParts[3]-1]." ".$dateParts[4].", ".$dateParts[2];
   }
   return($title);
} 
?>