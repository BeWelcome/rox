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

class AdminMassMailEditCreatePage extends AdminMassMailBasePage
{
    protected $id;
    protected $name;
    protected $subject;
    protected $text;
    protected $description;
    
    public function __construct($model, $id = 0) {
        parent::__construct($model);
        // No idea how to access class constants in column_col3. Works here but 
        //   not in adminmassmaileditcreate.column_col3.php. While $this-> works fine there
        if ($id == 0) {
            $this->id = 0;

            // empty fields as we create a new mass mailing
            $this->name = $this->subject = $this->text = $this->description = "";
            if ($this->newsletterType & parent::NEWSLETTERGENERAL == parent::NEWSLETTERGENERAL) {
                $this->type = "Normal";
            } else {
                $this->type = "Specific";
            }
        } else {
            $this->id = $id;
            // get fields from database
            $entry = $model->getMassmail($id);
            $this->name = $entry->Name;
            $this->subject = $entry->Subject;
            $this->body = $entry->Body;
            $this->description = $entry->Description;
            $this->type = $entry->Type;
        }
    }

    public function teaserHeadline() {
        if ($this->id == 0) {
            $editcreate = 'create';
        } else {
            $editcreate = 'edit';
        }
        return '<a href="admin">' . $this->words->get('AdminTools') . "</a> "
            . ' &raquo; <a href="admin/massmail">' . $this->words->get('AdminMassMail') . "</a>"
            . ' &raquo; <a href="admin/massmail/' . $editcreate . '">' . $this->words->get('AdminMassMail'. $editcreate) . "</a>";
    } 
    
    public function leftSidebar() {
        $words = $this->getWords();
        echo '<h3>' . $words->get('AdminMassMailActions') . '</h3>';
        echo '<ul class="linklist">';
        echo '<li><a href="admin/massmail">' . $words->get('AdminMassMailList') . '</a></li>';
        echo '</ul>';
    }
}
