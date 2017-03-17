<?php
/**
 * Renderer for XHTML output
 *
 * @author Harry Fuecks <hfuecks@gmail.com>
 * @author Andreas Gohr <andi@splitbrain.org>
 */
if (!defined('DOKU_INC')) die('meh.');


require_once DOKU_INC . 'inc/parser/xhtml.php';

/**
 * DokuWiki Plugin nicorender (Renderer Component)
 *
 * The Nico XHTML Renderer
 * @license GPL 2 http://www.gnu.org/licenses/gpl-2.0.html
 * @author  Nicolas GERARD <gerardnico@gmail.com>
 *
 * This is a replacement render of the DokuWiki's main renderer
 * That format the content that's output the tpl_content function.
 */
class  renderer_plugin_rox_renderer extends Doku_Renderer_xhtml
{
}
