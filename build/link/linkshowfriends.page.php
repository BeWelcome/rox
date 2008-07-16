<?php


/**
 * Hello universe page with a form that sends POST data.
 * Have a look at the superclasses for the layout definitions..
 *
 * @package hellouniverse
 * @author Andreas (lemon-head)
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License (GPL)
 * @version $Id$
 */
class LinkShowFriendsPage extends LinkPage
{
    protected function column_col3()
    {
        $page_url = PVars::getObj('env')->baseuri . implode('/', PRequest::get()->request);
        
		echo '
			<p>
			First rough draft for a friends system<br>
			show friends: list friends for a given username / id and a given distance<br>
			show links: show one or more links between two given members<br>
			update links: flush the link database and create new entries
			<p>
			
			<p>
			So far data from comments and special relations is taken into consideration.
			<p>
			
			<p>
			Stuff like Preference setting to hide/disable oneself from the link system and more is still needed
			<p>
			';
        
        echo '
			<p>
			<form method="POST" action="'.$page_url.'">
			'.$this->layoutkit->formkit->setPostCallback('LinkController', 'LinkShowFriendsCallback').'
			From: <input name="from"/> | Degree: <input name="degree"/> | Limit: <input name="limit"/>
			<input type="submit" value="send"/>
			</form>
			</p>
        ';
		
		if (!$mem_redirect = $this->layoutkit->formkit->getMemFromRedirect()) {
            } else {
            // result from calculation
            echo '
			<p>
			Your Query: Show '.$mem_redirect->limit.' Friends of:'.$mem_redirect->from.' with a distance of: '.$mem_redirect->degree.'
			</p>
           ';

			$model = new LinkModel();		   
			echo "<p>The IDs for the Friends (retrieved by getFriends): ";

			foreach ($mem_redirect->friendsIDs as $value) {
				echo $value ." / ";
			}
			echo "</p>";
			

			$friendsData = $mem_redirect->friendsFull;
			//var_dump($friendsData);
			require 'templates/linkshowfriendspage_people.php';
	   }
        
    }
	
	
    
    
    protected function teaserHeadline()
    {
        echo 'Show Friends';
    }
    
    
    protected function getPageTitle() {
        return 'Link it';
    }
}


?>