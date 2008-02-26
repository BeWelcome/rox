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
$id = 0;
?>
<script src="script/prototype.js" type="text/javascript"></script>
<script src="script/effects.js" type="text/javascript"></script>

<script type="text/javascript">
var RollIt = {
    timeout : null,
    showPopup : function(e){
        clearTimeout(this.timeout);
        if($(e).style.display == 'none'){
            this.timeout = setTimeout(function(){new Effect.BlindDown(e, {duration:.3, fps:40})},400);
        }
    },
    hidePopup : function(e){
        if($(e).style.display == 'none'){
            clearTimeout(this.timeout);
        }else{
            this.timeout = setTimeout(function(){new Effect.BlindUp(e, {duration:.3, fps:40})},300);
        }
    }    
}
</script>


<div class="subcolumns">
  <div class="c50l">
    <div class="subcl">

<?php
	
// stuff only for members of group volunteer
	if ($isvolunteer) {

// attention of volunteers needed	
		$url = "http://www.bevolunteer.org/trac/query?status=new&status=assigned&status=reopened&format=rss&type=volunteer+attention&order=priority";
	    $num_items = 10	;
		$MAGPIE_CACHE_ON = false;
	    $rss = fetch_rss($url);
	    $items = array_slice($rss->items, 0, $num_items);	
		echo "<div class=\"info\">\n";
		echo "<h3>", $words->get("Volunteer_Attention"),"</h3>";	
//		echo "<p>Here you will find a list of things you as a volunteer should check (poll, discussion) derived from wordpress blog or trac, to be discussed<p>";
	    foreach ($items as $item ) {
			$id = $id + 1;
			$title = $item['title'];
	    	$url   = $item['link'];
	    	$description   = $item['description'];   
		echo "<p><a href=\"",$url,"\" target=\"blank\" onmouseout=\"RollIt.hidePopup('",$id,"')\" onmouseover=\"RollIt.showPopup('",$id,"')\">",$title,"</a></p>\n";
		echo "<div id=\"",$id,"\" style=\"display:none;\" onmouseout=\"RollIt.hidePopup('",$id,"')\" onmouseover=\"RollIt.showPopup('",$id,"')\">";
		echo $description;
		echo "</div>";
	    } 
		echo "</div>\n";
		

//my tasks trac

		$url = "http://www.bevolunteer.org/trac/query?status=new&status=assigned&status=reopened&format=rss&owner=". $_SESSION['Username'] ."&order=priority";
	    $num_items = 10	;
		$MAGPIE_CACHE_ON = false;
	    $rss = fetch_rss($url);
	    $items = array_slice($rss->items, 0, $num_items);
	 	echo "<div class=\"info\">\n";   
	    echo "<h3>" . $words->get('VolunteerMyTasks') . "</h3><br> ";
//	    echo "<p>Here you will find a list of recent polls derived from BV forum<p>";
	    foreach ($items as $item ) {
			$id = $id + 1;
	    	$title = $item['title'];
	    	$url   = $item['link'];
	    	$description   = $item['description'];   
		echo "<p><a href=\"",$url,"\" target=\"blank\" onmouseout=\"RollIt.hidePopup('",$id,"')\" onmouseover=\"RollIt.showPopup('",$id,"')\">",$title,"</a></p>\n";
		echo "<div id=\"",$id,"\" style=\"display:none;\" onmouseout=\"RollIt.hidePopup('",$id,"')\" onmouseover=\"RollIt.showPopup('",$id,"')\">";
		echo $description;
		echo "</div>";
	    } 
		echo "</div>\n";
		
	}

// stuff only for normal BW members, to hook them up	
	else {
	
		echo "<div class=\"info\">\n";
		echo "<h3>", $words->get("Volunteer_Join"),"</h3>";
		echo "<p>",$words->get("Volunteer_JoinText"),"</p>";
		echo "</div>\n";
	}

// stuff for volunteers and members	
//hot tasks trac	
	$url = "http://www.bevolunteer.org/trac/query?status=new&status=assigned&status=reopened&format=rss&show_on_bw=1&order=priority";
    $num_items = 10	;
	$MAGPIE_CACHE_ON = false;
    $rss = fetch_rss($url);
    $items = array_slice($rss->items, 0, $num_items);
    
 	echo "<div class=\"info\">\n";   
    echo "<h3>" . $words->get('VolunteerHotTasks') . "</h3><br> ";
//    echo "<p>Here you will find a list of hot tasks derived from trac. Description is available but needs a nice way to display first, for example big tooltip for mouseover<p>";
    foreach ($items as $item ) {
		$id = $id + 1;
    	$title = $item['title'];
    	$url   = $item['link'];
    	$description   = $item['description'];   
		echo "<p><a href=\"",$url,"\" target=\"blank\" onmouseout=\"RollIt.hidePopup('",$id,"')\" onmouseover=\"RollIt.showPopup('",$id,"')\">",$title,"</a></p>\n";
		echo "<div id=\"",$id,"\" style=\"display:none;\" onmouseout=\"RollIt.hidePopup('",$id,"')\" onmouseover=\"RollIt.showPopup('",$id,"')\">";
		echo $description;
		echo "</div>";
    } 
	echo "</div>\n";
	
