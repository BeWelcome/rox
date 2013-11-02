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
     * words management overview page
     * 
     * @package Apps
     * @subpackage Admin
     */

class AdminWordEditPage extends AdminWordBasePage
{
    public $formdata = array();
    
    public function teaserHeadline()
    {
        $string = 'AdminWord';
        $string .= ' » '.$this->nav['currentLanguage'];
        $string .= ' » Translate';
        return $string;
    }
    
    public function getFormData($fields){
            //$formdata = array();
        foreach ($fields as $field) {
            if (isset($vars[$field])){
                $this->formdata[$field] = $vars[$field];
            } elseif (isset($_SESSION['form'][$field])){
                $this->formdata[$field] = $_SESSION['form'][$field];
            } elseif (isset($this->data->$field)) {
                $this->formdata[$field] = $this->data->$field;
            } else {
                $this->formdata[$field] = '';
            }
            unset ($_SESSION['form'][$field]);
        }
        if ($this->formdata['lang']==''){
            $this->formdata['lang'] = $this->nav['shortcode'];
        }
    }
    
    public function showScope(){
    if ($this->nav['scope']=='"All"'){
        return 'All';
    } else {
        array_map(function ($lng){echo $this->words->get('lang_'.$lng->ShortCode).' ';},$this->langarr);
    }
    }
}
