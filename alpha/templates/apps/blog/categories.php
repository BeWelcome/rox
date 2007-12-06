<?php
/**
 * categories
 *
 * @package blog
 * @subpackage template
 * @author The myTravelbook Team <http://www.sourceforge.net/projects/mytravelbook>
 * @copyright Copyright (c) 2005-2006, myTravelbook Team
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License (GPL)
 * @version $Id$
 */

$Blog = new Blog;
$callbackId = $Blog->categoryProcess();
// get the saved post vars
$vars =& PPostHandler::getVars($callbackId);
// get current request
$request = PRequest::get()->request;

$catText = array();
$catError = array();
$i18n = new MOD_i18n('apps/blog/categories.php');
$catText = $i18n->getText('catText');
$catError = $i18n->getText('catError');

if (!isset($vars['errors'])) {
    $vars['errors'] = array();
}
?>
<div id="blog-category">
<h2><?=$catText['page_title']?></h2>
<?

if (!$User = APP_User::login()) {
    echo '<p class="error">'.$catError['not_logged_in'].'</p>';
    return false;
}

$catIt = $Blog->getCategoryFromUserIt($User->getId());
if (in_array('upderror', $vars['errors'])) {
    echo '<span class="error">'.$catError['upderror'].'</span>';
}
if (in_array('inserror', $vars['errors'])) {
    echo '<span class="error">'.$catError['inserror'].'</span>';
}
if (in_array('delerror', $vars['errors'])) {
    echo '<span class="error">'.$catError['delerror'].'</span>';
}
?>
<ul>
<?
foreach ($catIt as $cat) {
    if (isset($request[2]) && $request[2] == 'edit' && isset($request[3]) && $cat->blog_category_id == $request[3]) {
        $vars['n'] = $cat->name;
    }
    echo '    <li>'.(isset($request[2]) && in_array($request[2], array('edit', 'del')) && isset($request[3]) && $cat->blog_category_id == $request[3]?
        '<b>'.$cat->name.'</b>':$cat->name).
        ' <a href="blog/cat/edit/'.$cat->blog_category_id.'">'.$catText['edit'].'</a>'.
        ' | <a href="blog/cat/del/'.$cat->blog_category_id.'">'.$catText['delete'].'</a>'.
        "</li>\n";
}
?>
</ul>


<?php
if (isset($request[2]) && $request[2] == 'del') {
?>
<form method="post" action="<?=implode('/', $request)?>" class="def-form" id="blog-cat-form">
    <div class="row">
        <p><?=$catText['ask_delete']?></p>
        <input type="submit" name="yes" value="<?=$catText['yes']?>" class="submit" />
        <input type="button" name="no" value="<?=$catText['no']?>" onclick="javascript:window.location.href='blog/cat'" />
    </div>
</form>
<?php
} else {
?>
<h3><?=$catText['title_create']?></h3>
<form method="post" action="" class="def-form" id="blog-cat-form">
    <div class="row">
    <label for="category-name"><?=$catText['label_name']?>:</label><br/>
        <input type="text" id="category-name" name="n" class="long" <?php 
echo isset($vars['n']) ? 'value="'.htmlentities($vars['n'], ENT_COMPAT, 'utf-8').'" ' : ''; 
?>/>
        <div id="bcomment-title" class="statbtn"></div>
<?php
if (in_array('nameinvalid', $vars['errors'])) {
    echo '<span class="error">'.$catError['nameinvalid'].'</span>';
}
if (in_array('namedupe', $vars['errors'])) {
    echo '<span class="error">'.$catError['namedupe'].'</span>';
}
if (in_array('nameempty', $vars['errors'])) {
    echo '<span class="error">'.$catError['nameempty'].'</span>';
}
?>
        <p class="desc"></p>
    </div>
    <p>
        <input type="submit" value="<?php
echo (isset($request[2]) && $request[2] == 'edit' ? $catText['submit_edit'] : $catText['submit_add']); ?>" class="submit" />
        <input type="hidden" name="<?php
// IMPORTANT: callback ID for post data 
echo $callbackId; ?>" value="1"/>
    </p>
</form>

</div>
<?
}
PPostHandler::clearVars($callbackId);
?>
