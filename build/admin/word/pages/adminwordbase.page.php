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
     * @author Tsjoek
     */

    /** 
     * words management base page
     * 
     * @package Apps
     * @subpackage Admin
     */

class AdminWordBasePage extends PageWithActiveSkin
{
    protected $purifier; // instance of html-purifier
    public $formdata = array(); // data collected from the form
    
    public function __construct($model = false) {
        parent::__construct();
        $this->purifier = MOD_htmlpure::getSuggestionsHtmlPurifier();
    }
    
    /*
     * default browsertab title
     *
     * @access protected
     * @return string
     */
    protected function getPageTitle() {
        return 'Words management | BeWelcome';
    }

    /*
     * create default teaser
     *
     * @access public
     * @return string
     */
    public function teaserHeadline(){
        $string = 'AdminWord';
        return $string;
    }

    protected function leftSidebar() {
        include '../build/admin/word/templates/adminword.leftsidebar.php';
    }
    
    protected function getStylesheets() 
    {
       $stylesheets = parent::getStylesheets();
       $stylesheets[] = 'styles/css/minimal/screen/custom/adminword.css';
       return $stylesheets;
    }

   /**
     * Make humanreadable string from array containing scopes
     *
     * @access private
     * @return String List of scopes to be displayed on screen
     **/
    protected function showScope(){
        if ($this->nav['scope']=='"All"'){
            return 'All';
        } else {
            $tot = '';
            foreach ($this->langarr as $item){
                if (strlen($tot)>0) {$tot.=', ';}
                $tot .= trim($this->words->get('lang_'.$item->ShortCode));
            }
            return $tot;
        }
    }
}
