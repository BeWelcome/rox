<?php
/*
   dynamic plugin loading
   ¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯
   Will load plugins on demand, so they must not be included() one by one
   together with the core script. This is what commonly the "plugin idea"
   suggests, and only has minimal disadvantages.
   - This loader currently only handles "page" and "action" plugins,
     many other extensions must be activated as before (the other ones
     are real functionality enhancements and behaviour tweaks, so this
     approach really made no sense for them).
   - There is no security risk with this plugin loader extension, because
     it allows you to set which of the available plugins CAN be loaded
     on demand (all others must/can be included() as usual elsewhere).
   - This however requires administration of this plugins` configuration
     array, but that is not much more effort than maintaining a bunch of
     include() statements.
   - Is a small degree faster then including multiple plugin script files
     one by one. Alternatively you could also merge (cat, mkhuge) all
     wanted plugins into one script file so you get a speed improvement
     against multiple include() calls.
   - Use tools/mkpluginmap to create the initial plugin list.
   
   NOTE: not yet updated to take advantage of new plugin *.meta files
   and cache.
*/


global $ewiki_plugins;
$ewiki_plugins["dl"]["action"] = array(
	"view" => array("", "", 0),
	"links" => array("", "", 0),
	"info" => array("", "", 0),
#	"edit" => array("spellcheck.php", "", 0),
	"raw" => array("action/raw.php", "ewiki_action_raw", 0),
	"diff" => array("action/diff.php", "ewiki_page_stupid_diff", 0),
#	"diff" => array("action/diff_gnu.php", "ewiki_page_gnu_diff", 0),
	"like" => array("action/like_pages.php", "ewiki_page_like", 0),
#	"verdiff" => array("action/verdiff.php", "ewiki_action_verdiff", 0),
#	"extodo" => array("action/extract.php", "ewiki_extract", 0),
#	"control" => array("admin/control.php", "ewiki_action_control_page", 0),
#	"imageappend" => array("aview/imgappend.php", "ewiki_action_image_append", 0),
#	"addpost" => array("aview/posts.php", "ewiki_add_post", 0),
#	"addthread" => array("aview/threads.php", "ewiki_add_thread", 0),
#	"updformatheader" => array("markup/update_format.php", "ewiki_header_format_swap", 0),
#	"calendar" => array("module/calendar.php", "ewiki_page_calendar", 0),
#	"binary" => array("module/downloads.php", "ewiki_binary", 0),
#	"attachments" => array("module/downloads.php", "ewiki_action_attachments", 0),
#	"tour" => array("module/tour.php", "ewiki_tour", 0),
	"search" => array("page/powersearch.php", "ewiki_action_powersearch", 0),
);

$ewiki_plugins["dl"]["page"] = array(
#	"SearchAndReplace" => array("admin/page_searchandreplace.php", "ewiki_page_searchandreplace", 0),
#	"SearchCache" => array("admin/page_searchcache.php", "ewiki_cache_generated_pages", 0),
#	"ImageGallery" => array("aview/aedit_pageimage.php", "ewiki_page_image_gallery", 0),
#	"MainGallery" => array("aview/aedit_pageimage.php", "ewiki_page_image_gallery", 0),
	"LinkDatabase" => array("linking/linkdatabase.php", "ewiki_linkdatabase", 0),
#	"PageCalendar" => array("module/calendar.php", "ewiki_page_calendar", 0),
#	"PageYearCalendar" => array("module/calendar.php", "ewiki_page_year_calendar", 0),
#	"FileUpload" => array("module/downloads.php", "ewiki_page_fileupload", 0),
#	"FileDownload" => array("module/downloads.php", "ewiki_page_filedownload", 0),
	"README" => array("page/README.php", "ewiki_page_README", 0),
#	"README.de" => array("page/README.php", "ewiki_page_README", 0),
#	"plugins/auth/README.auth" => array("page/README.php", "ewiki_page_README", 0),
#	"AboutPlugins" => array("page/aboutplugins.php", "ewiki_page_aboutplugins", 0),
#	"EWikiLog" => array("page/ewikilog.php", "ewiki_page_ewikilog", 0),
#	"Fortune" => array("page/fortune.php", "ewiki_page_fortune", 0),
	"HitCounter" => array("page/hitcounter.php", "ewiki_page_hitcounter", 0),
	"InterWikiMap" => array("page/interwikimap.php", "ewiki_page_interwikimap", 0),
	"OrphanedPages" => array("page/orphanedpages.php", "ewiki_page_orphanedpages", 0),
#	"PageIndex" => array("page/pageindex.php", "ewiki_page_index", 0),
#	"PhpInfo" => array("page/phpinfo.php", "ewiki_page_phpinfo", 0),
	"PowerSearch" => array("page/powersearch.php", "ewiki_page_powersearch", 0),
	"RandomPage" => array("page/randompage.php", "ewiki_page_random", 0),
#	"ScanDisk" => array("page/scandisk.php", "ewiki_page_scandisk", 0),
#	"SinceUpdatedPages" => array("page/since_updates.php", "ewiki_page_since_updates", 0),
#	"TextUpload" => array("page/textupload.php", "ewiki_page_textupload", 0),
	"WantedPages" => array("page/wantedpages.php", "ewiki_page_wantedpages", 0),
#	"WikiDump" => array("page/wikidump.php", "ewiki_page_wiki_dump_tarball", 0),
#	"WikiNews" => array("page/wikinews.php", "ewiki_page_wikinews", 0),
#	"WikiUserLogin" => array("page/wikiuserlogin.php", "ewiki_page_wikiuserlogin", 0),
	"WordIndex" => array("page/wordindex.php", "ewiki_page_wordindex", 0),
#	"AddNewPage" => array("page/addnewpage.php", "ewiki_addpage", 0),
#	"CreatePage" => array("page/addnewpage.php", "ewiki_addpage", 0),
#	"EineSeiteHinzufügen" => array("page/addnewpage.php", "ewiki_addpage", 0),
#	"CreateNewPage" => array("page/createnewpage.php", "ewiki_createpage", 0),
	"RecentChanges" => array("page/recentchanges.php", "ewiki_page_recentchanges", 0),
#	"ExAllTodo" => array("page/extractall.php", "ewiki_page_exall", 0),
);

