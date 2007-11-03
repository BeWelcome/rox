<?php
/*
* This plugin extracts  items from every page in the wiki database.
* You can view items by specific users by entering their initials in the textbox
* The filter supports the use of 'and' and 'or' expressions but not both.
* Example items.

! ToDo Items

* Start a todo item with @@ followed by a todo item type, item types are:
** @@Todo JE: Todo
** @@DONE JE: DONE
** @@cancelled JE: cancelled
** @@dEaDlInE JE: dEaDlInE 
** @@SuBjEcT SuBjEcT
* Case of the class names does not matter.
* DO NOT use a colon ":" after the class name, this may seem logical but it will cause problems for EWikiCSS

Written By: Jeffrey Engleman
*/

$ewiki_t["en"]["EXTODOTITLE"] = "Site Wide Todo Lists";	
$ewiki_t["en"]["EXTODOFOR"] = "Todo items for ";	
$ewiki_t["en"]["EXTODOFORALL"] = "All todo items.";	
$ewiki_t["en"]["EXALLCONTROLS"] = '<div id="ewiki_todofilter"><form name="input" action="liveweb.php?" method="get"><p>Enter initials: 
  <input type="hidden" value="$controlid" name="id"><input type="text" id="q" name="q" size="2" maxlength"16"> <input type="submit"
  value="Extract Items"><br />(Hint: For multiple users connect initials using "and" or "or")</p></form></div>';
$ewiki_t["en"]["EXALLERROR"] = "<p><h4>Error</h4><ul><li>Unable to complete query.  Please do not combine the 'and' and 'or' operators.</li></ul></p>";

define("EWIKI_PAGE_EXALL", "ExAllTodo");

$ewiki_plugins["page"][EWIKI_PAGE_EXALL]="ewiki_page_exall";

function ewiki_page_exall($id=0, $data=0, $action=0){
  global $ewiki_plugins, $ewiki_config;
  
  //$timestart=getmicrotime();
  $action=str_replace("all", "", strtolower($id));
  $invalid=false;
  
  $initials=ewiki_get_uservar("Initials");
  $exinitials=ewiki_get_uservar("ExtractorInitials");
  
  if(isset($_GET['q']) && $_GET['q']!=""){
    if($_GET['q']!='ALL'){
      $str_usr=str_replace(array("or", " ", "and"), array("|", "", "|"), $_GET['q']);
      //$str_usr=$_GET['q']; //initials were entered
      if(!(stristr($_GET['q'], "or")&& stristr($_GET['q'], "and"))){
        if(stristr($_GET['q'], "and")){
          $operator="and";
        }
        $extractFor=$_GET['q'];
      }else {
        $invalid=true;
      }
    }
  }elseif(strlen($initials)){ 
    $str_usr=$initials;
    $extractFor=$str_usr;
  }elseif(strlen($exinitials)){ 
    $str_usr=$exinitials;
    $extractFor=$str_usr;    
  }else {
    $str_usr="."; //no initials were entered
    $o = ewiki_make_title($id, ewiki_t(strtoupper($action)."TITLE"), 2);
  }

  if(isset($extractFor)){
    $o = ewiki_make_title($id, ewiki_t(strtoupper($action)."FOR").$extractFor, 2);
    if(($extractFor != $exinitials)&&($extractFor != $initials)&&(strlen($extractFor)==2)){
      ewiki_set_uservar("ExtractorInitials",$extractFor);
    }
  } else {
    $o = ewiki_make_title($id, ewiki_t(strtoupper($action)."TITLE"), 2);
  }  
 
  $o .= ewiki_t("EXALLCONTROLS", array("controlid"=>$id)); //prints text, textbox, and button
  
  $o.='<p>';
  if(strlen($initials)){ 
    $o.= '<a href="' . ewiki_script("", $id,array('q'=>$initials)) . '">' . ewiki_t(strtoupper($action)."FOR").$initials . "</a> ";
  }
  if(strlen($exinitials)&&($exinitials!=$initials)){ 
    $o.= '<a href="' . ewiki_script("", $id,array('q'=>$exinitials) ). '">' . ewiki_t(strtoupper($action)."FOR").$exinitials . "</a> ";
  }  
  $o.='<a href="' . ewiki_script("", $id,array('q'=>'ALL')) . '">' . ewiki_t(strtoupper($action)."FORALL"). "</a> ".'</p>';

  if($invalid){
    return($o.ewiki_t("EXALLERROR"));
  }
  //define types of todo/policy items
  $ext_types = $ewiki_config["extracttypes"][$action];
  
  //get data from database
  $data = ewiki_db::GETALL(array("content", "pagename", "flags"));
  
  while ($content = $data->get()) {
    $str_null=NULL;
    
    if (($content["flags"] & EWIKI_DB_F_TYPE) == EWIKI_DB_F_TEXT) {
      //code hijacked from action_extracttodo and modified
      preg_match_all("/^([;:#\*\- ]*)((@@(".implode("|",$ext_types).")) ((".$str_usr.")+.*)(:.*))$/im", $content["content"], $matches);

      if(!empty($matches[0])){
        $extractedContent=NULL;
        for($index=0;$index<sizeof($matches[0]);$index++){
          //extract each todo/policy item
          if($operator=="and"){
            $all=true;
            $a_users=explode("|", $str_usr);
            foreach($a_users as $str_user){
              if(!stristr($matches[5][$index],$str_user)){
                $all=false;
              }
            }
            if(!$all){
              continue;                
            }
          }
          //security layer
          if (EWIKI_PROTECTED_MODE && EWIKI_PROTECTED_MODE_HIDING && !ewiki_auth($content["id"], $str_null, $action)) {
            continue; //skip rest of loop and start from beginning
          }
          $extractedContent.="*".$matches[2][$index]."@@\n";              
        }
        if($extractedContent!=NULL){
          $o.=$ewiki_plugins["render"][0]("!!!".ewiki_t(strtoupper($action)."FROM")."[".$content["id"]."]");
          $o .= "<div class='ewiki_page_todolist'>".$ewiki_plugins["render"][0] ( $extractedContent , 1,
          EWIKI_ALLOW_HTML || (@$content["flags"]&EWIKI_DB_F_HTML) )."</div>";
        }
      }
    }
  }

  $o.=$ewiki_plugins["render"][0](ewiki_t(strtoupper($action)."POSTSCRIPT"));
  /*$timeend=getmicrotime();
  $o.="time: ".($timeend-$timestart);*/
  return($o);
}


?>