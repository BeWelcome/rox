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
$words = new MOD_words($this->getSession());
$Blog = new Blog;
$callback = $this->getCallbackOutput('BlogController', 'categoryProcess');


// get the saved post vars
$vars = $this->getRedirectedMem('vars');
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
<h2><?=$words->get('BlogManageCategories')?></h2>
<?

if (!$member = $this->_model->getLoggedInMember()) {
    echo '<p class="error">'.$words->get('not_logged_in').'</p>';
    return false;
}

$catIt = $Blog->getCategoryFromUserIt($member->id);
if (in_array('upderror', $vars['errors'])) {
    echo '<span class="error">'.$words->get('upderror').'</span>';
}
if (in_array('inserror', $vars['errors'])) {
    echo '<span class="error">'.$words->get('inserror').'</span>';
}
if (in_array('delerror', $vars['errors'])) {
    echo '<span class="error">'.$words->get('delerror').'</span>';
}
?>
<ul>
<?
foreach ($catIt as $cat) {
    if (isset($request[2]) && $request[2] == 'edit' && isset($request[3]) && $cat->blog_category_id == $request[3]) {
        $vars['n'] = $cat->name;
    }
    echo '    <li>'.(isset($request[2]) && in_array($request[2], array('edit', 'del')) && isset($request[3]) && $cat->blog_category_id == $request[3]?
        '<b>'.htmlspecialchars($cat->name, ENT_QUOTES).'</b>':htmlspecialchars($cat->name, ENT_QUOTES)).
        ' <a href="blog/cat/edit/'.$cat->blog_category_id.'">'.$words->get('edit').'</a>'.
        ' | <a href="blog/cat/del/'.$cat->blog_category_id.'">'.$words->get('delete').'</a>'.
        "</li>\n";
}
?>
</ul>


<?php
if (isset($request[2]) && $request[2] == 'del') {
?>
<form method="post" action="<?=implode('/', $request)?>" class="def-form" id="blog-cat-form">
    <div class="bw-row">
        <p><?=$words->get('ask_delete')?></p>
        <input type="submit" class="button" name="yes" value="<?=$words->get('yes')?>" class="submit" />
        <input type="submit" class="button" name="no" value="<?=$words->get('no')?>" class="submit"/>
        <?= $callback;?>
    </div>
</form>
<?php
} else {
?>
<h3><?php
echo (isset($request[2]) && $request[2] == 'edit' ? $words->get('Category_title_edit') : $words->get('Category_title_create')); ?></h3>
<form method="post" action="<?=implode('/', $request)?>" class="def-form" id="blog-cat-form">
    <div class="bw-row">
    <label for="category-name"><?=$words->get('Category_label_name')?>:</label><br/>
        <input type="text" id="category-name" name="n" class="long" <?php 
echo isset($vars['n']) ? 'value="'.htmlentities($vars['n'], ENT_COMPAT, 'utf-8').'" ' : ''; 
?>/>
        <div id="bcomment-title" class="statbtn"></div>
<?php
if (in_array('nameinvalid', $vars['errors'])) {
    echo '<span class="error">'.$words->get('nameinvalid').'</span>';
}
if (in_array('namedupe', $vars['errors'])) {
    echo '<span class="error">'.$words->get('namedupe').'</span>';
}
if (in_array('nameempty', $vars['errors'])) {
    echo '<span class="error">'.$words->get('nameempty').'</span>';
}
?>
        <p class="desc"></p>
    </div>
    <p>
        <input type="submit" class="button" value="<?php
echo (isset($request[2]) && $request[2] == 'edit' ? $words->getBuffered('Category_submit_edit') : $words->getBuffered('Category_submit_add')); ?>" class="submit" />
    <?=$callback;?>
    </p>
</form>

</div>
<?
}
echo $words->flushBuffer();
?>