global $ewiki_config;
$ewiki_config["dl"]["action_links"]["view"]["raw"] = "raw";
$ewiki_config["dl"]["action_links"]["view"]["verdiff"] = "verdiff";
$ewiki_config["dl"]["action_links"]["view"]["extodo"] = "EXTTODO";
$ewiki_config["dl"]["action_links"]["view"]["control"] = "page control";
$ewiki_config["dl"]["action_links"]["view"]["addpost"] = "Add a post";
$ewiki_config["dl"]["action_links"]["view"]["updformatheader"] = "UPDHEADERFORMAT";
$ewiki_config["dl"]["action_links"]["view"]["calendar"] = "PageCalendar";
$ewiki_config["dl"]["action_links"]["view"]["attachments"] = "Attachments";
$ewiki_config["dl"]["action_links"]["view"]["tour"] = "PageTour";
$ewiki_config["dl"]["action_links"]["view"]["pdf"] = "pdf";
$ewiki_config["dl"]["action_links"]["info"]["raw"] = "raw";
$ewiki_config["dl"]["action_links"]["info"]["diff"] = "diff";
$ewiki_config["dl"]["action_links"]["extodo"]["raw"] = "raw";
$ewiki_config["dl"]["action_links"]["extodo"]["verdiff"] = "verdiff";
$ewiki_config["dl"]["action_links"]["extodo"]["view"] = "VIEWCOMPL";
$ewiki_config["dl"]["action_links"]["tour"]["view"] = "ViewFullPage";
$ewiki_config["dl"]["action_links"]["tour"]["links"] = "BACKLINKS";
$ewiki_config["dl"]["action_links"]["tour"]["info"] = "PAGEHISTORY";





##############################################################################



#-- plugin glue
$ewiki_plugins["view_init"][] = "ewiki_dynamic_plugin_loader";


function ewiki_dynamic_plugin_loader(&$id, &$data, &$action) {

   global $ewiki_plugins, $ewiki_id, $ewiki_title, $ewiki_t,
          $ewiki_ring, $ewiki_author, $ewiki_config, $ewiki_auth_user,
          $ewiki_action;

   #-- check for entry
   if (empty($ewiki_plugins["page"][$id])) {
      $load = $ewiki_plugins["dl"]["page"][$id];
   }
   elseif (empty($ewiki_plugins["action"][$action])) {
      $load = $ewiki_plugins["dl"]["action"][$action];
   }

   #-- load plugin
   if ($load) {
      if (!is_array($load)) {
         $load = array($load, "");
      }
      if (!($pf=$load[1]) || !function_exists($pf)) {
         include_once(dirname(__FILE__)."/".$load[0]);
      }
   }

   #-- fake static pages
   foreach ($ewiki_plugins["dl"]["page"] as $name) {
      if (empty($ewiki_plugins["page"][$name])) {
         $ewiki_plugins["page"][$name] = "ewiki_dynamic_plugin_loader";
      }
   }

   #-- show action links
   foreach ($ewiki_plugins["dl"]["action"] as $action=>$uu) {
      foreach ($ewiki_config["dl"]["action_links"] as $where) {
         if ($title = $ewiki_config["dl"]["action_links"][$where][$action]) {
            $ewiki_config["action_links"][$where][$action] = $title;
         }
      }
   }

   return(NULL);
}


?>