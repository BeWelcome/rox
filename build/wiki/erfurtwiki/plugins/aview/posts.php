<?php

#  this plugin appends a class of wiki pages to the bottom of the
# current page this will grow/merge into or support the forums
# plugin
#
#  you could alternatively define EWIKI_AUTOVIEW to 0, and call the
#  ewiki_posts() wrapper function anywhere on yoursite.php

#-- text
$ewiki_t["en"] = array_merge($ewiki_t["en"], array(
   "EDITTHISPOST" => "Edit This Post",
   "INFOABOUTPOST" => "Information about post",
   "COMPVERPOST" => "See Changes"
   ));

#-- entitle actions
$ewiki_config['posts_action_links'] = array_merge(array(
    "edit" => "EDITTHISPOST",
    "info" => "INFOABOUTPOST",
    "diff" => "COMPVERPOST",

), $ewiki_config['posts_action_links']);

if (!defined("EWIKI_AUTOVIEW") || EWIKI_AUTOVIEW) {
   $ewiki_plugins["view_append"]['view_append_posts'] = "ewiki_view_append_posts";
}

$ewiki_t["en"]["POSTS"] = "posts";
$ewiki_t["en"]["POST"] = "Post: ";
$ewiki_t["en"]["ADDPOST"] = "Add a post";
$ewiki_t["en"]["TOO_MANY_POSTS"] = 'We are sorry.  The maximum number of posts has been exceeded.<br /><br />Return to [$id]';

$ewiki_config["action_links"]["view"]["addpost"] =  $ewiki_t["en"]["ADDPOST"];

$ewiki_plugins["action"]["addpost"] = "ewiki_add_post";
$ewiki_plugins["handler"][] = 'ewiki_adjust_thread_controls';
$ewiki_plugins["handler"][] = 'ewiki_view_post_parent';

define ('EWIKI_POST_SEPARATOR','_POST');
define("EWIKI_POSTID_PARSE_REGEX",'#(.*)'.preg_quote(EWIKI_POST_SEPARATOR) .'(\d{3})'.'#');
define ('EWIKI_VIEWPOSTACTION','view');

function ewiki_view_post_parent($id, $data, $action){
    global $ewiki_plugins;

    if(!preg_match(EWIKI_POSTID_PARSE_REGEX,$id,$matches)||($action!='view'))
        return;
        
    //Authentication for the new page is handled within this call.
    $o=ewiki_page('view/'.$matches[1]);
    
    //page_final plugins have been run, unset them
	unset($ewiki_plugins["page_final"]);    
    
    return($o);
}

function ewiki_adjust_thread_controls($id, $data, $action){
        global $ewiki_config;

	if($action!='view')
		return;
	if(!posts_exist())
		return;
                
	//Move add post action to first position in control links
	$temp = $ewiki_config["action_links"]['view']['addpost'];
	unset($ewiki_config["action_links"]['view']['addpost']);
	$ewiki_config["action_links"]['view'] = array_merge(array('addpost'=>$temp),$ewiki_config["action_links"]['view']);
}

function ewiki_add_post($id, $data, $action){
    global $ewiki_plugins,$ewiki_config;
    
    $postNum=getPostCount($id)+1; 
    $postNum=str_pad($postNum, 3, "0", STR_PAD_LEFT);
    
    if($postNum>999){
        $o=ewiki_format(ewiki_t("TOO_MANY_POSTS", array("id"=>$id)));
    } else {
        $id=$id.EWIKI_POST_SEPARATOR.($postNum);
        $ewiki_config["create"] = $id;
        //echo("Calling edit function $id");
        $o=$ewiki_plugins["action"]["edit"]($id,array("id"=>$id) ,$action);
    }
    
    return($o);
}

function ewiki_view_append_posts($id, $data, $action) {
	global $ewiki_plugins,$ewiki_config;

## Since this function calls ewiki_page_view it must disable itself when it is called
##  I do this by having it set separate view_append and view final actions for posts
##  but at a minimum it removes itself

   $std_view_append=$ewiki_plugins["view_append"];
   $std_view_final=$ewiki_plugins["view_final"];
   $std_action_links=$ewiki_config["action_links"]["view"];
   $thread_page_title=$GLOBALS["ewiki_title"];

   if(!isset($ewiki_config['posts_view_append'])){
	unset($ewiki_plugins["view_append"]['view_append_posts']);
   }else{
	$ewiki_plugins["view_append"]=$ewiki_config['posts_view_append'];
   }
   
   if(isset($ewiki_config['posts_view_final'])){
	$ewiki_plugins["view_final"]=$ewiki_config['posts_view_final'];
   }
   
   if(!isset($ewiki_config['posts_action_links'])){
	unset($GLOBALS['ewiki_config']["action_links"]["view"]["addpost"]);
   }else{
	$ewiki_config["action_links"]["view"]=$ewiki_config['posts_action_links'];
   }

    unset($_REQUEST["thankyou"]);		// Prevent thank you message from appearing in the menu
    						//  bar

/**
 * Code for the new database layer */
   $result = ewiki_db::SEARCH("id", $id.'_POST');

	#sort by post number
   $ord = array();
   while ($row = $result->get()) {
     if(preg_match('#_POST(\d{3})#', $row["id"],$matches = array())){
       $ord[$matches[1]] = $row['id'];
      }
   }
   asort($ord);
   
   foreach ($ord as $postNum => $id) {
        $GLOBALS["ewiki_title"]=ewiki_t('POST').$postNum;

        $row = ewiki_db::GET($id);
        
        #-- require auth
        if (EWIKI_PROTECTED_MODE) {
          if (!ewiki_auth($id, $row, EWIKI_VIEWPOSTACTION, $ring=false, $force=0)) {
             continue;
          }
        }
        
        $postOut = ewiki_page_view($id, $row, EWIKI_VIEWPOSTACTION);
        ewiki_page_css_container($postOut , $id, $row, $oo=EWIKI_VIEWPOSTACTION);        
        $o.=$postOut ;
   }

## Restore normal actions
	$ewiki_plugins["view_append"]=   $std_view_append;
	$ewiki_plugins["view_final"]=   $std_view_final;
	$ewiki_config["action_links"]["view"]=$std_action_links;
	$GLOBALS["ewiki_title"]=$thread_page_title;
	
   return("<div class='wiki aview posts'>".$o.'</div>');
}

function isPost($id){
	return(preg_match(EWIKI_POSTID_PARSE_REGEX,$id));
}
function getPostNumber($id){
	preg_match(EWIKI_POSTID_PARSE_REGEX,$id,$match=array());
	return($match[2]);
}

function getPostCount($id){
/**
 * Code for the new database layer*/
	#sort by post number
   $ord = array();

   $scan =  $id.EWIKI_POST_SEPARATOR; //Should I escape $id here?
   $result = ewiki_db::SEARCH("id", $scan);
   while ($row = $result->get()) {
	preg_match('#_POST(\d{3})#', $row["id"],$matches=array($matches));
        $ord[$row["id"]] = $matches[1];
   }
   rsort($ord);
   return($ord[0]);
}

function posts_exist($always=false) {
    $id = $GLOBALS["ewiki_id"];
    
    $result = $always || ($id)
        && ($result = ewiki_db::SEARCH("id", $id.'_POST'))
        && ($result->count());
    return( ($id) && ($id != EWIKI_PAGE_CALENDAR) && ($id != EWIKI_PAGE_YEAR_CALENDAR)
        && !isPost($id)
        && empty($_REQUEST["year"]) && $result );
}

?>