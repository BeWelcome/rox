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
     * @author Fake51
     */

    /** 
     * Translation management page
     * 
     * @package Apps
     * @subpackage Admin
     */

class AdminMassMailDetailsPage extends AdminMassMailBasePage
{
    protected $ROWSPERPAGE = 20;
    protected $id;
    protected $model;
    protected $massmail;
    protected $type;
    protected $pageno;
    protected $count;
    protected $details;
    
    public function __construct($model, $id, $detail = false, $pageno = false) {
        parent::__construct($model);
        $this->id = $id;
        $this->model = $model;
        $this->massmail = $model->getMassMail($id);
        $this->detail = $detail;
        if ($detail) {
            $this->count = $this->model->getMassmailRecipientsCount( $id, $detail); 
            $this->details = $this->model->getMassmailRecipientsInfo( $id, $detail, 
                ($pageno -1 ) * $this->ROWSPERPAGE, $this->ROWSPERPAGE);
        }
    }
    
    public function teaserHeadline() {
        return '<a href="admin">' . $this->words->get('AdminTools') . "</a> "
            . ' &raquo; <a href="admin/massmail">' . $this->words->get('AdminMassMail') . "</a>"
            . ' &raquo; ' . $this->words->get('AdminMassMailDetails');
    } 
    
    public function leftSidebar() {
        $words = $this->getWords();
        echo '<h3>' . $words->get('AdminMassMailActions') . '</h3>';
        echo '<ul class="linklist">';
        echo '<li><a href="admin/massmail">' . $words->get('AdminMassMailList') . '</a></li>';
        if ($this->detail) {
            echo '<li><a href="admin/massmail/details/' . $this->id . '">' . $words->get('AdminMassMailListDetails') . '</a></li>';
        }
        echo '<li><a href="admin/massmail/edit/' . $this->id . '">' . $words->get('AdminMassMailEdit') . '</a></li>';
        echo '<li><a href="admin/massmail/enqueue/' . $this->id . '">' . $words->get('AdminMassMailEnqueue') . '</a></li>';
        if ($this->canTrigger) {
            echo '<li><a href="admin/massmail/unqueue/' . $this->id . '">' . $words->get('AdminMassMailUnqueue') . '</a></li>';
            echo '<li><a href="admin/massmail/trigger/' . $this->id . '">' . $words->get('AdminMassMailTrigger') . '</a></li>';
        }
        echo '</ul>';
    }
}
