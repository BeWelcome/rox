<?php
/**
 * Site Export Plugin
 * 
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     i-net software <tools@inetsoftware.de>
 * @author     Gerry Weissbach <gweissbach@inetsoftware.de>
 */

// must be run within Dokuwiki
if(!defined('DOKU_INC')) define('DOKU_INC',realpath(dirname(__FILE__).'/../../').'/');
if(!defined('DOKU_PLUGIN')) define('DOKU_PLUGIN',DOKU_INC.'lib/plugins/');
require_once(DOKU_PLUGIN.'action.php');

class action_plugin_nodetailsxhtml extends DokuWiki_Action_Plugin {

	/**
	 * for backward compatability
	 * @see inc/DokuWiki_Plugin#getInfo()
	 */
    function getInfo(){
        if ( method_exists(parent, 'getInfo')) {
            $info = parent::getInfo();
        }
        return is_array($info) ? $info : confToHash(dirname(__FILE__).'/../plugin.info.txt');
    }
	
    	/**
	* Register Plugin in DW
	**/
	function register(Doku_Event_Handler $controller) {
		$controller->register_hook('TPL_TOC_RENDER', 'BEFORE', $this, 'check_toc');
	}
	
	/**
	* Check for Template changes
	**/
	function check_toc( &$event ) {
		global $conf, $INFO;
	
		if ( empty($event->data) && $INFO['meta']['forceTOC'] ) {
		    $event->data = $INFO['meta']['description']['tableofcontents'];
		}
		
	}
}