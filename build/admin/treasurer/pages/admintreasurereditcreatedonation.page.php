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
     * @author shevek, crumbking
     */

    /** 
     * Treasurer management page
     * 
     * @package Apps
     * @subpackage Admin/Treasurer
     */
class AdminTreasurerEditCreateDonationPage extends AdminTreasurerBasePage
{
    public function __construct(AdminTreasurerModel $model, $id) {
        parent::__construct($model);
        $this->model = $model;
        $this->member = $model->getLoggedInMember();

        $this->id = $id;
        if ($id == 0) {
            $this->username = "-empty-";
            $this->amount = "";
            $this->date = "";
            $this->comment = "Bank transfer";
            $this->countrycode = "";
        } else {
            $donation = $this->model->getDonation($id);
            $m = new Member($donation->IdMember);
            $this->username = $m->Username;
            $this->amount = $donation->Amount;
            $this->date = date('d.m.Y', strtotime($donation->created));
            $this->comment = $donation->SystemComment;
            $this->countrycode = $this->model->getCountryCodeForGeonameId($donation->IdCountry);
        }
        $this->addLateLoadScriptFile('/build/treasurer.js');
    }
    
    public function teaserHeadline()
    {
        $str = "<a href='admin'>{$this->words->get('AdminTools')}</a> &raquo; <a href='admin/treasurer'>{$this->words->get('AdminTreasurer')}</a> &raquo; ";
        if ($this->id) {
            $str .= $this->words->get('AdminTreasurerEditDonation');
        } else {
            $str .= $this->words->get('AdminTreasurerAddDonation');
        }
        return $str;
    }

    protected function getSubmenuActiveItem()
    {
        return 'add';
    }
}
