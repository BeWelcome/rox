<?php
/**
 * Loading base
 *
 * @package core
 * @author The myTravelbook Team <http://www.sourceforge.net/projects/mytravelbook>
 * @copyright Copyright (c) 2005-2006, myTravelbook Team
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License (GPL)
 * @version $Id: base.inc.php 187 2006-12-11 15:53:23Z david $
 */
// load base.xml document
$B = DOMDocument::load(SCRIPT_BASE.'base.xml');
if (!$B) {
    die('base.xml error!');
}
$B->x = new DOMXPath($B);

// is platform PT?
$is = $B->x->query('/basedata/is');
if ($is->length != 1) {
    die('base is?');
}
if ($is->item(0)->nodeValue != 'respice platform PT') {
    die('no, it\'s not');
}

// version
$version = $B->x->query('/basedata/version');
if ($version->length != 1) {
    die('version?');
}
$version = $version->item(0)->nodeValue;

// lib dir
$libdir = SCRIPT_BASE.'lib'.$version;
if (!file_exists($libdir) || !is_dir($libdir))
    $libdir = SCRIPT_BASE.'lib';
if (!file_exists($libdir) || !is_dir($libdir))
    die('libdir...');
define('LIB_DIR', $libdir.'/');

// build dir
$buildDir = 'build';
$build = $B->x->query('/basedata/build');
if ($build->length == 1) {
    $buildDir = $build->item(0)->nodeValue;
}
if (!file_exists(SCRIPT_BASE.$buildDir)) {
    $buildDir = 'build';
}
if (!file_exists(SCRIPT_BASE.$buildDir))
    die('builddir error!');
define('BUILD_DIR', SCRIPT_BASE.$buildDir.'/');

// template dir (EEEE Embedded Easter Egg Engine)
$template = $B->x->query('/basedata/template');
$templateDir = SCRIPT_BASE.'templates';
if ($template->length == 1) {
    $templateDir = SCRIPT_BASE.'templates_'.$template->item(0)->nodeValue;
    if (!file_exists($templateDir) || !is_dir($templateDir) || !is_readable($templateDir)) {
        $templateDir = SCRIPT_BASE.'templates';
    }
}
if (!file_exists($templateDir) || !is_dir($templateDir) || !is_readable($templateDir)) {
    die('Template dir error!');
}
define('TEMPLATE_DIR', $templateDir.'/');

// text dir
$text = $B->x->query('/basedata/text');
$textDir = SCRIPT_BASE.'text';
if ($text->length == 1) {
	$textDir = $textDir.'_'.$text->item(0)->nodeValue;
    if (!file_exists($textDir) || !is_dir($textDir) || !is_readable($textDir))
        $textDir = SCRIPT_BASE.'text';
}
if (!file_exists($textDir) || !is_dir($textDir) || !is_readable($textDir))
    die('Text dir error!');
define('TEXT_DIR', $textDir.'/');

$datadir = SCRIPT_BASE.'data';
if (!file_exists($datadir) || !is_dir($datadir) || !is_writable($datadir)) {
    die('Data dir error!');
}
define('DATA_DIR', $datadir.'/');
?>