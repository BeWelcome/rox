<?php
/**
 * tags
 *
 * defined vars:
 * $tag  - the tag the user has been searching for.
 *
 * @package blog
 * @subpackage template
 * @author The myTravelbook Team <http://www.sourceforge.net/projects/mytravelbook>
 * @copyright Copyright (c) 2005-2006, myTravelbook Team
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License (GPL)
 * @version $Id$
 */

$Blog = new Blog();
$BlogView = new BlogView($Blog);
$errors = array();

$tagsText = array();
$i18n = new MOD_i18n('apps/blog/tags.php');
$tagsText = $i18n->getText('tagsText');
$words = new MOD_words($this->getSession());
?>
<div id="blog-tags">
    <h2><?=$words->get('TagsTitle')?></h2>
    <?
if (!$tag) {
    // display only overview of all tags.
    $minstyle = 0;
    $maxstyle = 5;
    $prevcount = -999;
    $maxcount = false;
    $curstyle = $minstyle;
    $tagsIt = $Blog->getTagsIt($tag);
    $tags = array();
    foreach ($tagsIt as $t) {
        if (!$maxcount) $maxcount = $t->usecount;
        $tags[$t->name] = $t;
    }
    ksort($tags);
    foreach ($tags as $t) {
        if ($prevcount != $t->usecount) {
            $curstyle = $minstyle + $t->usecount * ($maxstyle-$minstyle) / $maxcount;
            $prevcount = $t->usecount;
        }
        echo '<a class="tagusage'.$curstyle.'" href="blog/tags/'.rawurlencode($t->name).'">'.htmlentities($t->name, ENT_COMPAT, 'utf-8').'</a>['.$t->usecount.'] ';
    }
} else {?>
    <h3><?=$words->get('posts_tagged_with')?>: <em><?=htmlentities($tag, ENT_COMPAT, 'utf-8')?></em></h3>
<?
    $request = PRequest::get()->request;
    $requestStr = implode('/', $request);
    $matches = array();
    if (preg_match('%/page(\d+)%', $requestStr, $matches)) {
        $page = $matches[1];
    } else {
        $page = 1;
    }
    $requestStr = preg_replace('%[/]page\d+%', '', $requestStr);
    
    // display matching tags and matching posts.
    $postIt      = $Blog->getTaggedPostsIt($tag, true);
    $pages       = PFunctions::paginate($postIt, $page);
    $postIt      = $pages[0];
    $maxPage     = $pages[2];
    $pages       = $pages[1];
    $currentPage = $page;
    foreach ($postIt as $blog) {
        require 'blogitem.php';
    }
    $BlogView->pages($pages, $currentPage, $maxPage, $requestStr.'/page%d');
}
    ?>
</div>
