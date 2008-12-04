<?php


/**
 * Hello universe view, a first simple version.
 * We redefine the methods of RoxPageView to configure this page.
 * We don't need to redefine all the methods, we already get something for an empty subclass of RoxPageView.
 * For the start, we only redefine the content of the main column.
 *
 * @package hellouniverse
 * @author Andreas (lemon-head)
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License (GPL)
 * @version $Id$
 */
class LinkSimplePage extends RoxPageView
{
    /**
     * content of the middle column - this is the most important part
     */
    protected function column_col3()
    {
        echo '
<h3>The hello universe middle column</h3>
using the class HellouniverseSimplePage.<br>
More beautiful in <a href="hellouniverse/advanced">hellouniverse/advanced</a>!<br>
With tabs in <a href="hellouniverse/tab1">hellouniverse/tab1</a>!
        ';
    
	$model = new LinkModel();
							$listitem = array('fromID' => '10', 'toID' => '11', 'degree' => '1', 'rank' => '1', 'path' => '2');
						var_dump($listitem);
	//$model->createLinkList($listitem);
	$comments = $model->getLinks();
	var_dump($comments);

	foreach ($comments as $comment) {
	echo "From: ".$comment->IdFromMember." To: ".$comment->IdToMember."<br>";
		$link = array('fromID' => "$comment->IdFromMember", 'toID' => "$comment->IdToMember", 'degree' => '1', 'rank' => '1', 'path' => "'($comment->IdFromMember,$comment->IdToMember)'");
		var_dump($link);
		$model->createLinkList($link);
	}
	}

}
	       


?>