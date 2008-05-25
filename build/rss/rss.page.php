<?php
/** RSS view
 * 
 * @package rss
 * @author Anu (narnua)
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License (GPL)
 * @version $Id$
 */

class PageWithGivenRSS extends AbstractBasePage 
{
	
    public function render()
    {
        $this->posts = $this->_model->getPosts();
        //UNcomment the following line to debug the rss before feed reader grabs it!
		//echo ".<pre>";
		//AND COMMENT the following 
        header('Content-type: text/xml');
        echo '<?xml version="1.0" encoding="iso-8859-1"?>
<rss version="2.0">
<channel>';
        
        $this->showHeader();
        
        foreach ($this->posts as $post) {
            $this->showItem($post);
        }
        
        echo '</channel>
</rss>';
        PVars::getObj('page')->output_done = true;
    }
    
    
    public function setModel($model) {
    	$this->_model = $model;
    }
    
    public function setPosts($posts) {
    	$this->posts = $posts;
    }

	
	protected function formatFeedTitle($feed_type = "Forum", 
		$feed_link = "", 
		$feed_description = "Feed for the BeWelcome forum") 
		{
			
		return
'<atom:link href="'.PVars::getObj('env')->baseuri.'rss/'.$feed_link.'" rel="self" type="application/rss+xml" />
  <title>BeWelcome '.$feed_type.' Feed</title>
  <link>'.PVars::getObj('env')->baseuri.'rss/'.$feed_link.'</link>
  <description>'.strip_tags($feed_description).'</description>  
'
		;
		
	}
	
		

	protected function formatFeedItem($title="", $message="", $pubdate, $link="") {
		$phpdate = strtotime( $pubdate );
		$pubdate = date("D, d M Y H:i:s", $phpdate)." GMT";
		return "
		  <item>
		    <title>".strip_tags($title)."</title>
		    <description>".strip_tags($message)."</description>
		    <pubDate>$pubdate</pubDate>
		    <category>BeWelcome</category>
		   	<guid>".PVars::getObj('env')->baseuri.$link."</guid>
		    <link>".PVars::getObj('env')->baseuri.$link."</link>
		  </item>
		";		
	}	
	
	    
}





?>