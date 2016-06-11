<?
/**
 * blog item template controller
 *
 * defined vars:
 * $blog        - the blog object.
 *
 * @package blog
 * @subpackage template
 * @author The myTravelbook Team <http://www.sourceforge.net/projects/mytravelbook>
 * @copyright Copyright (c) 2005-2006, myTravelbook Team
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License (GPL)
 * @version $Id$
 */

$words = new MOD_words();
$format = array(
    'short'=>$words->getSilent('DateFormatShort')
);
if (!isset($headingLevel)) {
  $headingLevel = 3;
}

$shouts = new ShoutsController();

?>

    <p class="action">
<?php
if (!$shouts->getShouts($application,$id)) {
  if ($shouts->count == 1) {
    echo '<img src="images/icons/comment.png" alt="'.$words->get('CommentsSingular').'"/> 1 '.$words->get('CommentsSingular');
  } else {
    echo '<img src="images/icons/comments.png" alt="'.$words->get('CommentsPlural').'"/> '.(int)$shouts->comments.' '.$words->get('CommentsPlural');
  }
} else {
  echo '<img src="images/icons/comment_add.png" alt="'.$words->get('CommentsAdd').'"/> '.$words->get('CommentsAdd');
}
?>
    </p>