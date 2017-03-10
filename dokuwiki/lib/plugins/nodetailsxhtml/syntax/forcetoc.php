<?php 
/**
 * iReflect Plugin
 * 
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     i-net software <tools@inetsoftware.de>
 * @author     Gerry Weissbach <gweissbach@inetsoftware.de>
 */

if(!defined('DOKU_INC')) define('DOKU_INC',realpath(dirname(__FILE__).'/../../').'/'); 
if(!defined('DOKU_PLUGIN')) define('DOKU_PLUGIN',DOKU_INC.'lib/plugins/'); 
require_once(DOKU_PLUGIN.'syntax.php'); 
  
/** 
 * All DokuWiki plugins to extend the parser/rendering mechanism 
 * need to inherit from this class 
 */ 
class syntax_plugin_nodetailsxhtml_forcetoc extends DokuWiki_Syntax_Plugin {

	var $acronyms = array();
	var $pattern = '';
 
	function getInfo() {
        if ( method_exists(parent, 'getInfo')) {
            $info = parent::getInfo();
        }
	    return array_merge(is_array($info) ? $info : confToHash(dirname(__FILE__).'/../plugin.info.txt'), array(
	    				'desc' => 'Force the TOC to be inserted',
		));
	} 
  
    function getType(){ return 'substition';}

    function getSort(){ return 230; }

    function connectTo($mode) {
        $this->Lexer->addSpecialPattern('~~forceTOC~~', $mode, 'plugin_nodetailsxhtml_forcetoc');
    }
    
    function handle($match, $state, $pos, &$handler) {
        return $match; 
    }    
    
    function render($mode, &$renderer, $data) {
        global $ID, $INFO;
        
        $renderer->info['forceTOC'] = true;
        $renderer->info['toc'] = true;
        $renderer->meta['internal']['forceTOC'] = true;
        return true;
    }
}

//Setup VIM: ex: et ts=4 enc=utf-8 :