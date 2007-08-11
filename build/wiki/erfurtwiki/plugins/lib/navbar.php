<?php

/* 
Ewiki nav bar plugin - by AndyFundinger

this plugin will create a navigation bar from a site map on a page.  The site 
map should be a list with wiki words or [] style wiki links.  You must select 
two functions to control the operation of your navigation bar.  
$ewiki_plugins['select_navbar_buttons'][0] will be run to determine which 
buttons are invisible($NavBar[$id][2] = 0), visible($NavBar[$id][2] = 1), or 
active ($NavBar[$id][2] = 2).  $ewiki_plugins['render_navbar_section'][0] will 
control the actual rendering and is designed to support recursive calls.  Two 
versions of each are supported.  You can also create your own site specific 
selector or renderer.  I use a function that calls 
ewiki_navbar_selectall_buttons() to select the level 1 pages and then 
ewiki_navbar_select_psuc() to select parents, siblings, uncles, and children if 
possible.

The effect of this NavBar relies on stylesheets.  You must add several classes
to make the NavBar appear properly.  An example style sheet is 
WikiNavBarStyle.css.  The following tag will add this example style sheet to your
code:

<link rel='stylesheet' href='./fragments/WikiNavBarStyle.css' type='text/css' media='all' />

The NavBar array is composed of one line per button as follows:
$NavBar[]=array( line text, depth, activation,target page);

Call mkLiveWebNavBar() from within your page to create a NavBar.
*/

define("EWIKI_NAVBAR_SELECTALL_DEPTH",99);
define("EWIKI_NAVBAR_ACTIVATEPARENTS",1);
define("EWIKI_NAVBAR_ACTIVATECHILDREN",1);
define('EWIKI_NAVBAR_ACTION','view');

//Select only Parents, Siblings, Uncles, and Children
$ewiki_plugins['select_navbar_buttons'][]="ewiki_navbar_select_psuc";
//Select entire menu, highlight current page, selects to depth of 
// EWIKI_NAVBAR_SELECTALL_DEPTH
$ewiki_plugins['select_navbar_buttons'][]="ewiki_navbar_selectall_buttons";
//Renders NavBar as a set of nested lists 
$ewiki_plugins['render_navbar_section'][]='ewiki_render_navbar_list';
//Alternate render routine that puts sub bars below the major categories.
$ewiki_plugins['render_navbar_section'][]='ewiki_render_navbar_fixedtop_list';

//Create a navBar, call with a page to be turned into the bar and a page to be 
// selected, uses $ewiki_plugins['select_navbar_buttons'][0] and 
// $ewiki_plugins['render_navbar_section'][0].  Returns nothing if no bar is 
// created.
function mkLiveWebNavBar($navBarPage, $activePage){
    #-- fetch from db
    $data = ewiki_db::GET($navBarPage);
    
    #-- Authenticate, return nothing if authentication fails
    if (!ewiki_auth($navBarPage, $data, EWIKI_NAVBAR_ACTION, $ring=3, $force=0)) {
         return("");
    }

    #-- escape if navBarPage does not exist or was not retrieved.  
    if(empty($data["content"])){
        return("");
    }
    //echo(":".$data["content"].":");
    
    $o .= ewiki_navbar_format ( $data["content"], 1,urlencode($activePage) );

    //Apply class only if we have a bar at all
    if($o)
        return(" <div class='wikiNavBar' >\n".$o."\n</div>\n");
    return("");
}

//An adaptation of ewiki_format to format navigation bars
function ewiki_navbar_format ($wiki_source, $scan_links=1,
    $currpage=EWIKI_PAGE_INDEX)
{
    global $ewiki_links, $ewiki_plugins;
    
    //echo("navbar function run");
    // pre-scan WikiLinks
    if ($scan_links) {
        ewiki_scan_wikiwords($wiki_source, $ewiki_links);
    }
    
    // formatted output
    $o = "\n";
    
    // plugins only format finals are respected
    $pf_final = @$ewiki_plugins["format_final"];
    
    $table_defaults = 'cellpadding="2" border="1" cellspacing="0"';
    $syn_htmlentities = array(
        "&" => "&amp;",
        ">" => "&gt;",
        "<" => "&lt;",
        "%%%" => "<br />"
    );
    $wm_list = array(
        "-" => array('ul type="square"', "", "li"),
        "*" => array('ul type="circle"', "", "li"),
        "#" => array("ol", "", "li"),
        ":" => array("dl", "dt", "dd"),
        ";" => array("dl", "dt", "dd"),
    );

   // eleminate html
    foreach ($syn_htmlentities as $find=>$replace) {
        $wiki_source = str_replace($find, $replace, $wiki_source);
    }
    array_pop($syn_htmlentities);   // strips "&amp;"
    
    // add a last empty line to get opened tables/lists closed correctly
    $wiki_source = trim($wiki_source) . "\n";
    
    
    #-- finally the link-detection-regex
    #   (impossible to do with the simple string functions)
    ewiki_render_wiki_links($wiki_source);
        
    $NavBar = array();
    
                //return($wiki_source);
    foreach (explode("\n", $wiki_source) as $line) {

        $line = rtrim($line);
        $lineout="";

        #-- wiki list markup
        if ( strlen($line) && isset($wm_list[@$line[0]]) ) {
            $n = 0;
            $li = "";
            #-- count depth of list
            #  	line has length		first character in the line is in wm_list
            while (strlen($line) && ('*'==$line[0]) ) {
                $li .= '*';	//add new list delim to list count
                $n++;			//count depth
                $line = substr($line, 1);	//remove first character 
            }
            $line = ltrim($line);
            
            $regex='#<a href=["\'](.*)'.preg_quote(EWIKI_SCRIPT) .'(.*?)["\'&?^](.*)#i';
            preg_match($regex,$line,$matches=array());

            $href=$matches[2];
            //echo($regex.$line."HREF'".$href."'");

            $NavBar[]=array($line,$n,0,$href);
            //echo($line.$n.count($NavBar));

        }
   }
   
    //$NavBar now contains all elements in the navigation bar.
      
    //return("");

    $NavBar=$ewiki_plugins['select_navbar_buttons'][0]($NavBar,$currpage);
    
    reset($NavBar);
    $pre='';
    $post='';
    $barText=$ewiki_plugins['render_navbar_section'][0]($pre,$post,$NavBar);
    $barText=$pre.$barText.$post;
      
    //Cut out if we have no navigation bar
    if(!$barText)
        return("");    
    
    #-- close last line
    $o .= $barText."\n";
    
    #-- international characters
    if (EWIKI_HTML_CHARS) {
        $o = str_replace("&amp;#", "&#", $o);
    }
    
    
    #-- call post processing plugins
    if ($pf_final) {
        foreach ($pf_final as $pf) $pf($o);
    }
    
    return($o);
}


