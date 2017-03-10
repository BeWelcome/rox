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
class syntax_plugin_nodetailsxhtml_acronym extends DokuWiki_Syntax_Plugin {

	var $acronyms = array();
	var $pattern = '';
 
	function getInfo() {
        if ( method_exists(parent, 'getInfo')) {
            $info = parent::getInfo();
        }
	    return array_merge(is_array($info) ? $info : confToHash(dirname(__FILE__).'/../plugin.info.txt'), array(
				'desc' => 'Acronym Extension to enable acronyms with whitespaces (represented as "_")',
		));
	} 
  
    function getType(){ return 'substition';}

    function getSort(){ return 230; }
    
    function syntax_plugin_nodetailsxhtml_acronym() {
    	global $conf;
    	
    	if ( $conf['renderer_xhtml'] != 'nodetailsxhtml' ) { return; }
    	$this->acronyms = getAcronyms();
    }

    function preConnect() {
        
        $acronyms = array();
        foreach( $this->acronyms as $key => $value ) {
        		if ( !strstr($key, '_') ) { unset($this->acronyms[$key]); continue; }
				$acronyms[] = str_replace('_', ' ', $key);
        }
        
        if(!count($acronyms)) return;

        $bound = '[\x00-\x2f\x3a-\x40\x5b-\x60\x7b-\x7f]';
        $acronyms = array_map('Doku_Lexer_Escape',$acronyms);
        $this->pattern = '(?<=^|'.$bound.')(?:'.join('|',$acronyms).')(?='.$bound.')';
    }

    	
	function connectTo($mode){
        if(!count($this->acronyms)) return;

        if ( strlen($this->pattern) > 0 ) {
			$this->Lexer->addSpecialPattern($this->pattern,$mode,'acronym');
        }
	} 
}

//Setup VIM: ex: et ts=4 enc=utf-8 :