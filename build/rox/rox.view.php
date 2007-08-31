<?php

/**
 * rox view
 *
 * @package rox
 * @author Felix van Hove <fvanhove@gmx.de>
 * @license http://opensource.org/licenses/gpl-license.php GNU General Public License Version 2
 */
class RoxView extends PAppView {
    private $_model;
            
    public function __construct(Rox $model) {
        $this->_model = $model;
    }

    public function passthroughCSS($req) {
        $loc = PApps::getBuildDir().'rox/'.$req;
        if (!file_exists($loc))
            exit();
        $headers = apache_request_headers();
        // Checking if the client is validating his cache and if it is current.
        if (isset($headers['If-Modified-Since']) && (strtotime($headers['If-Modified-Since']) == filemtime($loc))) {
            // Client's cache IS current, so we just respond '304 Not Modified'.
            header('Last-Modified: '.gmdate('D, d M Y H:i:s', filemtime($loc)).' GMT', true, 304);
        } else {
            // File not cached or cache outdated, we respond '200 OK' and output the image.
            header('Last-Modified: '.gmdate('D, d M Y H:i:s', filemtime($loc)).' GMT', true, 200);
            header('Content-Length: '.filesize($loc));
        }
        header('Content-type: text/css');
        @copy($loc, 'php://output');
        exit();
    }

    public function searchmembers($callbackId, $TGroup, $TabTypicOffer, $MapOff)
    {
        include TEMPLATE_DIR.'apps/rox/searchmembers.php';
    }
    public function searchmembers_ajax($TList, $vars)
    {
        include TEMPLATE_DIR.'apps/rox/searchmembers_ajax.php';
    }
    public function aboutpage()
    {
        require TEMPLATE_DIR.'apps/rox/about.php';
    }
    public function globalhelppage()
    {
        require TEMPLATE_DIR.'apps/rox/help.php';
    }
    public function startpage() {
        require TEMPLATE_DIR.'apps/rox/startpage.php';
    }
	
	
    public function teaser() {
        require TEMPLATE_DIR.'apps/rox/teaser.php';
    }
	/* This adds other custom styles to the page*/
	public function customStyles() {
		$out = '';
		/* 2column layout */
		$out .= '<link rel="stylesheet" href="styles/YAML/screen/custom/bw_basemod_2col.css" type="text/css"/>';
		$out .= '<link rel="stylesheet" href="styles/YAML/screen/custom/index.css" type="text/css"/>';
		return $out;
    }
    public function rightContent() {
	$User = new UserController;
		$User->displayLoginForm();
	}
    public function topMenu($currentTab) {
        require TEMPLATE_DIR.'apps/rox/topmenu.php';
    }
    
    public function footer() {
        $flagList = $this->buildFlagList();
        require TEMPLATE_DIR.'apps/rox/footer.php';
    }
    
    private function buildFlagList() {
        
  $pair = $this->_model->getLangNames();
		$flaglist = '';
		foreach($pair as $abbr => $title) {
		    $png = $abbr.'.png';
		    if ($_SESSION['lang'] == $abbr) {		        
		        $flaglist .= "<span><a href=\"/rox/in/" . $abbr .
		        "\"><img src=\"/bw/images/flags/" . $png . "\" alt=\"" . $title . 
		        "\" title=\"" . $title . "\"></img></a></span>\n";
		    }
		    else {
		        $flaglist .= "<a href=\"/rox/in/" . $abbr . 
		        "\"><img src=\"/bw/images/flags/" . $png . 
		        "\" alt=\"" . $title . "\" title=\"" . $title . "\"></img></a>\n";
		    }
		}
		
		return $flaglist;
    }

}
?>
