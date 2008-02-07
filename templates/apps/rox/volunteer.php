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
?>

<div class="subcolumns">
  <div class="c50l">
    <div class="subcl">

<?php
	echo "<div class=\"info\">\n";
	echo "<h3>", $words->get("Volunteer_Join"),"</h3>";
	echo "<p>",$words->get("Volunteer_JoinText"),"</p>";
	echo "<h3>", $words->get("Volunteer_Attention"),"</h3>";	
	echo "<p>Here you will find a list of things you as a volunteer should check (poll, discussion) derived from wordpress blog or trac<p>";
    ssi_recentPoll(); 
	echo "</div>\n";
?> 


<?php    
    $url = 'http://www.bevolunteer.org/trac/query?status=new&status=assigned&status=reopened&format=rss&owner=philipp&order=priority';
    $num_items = 2;
//    $rss = fetch_rss($url);
//    $items = array_slice($rss->items, 0, $num_items);
    
 	echo "<div class=\"info\">\n";   
    echo "<h3>" . $words->get('VolunteerMyTasks') . "</h3><br> ";
    echo "<p>Here you will find a list of your tasks derived from trac (once trac rss output is repaired)<p>";
/*    foreach ($items as $item ) {
    	$title = $item['title'];
    	$url   = $item['link'];
    	$description   = $item['description'];   
    //	$subject = $item ['dc'] ['subject']; 
    	$startdate   = $item['date'];
    	$type   = $item['type'];   
    	$author   = $item['author'];         
    	echo "<h1><a href=\"",$url,"\">",$title,"</a></h1>
        <p>",$description,"</p>    ";	
    } */
	echo "</div>\n";

	    $url = 'http://trac.edgewall.org/query?status=new&status=assigned&status=reopened&group=type&format=rss&order=priority';
    $num_items = 50	;
    $rss = fetch_rss($url);
    $items = array_slice($rss->items, 0, $num_items);
    
 	echo "<div class=\"info\">\n";   
    echo "<h3>" . $words->get('VolunteerHotTasks') . "</h3><br> ";
    echo "<p>Here you will find a list of hot tasks derived from trac (once trac rss output is repaired)<p>";
    foreach ($items as $item ) {
    	$title = $item['title'];
    	$url   = $item['link'];
    	$description   = $item['description'];   
    //	$subject = $item ['dc'] ['subject']; 
    //	$startdate   = $item['date'];
    //	$type   = $item['type'];   
    //	$author   = $item['author'];         
    //	echo "<h2><a href=\"",$url,"\">",$title,"</a></h2>";
		echo "<p><a href=\"",$url,"\">",$title,"</a></p>";
    //    <p>",$description,"</p>    ";	
    } 
	echo "</div>\n";
?>
		  
    </div>
  </div>
  <div class="c50r">
    <div class="subcr">
<?php    
    $url = 'http://blogs.bevolunteer.org/feed';
    $num_items = 3;
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
    	$date   = $item['pubdate'];
    	/*$type   = $item['type'];   
    	$author   = $item['author'];     */     
    	echo "<h2><a href=\"",$url,"\">",$title,"</a></h2>
        <p>",$date,"</p>
        <p>",$description,"</p>
        
    ";
    } 
        echo "<a href=\"http://blogs.bevolunteer.org\">", $words->get("getMoreEntriesandComments"),"</a>\n";   
	echo "</div>\n";

	$url = 'http://blogs.bevolunteer.org/internal/feed';
    $num_items = 3;
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
    	$date   = $item['pubdate'];
    	/*$type   = $item['type'];   
    	$author   = $item['author'];     */     
    	echo "<h2><a href=\"",$url,"\">",$title,"</a></h2>
        <p>",$date,"</p>
        <p>",$description,"</p>
        
    ";
    } 
        echo "<a href=\"http://blogs.bevolunteer.org/internal\">", $words->get("getMoreEntriesandComments"),"</a>\n";   
	echo "</div>\n";
	
	$url = 'http://blogs.bevolunteer.org/tech/feed';
    $num_items = 3;
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
    	$date   = $item['pubdate'];
    	/*$type   = $item['type'];   
    	$author   = $item['author'];     */     
    	echo "<h2><a href=\"",$url,"\">",$title,"</a></h2>
        <p>",$date,"</p>
        <p>",$description,"</p>
        
    ";
    } 
        echo "<a href=\"http://blogs.bevolunteer.org/tech\">", $words->get("getMoreEntriesandComments"),"</a>\n";   
	echo "</div>\n";
?>
	 
    </div>
  </div>
</div>	