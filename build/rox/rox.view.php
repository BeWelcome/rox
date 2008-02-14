<?php
/*

Copyright (c) 2007 BeVolunteer

This file is part of BW Rox.

BW Rox is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

BW Rox is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, see <http://www.gnu.org/licenses/> or 
write to the Free Software Foundation, Inc., 59 Temple Place - Suite 330, 
Boston, MA  02111-1307, USA.

*/
/**
 * rox view
 *
 * @package rox
 * @author Felix van Hove <fvanhove@gmx.de>
 */
class RoxView extends PAppView {
    private $_model;
            
    public function __construct(Rox $model)
    {
        $this->_model = $model;
    }

    public function passthroughCSS($req)
    {
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
    
    /**
     * Loading Simple Teaser - just needs defined title
     *
     * @param void
     */
    public function ShowSimpleTeaser($title)
    {
        require TEMPLATE_DIR.'apps/rox/teaser_simple.php';
    }

// Pages (Everything in 'Content')

    public function aboutpage()
    {
        require_once ("magpierss/rss_fetch.inc");    
        require TEMPLATE_DIR.'apps/rox/about.php';
    }
    public function bodpage()
    {
        require TEMPLATE_DIR.'apps/rox/bod.php';
    }    
    public function thepeoplepage()
    {
        require TEMPLATE_DIR.'apps/rox/thepeople.php';
    }    
    public function getactivepage()
    {
        require TEMPLATE_DIR.'apps/rox/getactive.php';
    }   
    public function terms()
    {
        require TEMPLATE_DIR.'apps/rox/terms.php';
    }
     public function impressum()
    {
        require TEMPLATE_DIR.'apps/rox/impressum.php';
    }   
    public function privacy()
    {
        require TEMPLATE_DIR.'apps/rox/privacy.php';
    }
    
    public function globalhelppage()
    {
        require TEMPLATE_DIR.'apps/rox/help.php';
    }
    
    public function volunteerpage()
    {
		// check if member belongs to group Volunteers
		$isvolunteer = $this->_model->isVolunteer($_SESSION['IdMember']);
    	require_once ("magpierss/rss_fetch.inc");
		require TEMPLATE_DIR.'apps/rox/volunteer.php';
    }
	
    public function volunteertoolspage($currentSubPage)
    {
        require TEMPLATE_DIR.'apps/rox/volunteertoolspage.php';
    }
	
	public function volunteersearchpage()
    {
        require TEMPLATE_DIR.'apps/rox/volunteersearchpage.php';
    }
	
    
    public function startpage()
    {
        $flagList = $this->buildFlagList();
        require TEMPLATE_DIR.'apps/rox/startpage.php';
    }
    public function mainpage()
    {
    	$Forums = new ForumsController;
    	// waitin for a later commit
	    // PVars::getObj('page')->title = $_SESSION['Username'].' Home - BeWelcome';
        require TEMPLATE_DIR.'apps/rox/mainpage.php';
    }	
    
// Action menus (Everything in 'newBar' or 'rContent')    

    public function userBar()
    {
        require TEMPLATE_DIR.'apps/rox/userbar.php';
    }		
    public function aboutBar($currentSubPage)
    {
        require TEMPLATE_DIR.'apps/rox/aboutbar.php';
    }
    
    public function volunteerBar(
                        $numberPersonsToBeAccepted,
                        $numberPersonsToBeChecked,
                        $numberMessagesToBeChecked,
                        $numberSpamToBeChecked
                    )
    {
        require TEMPLATE_DIR.'apps/rox/volunteerbar.php';
    }
    
    
    // waiting for a later commit
    /*
    public function volunteerMenu(
                        $numberPersonsToBeAccepted,
                        $numberPersonsToBeChecked,
                        $numberMessagesToBeChecked,
                        $numberSpamToBeChecked
                    )
    {
        require TEMPLATE_DIR.'apps/rox/volunteermenu.php';
    }
    */
    
    public function volunteerToolsBar()
    {
        require TEMPLATE_DIR.'apps/rox/volunteertoolsbar.php';
//		require TEMPLATE_DIR.'apps/rox/volunteertoolsloginbar.php';
		
    }    		

// Teasers (Everything in 'teaserBar')
    
    public function teaser()
    {
        require TEMPLATE_DIR.'apps/rox/teaser.php';
    }
    public function teasermain()
    {
        $words = new MOD_words();
        $thumbPathMember = MOD_layoutbits::smallUserPic_userId($_SESSION['IdMember']);
        //$imagePathMember = MOD_user::getImage();
        
        $_newMessagesNumber = $this->_model->getNewMessagesNumber($_SESSION['IdMember']);
        
        if ($_newMessagesNumber > 0) {
            $_mainPageNewMessagesMessage = $words->getFormatted('MainPageNewMessages', $_newMessagesNumber);
        } else {
            $_mainPageNewMessagesMessage = $words->getFormatted('MainPageNoNewMessages');
        }
        require TEMPLATE_DIR.'apps/rox/teaser_main.php';
    }
       
    public function teasergetanswers()
    {
        require TEMPLATE_DIR.'apps/rox/teaser_getanswers.php';
    }

    public function teaservolunteer()
    {
        require TEMPLATE_DIR.'apps/rox/teaser_volunteer.php';
    }

// Sub menus (Everything in 'subMenu')    
    
    public function submenuGetAnswers($subTab) {
        require TEMPLATE_DIR.'apps/rox/submenu_getanswers.php';        
    }      
	
    public function submenuVolunteer($subTab) {
        require TEMPLATE_DIR.'apps/rox/submenu_volunteer.php';        
    }      
	// This adds other custom styles to the page
	public function customStyles()
	{		
	// calls a 2column layout 
		 echo "<link rel=\"stylesheet\" href=\"styles/YAML/screen/custom/bw_basemod_2col.css\" type=\"text/css\"/>";
		 echo "<link rel=\"stylesheet\" href=\"styles/YAML/screen/custom/index.css\" type=\"text/css\"/>";
	}
    
    public function rightContentIn()
    {
	// Space for advertisement
    //    require TEMPLATE_DIR.'apps/rox/ads.php';
	}
    public function rightContentOut()
    {
        $request = PRequest::get()->request;
        if(!isset($request[0])) {
            $redirect_url = false;
        } else if ($request[0]=='login') {
            $redirect_url = implode('/', array_slice($request, 1));
        } else {
            $redirect_url = false;
        }
        $User = new UserController;
		$User->displayLoginForm($redirect_url);
	}
	
    public function topMenu($currentTab)
    {
        require TEMPLATE_DIR.'apps/rox/topmenu.php';
    }
    
    public function footer()
    {
        $flagList = $this->buildFlagList();
        require TEMPLATE_DIR.'apps/rox/footer.php';
    }
    
    private function buildFlagList()
    {
        
        $pair = $this->_model->getLangNames();
        $flaglist = '';
        $request_string = implode('/',PVars::__get('request'));
		foreach($pair as $abbr => $title) {
		    $png = $abbr.'.png';
		    if ($_SESSION['lang'] == $abbr) {		        
		        $flaglist .=
                    '<span><a href="rox/in/'.$abbr.'/'.$request_string.'"><img '.
                        'src="bw/images/flags/'.$png.'" '.
                        'alt="'.$title.'" '. 
                        'title="'.$title.'"'.
                    "></img></a></span>\n"
		        ;
		    }
		    else {
		        $flaglist .= "<a href=\"rox/in/".$abbr.'/'.$request_string.
		        "\"><img src=\"bw/images/flags/" . $png . 
		        "\" alt=\"" . $title . "\" title=\"" . $title . "\"></img></a>\n";
		    }
		}
		
		return $flaglist;
    }

}
?>