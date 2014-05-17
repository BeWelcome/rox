<?php
/*

Copyright (c) 2007-2009 BeVolunteer

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
     * @author Felix van Hove <fvanhove@gmx.de>
     * @author Fake51
     */

    /**
     * admin view
     *
     * @package Apps
     * @subpackage Admin
     */
class AdminView extends RoxAppView
{
    private $_model;
    
    public function __construct(AdminModel $model)
    {
        $this->_model = $model;
    }

    /**
     * 
     */
    private function _pager($totalNumber, $vars)
    {        
		$PageName='/admin/activitylogs';

        // Felix: skipped while porting to platform PT
		//$strurl="action=Find".ParamUrl() ;		
		// Felix: skipped while porting to platform PT
		//$strurl="OrderBy=".GetStrParam("OrderBy") ;

		$strurl = '';
		$output = "\n<center>" ;
		$countlink = 0;
		
		for ($ii=0; $ii<$totalNumber; $ii=$ii+$vars['limitcount']) {
		    
		    $i1=$ii;
		    $i2=min($ii+$vars['limitcount'],$totalNumber) ;
		    $countlink++;
		    
		    $output .= "<a href=\"" . $PageName . "?" . $strurl."&start_rec=" . $i1 . "\">";
		    
		    if ($countlink>20) {
		        $output .= "...</a>";
		        break ; // do not insert too much links
		    }

		    $output .= $i1+1 . ".." . $i2 . "</a>&nbsp;&nbsp;";
            
		}
		return $output . "</center>\n";    
    }
    
    /**
     * Displays the menu for the administrator user
     */
    public function leftSidebar()
    {
        require 'templates/leftsidebar.php';
    }

    /**
     *
     */
    public function activitylogs($level)
    {
        $callbackId = PFunctions::hex2base64(sha1(__METHOD__));

        if (PPostHandler::isHandling()) {
            $vars =& PPostHandler::getVars();
        } else {
            $vars = $this->_gainGetParams();
        }

        $result = $this->_model->procActivitylogs($vars, $level);
        $tData = current($result);
        $totalNumber = key($result);

        PPostHandler::setCallback($callbackId, __CLASS__, __FUNCTION__);

        require 'templates/activitylogs.php';
    }

    /**
     *
     */
    public function wordsdownload($callbackId)
    {
        require 'templates/wordsdownload.php';
    }

    public function wordsdownload_teaser()
    {
        require 'templates/wordsdownload_teaser.php';
    }

    private function _gainGetParams()
    {
        $vars = array(
                "username" => $this->_getParam("username", "0"),
                "type" => $this->_getParam("type"),
                "limitcount" => $this->_getParam("limitcount", "100"),
                "start_rec" => $this->_getParam("start_rec", "0"),
                "andS1" => $this->_getParam("andS1", ""),
                "andS2" => $this->_getParam("andS2", ""),
                "notAndS1" => $this->_getParam("notAndS1", ""),
                "notAndS2" => $this->_getParam("notAndS2", ""),
                "ip" => $this->_getParam("ip", ""),
                "action" => $this->_getParam("action")
        );
        return $vars;
    }
    
    /**
     * FIXME: more or less a copy from method GetStrParam($param, $defaultvalue)
     * clean it up!
     * 
     * FIXME: move to dedicated module or other place
     *
     * POST params are to be handled by platform PT library!
     *  
     * @see /htdocs/bw/lib/FunctionsTools.php 
     */
    private function _getParam($param, $defaultValue = '')
    {
	    if (isset ($_GET[$param])) {
		    $m=$_GET[$param];
		}
		
		if (!isset($m))
			return $defaultValue;
		
		$m=mysql_real_escape_string($m);
		$m=str_replace("\\n","\n",$m);
		$m=str_replace("\\r","\r",$m);
		if ((stripos($m," or ")!==false)or (stripos($m," | ")!==false)) {
		    $L = MOD_log::get();
				$L->write(
				    "Warning! GetStrParam trying to use a <b>" . addslashes($m) .
				        "</b> in a param $param for ".$_SERVER["PHP_SELF"],
				        "alarm");
		}
		if (empty($m) and ($m!="0")){	// a "0" string must return 0 for the House Number for exemple 
			return ($defaultValue); // Return defaultvalue if none
		} else {
			return ($m);		// Return translated value
		}
    }
    
    protected function getStylesheets() {
       $stylesheets = parent::getStylesheets();
       $stylesheets[] = 'styles/css/minimal/screen/custom/admin.css?1';
       return $stylesheets;
    }

    
}
?>
