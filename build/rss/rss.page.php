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
        echo '<?xml version="1.0" encoding="utf-8"?>
<rss version="2.0" xmlns:atom="http://www.w3.org/2005/Atom">
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
    
	
	protected function formatFeedTitle($feed_title = "BeWelcome Forum Feed", 
		$site_link = "", 
		$feed_description = "Feed for the BeWelcome forum")
		{
			
		return
'
	<title>'.$feed_title.'</title>
	<link>'.PVars::getObj('env')->baseuri.$site_link.'</link>
	<description>'.strip_tags($feed_description).'</description>
	<atom:link href="'.PVars::getObj('env')->baseuri.$site_link.'" rel="self" type="application/rss+xml" />  
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