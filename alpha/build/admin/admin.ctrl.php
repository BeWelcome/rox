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
 * admin controller
 * 
 * @package admin
 * @author Felix <fvanhove@gmx.de>
 * 
 * Channels all requests, which are available exclusively for administrators
 */
class AdminController extends PAppController
{

    private $_model;
    private $_view;
    
    public function __construct()
    {        
        parent::__construct();
        $this->_model = new Admin();
        $this->_view  = new AdminView($this->_model);
    }
    
    public function __destruct() 
    {
        unset($this->_model);
        unset($this->_view);
    }
    
    /**
     * @param void
     */
    public function index()
    {
        throw new PException("Not ready for use yet.");
        
        // FIXME: check, if requester is admin; shouldn't we?
        
        $request = PRequest::get()->request;
        if (!isset($request[1])) {
            $request[1] = '';
        }
        
        $R = MOD_right::get();
                
        switch ($request[1]) {
	        
            case 'activitylogs':
	            
                $level = $R->hasRight('Logs');
	            if (!$level || $level < 1) {
	                PPHP::PExit(); // TODO: redirect or display message?
	            }
	            
	            ob_start();
	            $this->_view->leftSidebar();
	            $this->_view->activitylogs($level);
	            $str = ob_get_contents();
	            ob_end_clean();
	            $Page = PVars::getObj('page');
	            $Page->content .= $str;
	            break;
	            
	        case 'test':
	            
	            throw new PException("No tests implemented yet.");
	            break;
	            
	        default:
	            
	            throw new PException("No default implemented yet.");
	            break;
	            
	    }
    }
}
?>
