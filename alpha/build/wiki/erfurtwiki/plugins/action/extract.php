<?php

/*
* This plugin extracts todo items(@@TODO, @@DONE, @@CANCELLED ) from the 
* page it is run on.  A change to ewiki_control_links submitted with 
* this plugin adds a link back to the 'normal' view.
* 
* This plugin is designed to work with the EWikiCSS plugins but they are
* not necessary.
* Load this plugin _after_ the core ewiki.php script.
*
* Defining a new set of $ewiki_config["extracttypes"] will allow you to 
* extract other one line items once an $ewiki_plugins["action"]["exXXXX"]
* action is defined.
*
* See http://erfurtwiki.sourceforge.net/?id=TodoExtractorPlugin for more 
* details.
* 
* AndyFundinger(Andy@burgiss.com)
* 
* Add this text to your WikiMarkup page to explain this plugin to your users:

! ToDo Items

* Start a todo item with @@ followed by a todo item type, item types are:
** @@Todo AF: Todo
** @@DONE AF: DONE
** @@cancelled AF: cancelled
** @@dEaDlInE AF: dEaDlInE 
** @@SuBjEcT SuBjEcT
* Case of the class names does not matter.
* DO NOT use a colon ":" after the class name, this may seem logical but it will cause problems for EWikiCSS
* The ErfurtWiki:ToDoExtractorPlugin will extract todos and headlines from pages on which it is used.
* See our ToDoListConvention for more information and guidelines.

*/

 $ewiki_t["en"]["EXTODOFROM"] = "Todos extracted from ";		
 $ewiki_t["en"]["EXTTODO"] = "Extract todo List";		
 $ewiki_t["en"]["VIEWCOMPL"] = "View complete page";		
 $ewiki_t["en"]["EXTODOPOSTSCRIPT"] = "\n\nplease follow our [ToDoListConvention]";		
 $ewiki_t["de"]["EXTODOFROM"] = "ToDos aus ";
 $ewiki_t["de"]["EXTTODO"] = "Zeige ToDo-Liste";
 $ewiki_t["de"]["VIEWCOMPL"] = "Zeige komplette Seite";
 $ewiki_t["de"]["EXTODOPOSTSCRIPT"] = "\n\nBitte folge unserer [ToDoListConvention]";
  
 $ewiki_plugins["action"]["extodo"] = "ewiki_extract";
 $ewiki_config["action_links"]["extodo"]=$ewiki_config["action_links"]["view"];
 $ewiki_config["action_links"]["extodo"]["view"] = ewiki_t("VIEWCOMPL");
 $ewiki_config["action_links"]["view"]["extodo"] = ewiki_t("EXTTODO");

 $ewiki_config["extracttypes"]["extodo"]=array("TODO","DONE","CANCELLED","SUBJECT","DEADLINE");

 function ewiki_extract($id, $data, $action){ 
    global $ewiki_links,$ewiki_config, $ewiki_plugins, $ewiki_ring, $ewiki_title;
 
    $extracttypes = $ewiki_config["extracttypes"][$action];
 
    $o = ewiki_make_title($id, ewiki_t(strtoupper($action)."FROM").$ewiki_title, 2, $action, "", "_MAY_SPLIT=1");
 
				//ignore any number of list markup tags in front of an @@{todotype},
				//extract only the @@, the types, and their message				
					//or				
				//extract any header line							
					//1 2         3  4-Class                      5     6   
	preg_match_all("/^(([-;:#\* ]*)(@@(".implode("|",$extracttypes).")(.*))|(!+.*))$/im",$data["content"],$matches);
	for($index=0;$index<sizeof($matches[0]);$index++){
		//a line will be either header or todo, concatenate the two sub expressions 
		$extractedContent.=$matches[3][$index].$matches[6][$index]."\n\n";
	}
	
	//Render extracted lines as a wiki page, this code extracted from ewiki_page
		
      #-- render requested wiki page  <-- goal !!!
      $o .= "<div class='ewiki_page_todolist'>".$ewiki_plugins["render"][0] ( $extractedContent.ewiki_t(strtoupper($action)."POSTSCRIPT") , 1,
            EWIKI_ALLOW_HTML || (@$data["flags"]&EWIKI_DB_F_HTML) )."</div>";

      #-- control line + other per-page info stuff
      if ($pf_a = $ewiki_plugins["view_append"]) {
         ksort($pf_a);
         foreach ($pf_a as $n => $pf) { 
			
		 	$o .= $pf($id, $data, $action); 
		}
      }
      if ($pf_a = $ewiki_plugins["view_final"]) {
         ksort($pf_a);
         foreach ($pf_a as $n => $pf) { 
		 	if(!preg_match('/_title/',$pf)){
			 	$pf($o, $id, $data, $action); 
			}
		}
      }

    return($o);
 }


?>