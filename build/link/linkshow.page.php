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
class LinkShowPage extends LinkPage
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
			'.$this->layoutkit->formkit->setPostCallback('LinkController', 'LinkShowCallback').'
			From: <input name="from"/> > To: <input name="to"/> | Limit: <input name="limit"/>
			<input type="submit" value="send"/>
			</form>
			</p>
        ';
		
		if (!$mem_redirect = $this->layoutkit->formkit->getMemFromRedirect()) {

        } else {
            // result from calculation
            echo '
			<p>
			Your Query: Show '.$mem_redirect->limit.' shortest connections between'.$mem_redirect->from.' and: '.$mem_redirect->degree.'
			</p>
           ';
		$model = new LinkModel();

		foreach ($mem_redirect->links as $key => $value) {
			foreach ($value as $id) {
				echo $id." >";
			}
			echo "<br>";
		}
		
		$linksData = $mem_redirect->linksFull;
		require 'templates/linkshowlinkpage_people.php';
	   
        }
    }
	
	
    
    
    protected function teaserHeadline()
    {
        echo 'Show Links between members';
    }
    
    
    protected function getPageTitle() {
        return 'Link it';
    }
}


?>