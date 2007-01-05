<?php
require_once("Menus_micha.php") ;
function DisplayComments($m,$TCom) {
  global $title ;
  $title=ww('ViewComments') ;
  include "header_micha.php" ;
	
	Menu1() ; // Displays the top menu

	Menu2("member.php") ;
// Header of the profile page
  require_once("profilepage_header.php") ;

echo "	<div id=\"columns\">" ;
menumember("viewcomments.php?cid=".$m->id,$m->id,$m->NbComment) ;
echo "		<div id=\"columns-low\">" ;

echo "\n    <!-- leftnav -->"; 
echo "     <div id=\"columns-left\">\n"; 
echo "       <div id=\"content\">"; 
echo "         <div class=\"info\">"; 
echo "           <h3>Actions</h3>"; 

echo "           <ul>"; 
echo "               <li><a href=\"addcomments.php?cid=".$m->id."\">",ww("addcomments"),"</a></li>"; 
echo "               <li><a href=\"todo.php\">Add to my list</a></li>"; 
echo "               <li><a href=\"todo.php\">View forum posts</a></li>"; 
echo "           </ul>"; 
echo "         </div>"; 
echo "       </div>\n"; 
echo "     </div>\n"; 

echo "\n    <!-- rightnav -->"; 
echo "     <div id=\"columns-right\">\n" ;
echo "       <ul>" ;
echo "         <li class=\"label\">",ww("Ads"),"</li>" ;
echo "         <li></li>" ;
echo "       </ul>\n" ;
echo "     </div>\n" ;

echo "			<div class=\"clear\" />" ;
  echo "\n<center>\n" ;
  echo "<table>\n" ;


	$iiMax=count($TCom) ;
	$tt=array() ;
	for ($ii=0;$ii<$iiMax;$ii++) {
	  $color="black" ;
	  if ($TCom[$ii]->Quality=="Good") {
		  $color="#808000" ;
		}
	  if ($TCom[$ii]->Quality=="Bad") {
		  $color="red" ;
		}
    echo "<tr><td valign=center>" ;
		echo "<ul>" ;
		echo "<li>" ;
    echo "<b>",ww("CommentFrom",$TCom[$ii]->Commenter),"</b><br><br>" ;
		echo "<li>" ;
		echo "</li>" ;
    echo "<i>",$TCom[$ii]->TextWhere,"</i>" ;
    echo "<br><font color=$color>",$TCom[$ii]->TextFree,"</font>" ;
		echo "</li>" ;
		echo "</ul>" ;
    echo "</td>" ;
		$tt=explode(",",$TCom[$ii]->Lenght) ;
		echo "<td>" ;
		for ($jj=0;$jj<count($tt);$jj++) {
		  echo "&nbsp;&nbsp;&nbsp;<li>",ww("Comment_".$tt[$jj]),"</li><br>" ;
		} 
		
		echo "</td>" ;
	}
  
  echo "</table>\n" ;
	
  echo "</center>\n" ;
echo "					<div class=\"user-content\">" ;
  include "footer.php" ;
echo "					</div>" ;
}

?>
