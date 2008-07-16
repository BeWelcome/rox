<?php


/**
 * Hello universe page.
 * This is a base class for other pages in the same application.
 * We redefine the methods of RoxPageView to configure this page.
 * Here we make some more decorations.
 *
 * @package hellouniverse
 * @author Andreas (lemon-head)
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License (GPL)
 * @version $Id$
 */
class LinkUpdatePage extends LinkPage  /* HelloUniversePage doesn't work! */
{
    /**
     * content of the middle column - this is the most important part
     */
    protected function column_col3()
    {
        // get the translation module
        $words = $this->getWords();
        
        echo '
<h3>The hello universe (advanced) middle column</h3>
<p>
Using the class "'.get_class($this).'".<br>
Simple version in <a href="hellouniverse">hellouniverse</a>.<br>
More beautiful in <a href="hellouniverse/advanced">hellouniverse/advanced</a>!<br>
With tabs in <a href="hellouniverse/tab1">hellouniverse/tab1</a>!
</p>
<br>
<p>
A translated word (wordcode "Groups"):
'.$words->getFormatted('Groups').'
</p>
        ';
    
	
		$model = new LinkModel();
		// $listitem = array('fromID' => '10', 'toID' => '11', 'degree' => '1', 'rank' => '1', 'path' => '2');
		// var_dump($listitem);
	// $model->createLinkList($listitem);

	$flush = $model->deleteLinkList();
	$model->getTree();
	
		

	
	
	
	
	}
    
    /**
     * configure the teaser (the content of the orange bar)
     */
    protected function teaserHeadline() {
        echo 'Update links';
    }
    
    /**
     * configure the page title (what appears in your browser's title bar)
     * @return string the page title
     */
    protected function getPageTitle() {
        return 'Link it!';
    }
    

}




?>