//Select entire menu, highlight current page, selects to depth of 
// EWIKI_NAVBAR_SELECTALL_DEPTH, or can be called with a specified depth.
function ewiki_navbar_selectall_buttons( $NavBar,$currpage,$maxlevel=EWIKI_NAVBAR_SELECTALL_DEPTH){
    foreach($NavBar as $id=>$row){
        //echo("($id,$currpage)");
        if($row[1]>$maxlevel){
            next;
        }elseif($row[3]==$currpage){
            $NavBar[$id][2]=2;
        }else{
            $NavBar[$id][2]=1;            
        }
        //echo("($id,$row[0],".$NavBar[$id][2].")");
    }
    return($NavBar);
}

//Selects parents, siblings, uncles and children
function ewiki_navbar_select_psuc( $NavBar,$currpage){
    foreach($NavBar as $id=>$row){
        //echo("($row[3],$currpage)");
        if($row[3]==$currpage){
            $NavBar[$id][2]=2;
            $selLev=$row[1];
            
            for($index=$id-1;$index>=0;$index--){
                if($NavBar[$index][1]<=$selLev){
                    //set to displayed or active depending on ACTIVATEPARENTS
                    $NavBar[$index][2]=
                       ((($NavBar[$index][1]==$selLev)||
                           (!EWIKI_NAVBAR_ACTIVATEPARENTS))? 1 : 2);  
                    $selLev=$NavBar[$index][1];    //reduce selected level
                }
            }
            
            $selLev=$row[1]+1;  
            for($index=$id+1;$index<count($NavBar);$index++){ 
                //echo(":".$selLev.":(".$index.$NavBar[$index][0].$NavBar[$index][1].")");
                
                if($NavBar[$index][1]<=$selLev){
                    if(!EWIKI_NAVBAR_ACTIVATECHILDREN){
                        $NavBar[$index][2]=1;  //set to displayed
                    }elseif($NavBar[$index][1]==$row[1]+1){
                        $NavBar[$index][2]=2;  //set to active
                    }else{
                        $NavBar[$index][2]=1;  //set to displayed                    
                    }
                    $selLev=$NavBar[$index][1];    //reduce selected level
                }
            } //*/
        }
        //echo($id.$NavBar[$id][2]);
    }
    return($NavBar);
}

//Render nav bar with sub menus appearing below entire bar
function ewiki_render_navbar_fixedtop_list($pre,$post,&$NavBar){

    $element=current($NavBar);
    $currLevel=$element[1];
    //echo("processing".$currLevel.$uu[0]);
    $myPre="<ul class='wikiNavBarDepth".$currLevel."'>";
    $myPost="</ul>";
    do{        
        if(($element[1]>$currLevel)){
            $myPost.=ewiki_render_navbar_fixedtop_list($myPre,$myPost,$NavBar);
            $element=current($NavBar);
            continue;
        }elseif($element[2]>=2){
            $mySection.="<li class='activebutton'>".$element[0]."</li>";
        }elseif($element[2]>=1){
            $mySection.="<li class='inactivebutton'>".$element[0]."</li>";        
        }else{
            //$mySection.=$element[0]            ."($element[1],$element[2])";        
        }
        $element=next($NavBar);
    }while(($element[1]>=$currLevel));
    
    //Return list if there are elements
    if($mySection)
        return($myPre.$mySection.$myPost);
    return("");
}

//Render nav bar as a simple list
function ewiki_render_navbar_list($pre,$post,&$NavBar){

    $element=current($NavBar);
    $currLevel=$element[1];
    //echo("processing".$currLevel.$uu[0]);
    $myPre="<ul class='wikiNavBarDepth".$currLevel."'>";
    $myPost="</ul>\n";
    do{        
        if(($element[1]>$currLevel)){
            $mySection.=ewiki_render_navbar_list($myPre,$myPost,$NavBar);
            $element=current($NavBar);
            continue;
        }elseif($element[2]>=2){
            $mySection.="<li class='activebutton'>".$element[0]."</li>";
        }elseif($element[2]>=1){
            $mySection.="<li class='inactivebutton'>".$element[0]."</li>";        
        }else{
            //$mySection.=$element[0]            ."($element[1],$element[2])";        
            
        }
        $element=next($NavBar);
    }while(($element[1]>=$currLevel));
    
    //Return list if there are elements
    if($mySection)
        return($myPre.$mySection.$myPost);   
    return("");
}



?>