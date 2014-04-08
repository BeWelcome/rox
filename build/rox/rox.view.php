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
    public function commentguidelines()
    {
        require TEMPLATE_DIR.'apps/rox/commentguidelines.php';
    }
     public function impressum()
    {
        require TEMPLATE_DIR.'apps/rox/impressum.php';
    }

     public function donate($sub = false,$TDonationArray = false)
    {
//        if ($sub == cancel) {
//            require TEMPLATE_DIR.'apps/rox/donate_return.php';
//        } else {
        require TEMPLATE_DIR.'apps/rox/donate.php';
//        }
    }

     public function affiliations()
    {
		// check if member belongs to group Volunteers
		$isvolunteer = $this->_model->isVolunteer($_SESSION['IdMember']);
        require TEMPLATE_DIR.'apps/rox/affiliations.php';
    }
    public function privacy()
    {
        require TEMPLATE_DIR.'apps/rox/privacy.php';
    }

    public function globalhelppage()
    {
        require TEMPLATE_DIR.'apps/rox/help.php';
    }

	 public function stats()
    {
        $countryrank = $this->_model->getMembersPerCountry();
		$loginrank = $this->_model->getLastLoginRank();
		$loginrankgrouped = $this->_model->getLastLoginRankGrouped();
		$statsall = $this->_model->getStatsLogAll();
		$statslast = $this->_model->getStatsLog2Month();

		require TEMPLATE_DIR.'apps/rox/stats.php';
	}

    public function volunteerpage()
    {
		// check if member belongs to group Volunteers
		$isvolunteer = $this->_model->isVolunteer($_SESSION['IdMember']);
		define('MAGPIE_CACHE_ON',false);
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
        require 'templates/_languageselector.helper.php';
        require 'templates/startpage.php';
    }
    public function mainpage()
    {
    	$Forums = new ForumsController;
    	// waitin for a later commit
	    // PVars::getObj('page')->title = $_SESSION['Username'].' Home - BeWelcome';
		$citylatlong = $this->_model->getAllCityLatLong();
		$google_conf = PVars::getObj('config_google');
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

    public function donateBar()
    {
        require TEMPLATE_DIR.'apps/rox/userbar_donate.php';
    }

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

		// We will mark the fact the member has or has no picture here, this is based on the returned default picture et something
		if ((strpos($thumbPathMember,"et_male.square")!==false) or
			(strpos($thumbPathMember,"et.square")!==false) or
			(strpos($thumbPathMember,"et_female.square")!==false) ) {
			$_SESSION['MemberHasNoPicture']=1 ;
		}
		else {
			if (isset($_SESSION['MemberHasNoPicture'])) {
				unset ($_SESSION['MemberHasNoPicture']) ;
			}
		}

        $_newMessagesNumber = $this->_model->getNewMessagesNumber($_SESSION['IdMember']);

        if ($_newMessagesNumber > 0) {
            $_mainPageNewMessagesMessage = $words->getFormatted('MainPageNewMessages', $_newMessagesNumber);
        } else {
            $_mainPageNewMessagesMessage = $words->getFormatted('MainPageNoNewMessages');
        }
        require 'templates/teaser_main.php';
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
		 echo "<link rel=\"stylesheet\" href=\"styles/YAML/screen/custom/index.css?3\" type=\"text/css\"/>";
	}

    public function topMenu($currentTab)
    {
	require TEMPLATE_DIR . 'shared/roxpage/topmenu.php';
    }

}
/* removed functions referencing app user - pending deletion

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
            $redirect_url .= (empty($_SERVER['QUERY_STRING']) ? '' : '?' . $_SERVER['QUERY_STRING']);
        } else {
            $redirect_url = false;
        }
        $User = new UserController;
		$User->displayLoginForm($redirect_url);
	}

*/
