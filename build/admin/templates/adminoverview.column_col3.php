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
require_once 'simplepie/autoloader.php';
$words = new MOD_words($this->getSession());
$id = 0;
?>
<script type="text/javascript">
var RollIt = {
    timeout : null,
    showPopup : function(e){
        clearTimeout(this.timeout);
        if($(e).style.display == 'none'){
        	// change duration - cause faster movement   
            this.timeout = setTimeout(function(){new Effect.BlindDown(e, {duration:.4, fps:40})},0);
        }
    },
    hidePopup : function(e){
        if($(e).style.display == 'none'){
            clearTimeout(this.timeout);
        }else{
        	// change duration - cause faster movement
            this.timeout = setTimeout(function(){new Effect.BlindUp(e, {duration:.1, fps:40})},0);
        }
    }    
}
</script>

<div class="info">
<?php echo $words->get("Volunteer_Introduction");?>
</div>

<div class="subcolumns">
  <div class="c50l">
    <div class="subcl">

<?php
// attention of volunteers needed	
    $feed = new SimplePie();
    $feed->set_feed_url('http://trac.bewelcome.org/query?changetime=1weekago..&format=rss&report=10&desc=1&order=changetime&status=new&status=reopened');
    $feed->init();
    $feed->handle_content_type();
		if (!$feed) 
        {
		echo "<div class=\"info\">\n Failed to fetch information from trac.\n </div>\n";
		} else 
        {
			echo "<div class=\"info\">\n";
			echo "<h3>", $words->get("Volunteer_Attention"),"</h3>";
               foreach ($feed->get_items(0, 10) as $item ) 
                {
                $id = $id + 1;
                $title = $item->get_title();
    	        $url   = $item->get_permalink();
    	        $description   = $item->get_description();   
                echo "<p><a href=\"",$url,"\" target=\"blank\" onmouseout=\"RollIt.hidePopup('",$id,"')\" onmouseover=\"RollIt.showPopup('",$id,"')\">",$title,"</a></p>\n";
			    echo "<div id=\"",$id,"\" style=\"display:none;\" onmouseout=\"RollIt.hidePopup('",$id,"')\" onmouseover=\"RollIt.showPopup('",$id,"')\">";
			    echo $description;
			    echo "</div>";
		        } 
			echo "</div>\n";
		}

?>
		  
    </div>
  </div>
  <div class="c50r">
    <div class="subcr">
<?php
    $feed = new SimplePie();
    $feed->set_feed_url('http://www.bewelcome.org/rss/blog/tags/Community%20News%20for%20the%20frontpage');
    $feed->init();
    $feed->handle_content_type();
    $flink = $feed->get_permalink();     
    echo "<h3><a href=\"",$flink,"\">",$words->get('CommunityNews'),"</a></h3>";
    foreach ($feed->get_items(0, 2) as $item ) {
    	$title = $item->get_title();
    	$url   = $item->get_permalink();
    	$description   = $item->get_description();   
    	$date   = $item->get_date('j F Y');
    	echo "<h4><a href=\"",$url,"\">",$title,"</a></h4>
        <p>",$date,"</p>
        <p>",$description,"</p>
        
    ";
    } 
        echo "<a href=\"http://www.bewelcome.org/blog/tags/Community%20News%20for%20the%20frontpage\">", $words->get("getMoreEntriesandComments"),"</a>\n";   
?>	



	 
    </div>
  </div>
</div>
