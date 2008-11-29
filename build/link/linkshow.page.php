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
		protected $Data ; // Receives the result as constructor received it (if any) 

    function __construct($selected_tab,$result="")
    {
				$this->Data=$result ;
        parent::__construct($selected_tab);
    }

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
			if you don\'t want your username to be display among the results of these links, you can choose to disable the tree link in your preferences
			<p>
		';
		
			if (!empty($this->Data)) { // For the case the display results comes from adn URL an not from a form
				$mem_redirect = $this->Data ;
			} 
			else {
				$mem_redirect = $this->layoutkit->formkit->getMemFromRedirect() ;
			}
//			print_r($mem_redirect) ; die ("stop") ;
			if (isset($mem_redirect) and isset($mem_redirect->from) ) {
				 $from=$mem_redirect->from ;
  			 $to=$mem_redirect->to ;
				 $limit=$mem_redirect->limit ;
			}
			else {
				$from=$to=$limit="" ;
			}
        
      echo '<p><form method="POST" action="'.$page_url.'">' ;
			echo $this->layoutkit->formkit->setPostCallback('LinkController', 'LinkShowCallback') ;
			echo 'From: <input name="from" value="'.$from.'"/> To: <input name="to" value="'.$to.'"/> Limit: <input name="limit"  value="'.$limit.'"/>' ;
			echo '<input type="submit" value="send"/></form></p>';
		
		if ($mem_redirect) {
            // result from calculation
            echo '
			<p>
			Your Query: Show '.$mem_redirect->limit.' shortest connections between: '.$mem_redirect->from.' and: '.$mem_redirect->to.'
			</p>
           ';
		$model = new LinkModel();

		$linksIds = $mem_redirect->links;
		if ($linksIds) {
			foreach ($linksIds as $key => $value) {
				foreach ($value as $id) {
					echo $id." >";
				}
				echo "<br>";
			}
		}
		
		$linksData = $mem_redirect->linksFull;
		if ($linksData) {
				$SearchUsername=$mem_redirect->SearchUsername ;
				require 'templates/linkshowlinkpage_people.php';
			}
	    else echo "no link";
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