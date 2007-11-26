<?php
/*

Copyright (c) 2007 BeVolunteer

This file is part of BW Rox.

BW Rox is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

BW Rox is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, see <http://www.gnu.org/licenses/> or 
write to the Free Software Foundation, Inc., 59 Temple Place - Suite 330, 
Boston, MA  02111-1307, USA.

*/
$words = new MOD_words();
require_once ("magpierss/rss_fetch.inc");
?>

<div class="subcolumns">
  <div class="c66l">
    <div class="subcl">

<div class="subcolumns">
  <div class="c50l">
    <div class="subcl">
<?php
	echo "<div class=\"info\">\n";
	echo "<h3>", $words->get("AboutUs_TheIdea"),"</h3>";
	echo "<p>",$words->get("AboutUs_TheIdeaText"),"</p>";
	echo "<h3>", $words->get("AboutUs_GetActive"),"</h3>";
	echo "<p>",$words->get("AboutUs_GetActiveText"),"</p>";
	echo "<p>",$words->get("AboutUs_Greetings"),"</p>";
	echo "</div>\n";
?> 
    </div>
   </div>


  <div class="c50r">
    <div class="subcr">
<?php	
	echo "<div class=\"info\">\n";
	echo "<h3>", $words->get("AboutUs_HowOrganized"),"</h3>";
	echo "<p>",$words->get("AboutUs_HowOrganizedText"),"</p>";
	echo "<h3>", $words->get("AboutUs_GiveFeedback"),"</h3>";
	echo "<p>",$words->get("AboutUs_GiveFeedbackText"),"</p>";
	echo "</div>\n";
?>		  
    </div>
  </div>
</div>	
    </div>
   </div>
  <div class="c33r">
    <div class="subcr">
<?php    
    $url = 'http://blogs.bevolunteer.org/feed';
    $num_items = 10;
    $rss = fetch_rss($url);
    $items = array_slice($rss->items, 0, $num_items);
    
 	echo "<div class=\"info\">\n";   
    echo "<h3>", $rss->channel['title'], "</h3><br>
    ";
    foreach ($items as $item ) {
    	$title = $item['title'];
    	$url   = $item['link'];
    	$description   = $item['description'];   
    /*	$subject = $item ['dc'] ['subject']; */
    	/*$startdate   = $item['date'];
    	$type   = $item['type'];   
    	$author   = $item['author'];     */     
    	echo "<h2><a href=\"",$url,"\">",$title,"</a></h2>
        <p>",$description,"</p>
        
    ";
    } 
        echo "<a href=\"http://blogs.bevolunteer.org\">", $words->get("getMoreEntriesandComments"),"</a>\n";   
	echo "</div>\n";        
?>
	 
    </div>
  </div>
</div>	