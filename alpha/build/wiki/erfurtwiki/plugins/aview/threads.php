<?php

/*
    Thread plugin for ewiki
    
    Add this plugin after aview_posts to enable threads.  Threads are sub pages 
    which are named as $id.EWIKI_THREAD_SEPARATOR.{thread name}.  Threads may or
    may not be edited but should definately have posts added to them.  This 
    plugin is incomplete.  It still lacks a title_transform for threads and 
    other features.
    
    by AndyFundinger (http://erfurtwiki.sourceforge.net/?id=AndyFundinger)
*/

$ewiki_plugins["view_append"][]="ewiki_view_append_threads";
$ewiki_plugins["action"]["addthread"] = "ewiki_add_thread";

$ewiki_t["en"]["THREADS"]= "Threads";
$ewiki_t["en"]["NEWTHREAD"]= "Create new thread";
define ('EWIKI_THREAD_SEPARATOR','_THREAD_');

function isThread($id){
    return((strpos($id,EWIKI_THREAD_SEPARATOR)!==FALSE)&&!isPost($id));
}

function ewiki_view_append_threads($id, $data, $action) {
    if(isThread($id)) return("");

    $result = ewiki_db::SEARCH("id", $id.EWIKI_THREAD_SEPARATOR);
    while ($row = $result->get()) {
            if(!isPost($row["id"])){
                $pages[$row["id"]] = "";
                }
    }
    
    if(0!=count($pages)){ 
        $o = "<div class='wiki_threads'><small>".ewiki_t('THREADS').":</small><br />";
        $o .= ewiki_list_pages($pages)."</div>\n";    
    }

    $o .="<form action='".ewiki_script('addthread', $id)."' method='POST'>".
        ewiki_t("NEWTHREAD").":  <input type='text' name='threadname'>".
        "<input type='submit' value='Add Thread'>".
        "</form>";
        
    return("<div class='wiki aview threads'>".$o.'</div>');
}


//Adding a thread is just creating a specially named page.
//We create a blank page and then edit a post off of it
function ewiki_add_thread($id, $data, $action){
	global $ewiki_plugins;

	$id=$id.EWIKI_THREAD_SEPARATOR.$_REQUEST['threadname'];

	$save = array(
		"id" => $id,
		"version" => 1,
		"flags" => '',
		"content" => "   ",
		"author" => ewiki_author(),
		"lastmodified" => time(),
		"created" => time(),
		"meta" => array('isThread'=>'1'),
		"hits" => 0,
		"refs" => ""
	);

	if (!ewiki_db::WRITE($save)) {
		return(ewiki_t("ERRORSAVING"));
	}

	return(ewiki_add_post($id, array(), 'addpost'));
}

?>