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
     * @author crumbking
     */

    /** 
     * words management overview page
     * 
     * @package Apps
     * @subpackage Admin
     */

class AdminMassMailEnqueuePage extends AdminMassMailBasePage
{
    protected $id;
    
    public function __construct($model, $id) {
        parent::__construct($model);
        $this->id = $id;
    }
    
    public function teaserHeadline() {
        return '<a href="admin">' . $this->words->get('AdminTools') . "</a> "
            . ' &raquo; <a href="admin/massmail">' . $this->words->get('AdminMassMail') . "</a>"
            . ' &raquo; <a href="admin/massmail/enqueue/' . $this->id . '">' . $this->words->get('AdminMassMailEnqueue') . "</a>";
    } 
    
    public function leftSidebar() {
        $words = $this->getWords();
        echo '<h3>' . $words->get('AdminMassMailActions') . '</h3>';
        echo '<ul class="linklist">';
        echo '<li><a href="admin/massmail">' . $words->get('AdminMassMailList') . '</a></li>';
        echo '</ul>';
    }
}