// //froum recent polls
	// $url = 'http://www.bevolunteer.org/forum/index.php?type=rss&action=.xml';
    // $num_items = 50	;
    // $rss = fetch_rss($url);
    // $items = array_slice($rss->items, 0, $num_items);
    
 	// echo "<div class=\"info\">\n";   
    // echo "<h3>" . $words->get('VolunteerBVForumPolls') . "</h3><br> ";
    // echo "<p>Here you will find a list the latest BV forum polls if we keep them all in one board<p>";
    // foreach ($items as $item ) {
    	// $title = $item['title'];
    	// $url   = $item['link'];
    	// $description   = $item['description'];
        // $category = $item['category'];
		// $date = $item['pubdate'];
		// echo "<p>",$category," <a href=\"",$url,"\" target=\"blank\" >",$title,"</a></p>";
    // } 
	// echo "</div>\n";	

	
// //froum recent posts	
	// $url = 'http://www.bevolunteer.org/forum/index.php?type=rss&action=.xml';
    // $num_items = 50	;
    // $rss = fetch_rss($url);
    // $items = array_slice($rss->items, 0, $num_items);
    
 	// echo "<div class=\"info\">\n";   
    // echo "<h3>" . $words->get('VolunteerBVForumPosts') . "</h3><br> ";
    // echo "<p>Here you will find a list the latest BV forum posts<p>";
    // foreach ($items as $item ) {
    	// $title = $item['title'];
    	// $url   = $item['link'];
    	// $description   = $item['description'];
        // $category = $item['category'];
		// $date = $item['pubdate'];
		// echo "<p>",$category," <a href=\"",$url,"\" target=\"blank\" >",$title,"</a></p>";
    // } 
	// echo "</div>\n";	
	
	
// //mediawiki recent changes
	// $url = 'http://www.bevolunteer.org/forum/index.php?type=rss&action=.xml';
    // $num_items = 50	;
    // $rss = fetch_rss($url);
    // $items = array_slice($rss->items, 0, $num_items);
    
 	// echo "<div class=\"info\">\n";   
    // echo "<h3>" . $words->get('VolunteerBVWiki') . "</h3><br> ";
    // echo "<p>Here you will find a list the latest changes in Mediawiki once it is public<p>";
    // foreach ($items as $item ) {
    	// $title = $item['title'];
    	// $url   = $item['link'];
    	// $description   = $item['description'];   
    // //	$subject = $item ['dc'] ['subject']; 
    // //	$startdate   = $item['date'];
    // //	$type   = $item['type'];   
    // //	$author   = $item['author'];         
    // //	echo "<h2><a href=\"",$url,"\">",$title,"</a></h2>";
		// echo "<p><a href=\"",$url,"\" target=\"blank\" >",$title,"</a></p>";
    // //    <p>",$description,"</p>    ";	
    // } 
	// echo "</div>\n";	
	
//Trac changes	
	$url = 'http://www.bevolunteer.org/trac/timeline?milestone=on&ticket=on&wiki=on&max=10&daysback=90&format=rss';
    $num_items = 10	;
	$MAGPIE_CACHE_ON = false;
    $rss = fetch_rss($url);
    $items = array_slice($rss->items, 0, $num_items);
    
 	echo "<div class=\"info\">\n";   
    echo "<h3>" . $words->get('VolunteerTracWikiChanges') . "</h3><br> ";
//    echo "<p>Here you will find a list of the latest changes in the Trac wiki<p>";
    foreach ($items as $item ) {
		$id = $id + 1;	
    	$title = $item['title'];
    	$url   = $item['link'];
    	$date   = $item['pubdate'];
    	$description   = $item['description']; 		
		echo "<p>",$date," <br> <a href=\"",$url,"\" target=\"blank\" onmouseout=\"RollIt.hidePopup('",$id,"')\" onmouseover=\"RollIt.showPopup('",$id,"')\">",$title,"</a></p>\n";
		echo "<div id=\"",$id,"\" style=\"display:none;\" onmouseout=\"RollIt.hidePopup('",$id,"')\" onmouseover=\"RollIt.showPopup('",$id,"')\">";
		echo $description;
		echo "</div>";		
    } 
	echo "</div>\n";

//	echo "<div><iframe NAME='traclogin' WIDTH='200' HEIGHT='200' src=\"http://". $_SESSION['Username'] ."@www.bevolunteer.org/trac/login\"></iframe></div>";



	
	
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
	
	
?>
	 
    </div>
  </div>
</div>	