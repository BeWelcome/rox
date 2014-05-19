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
     * comments overview page
     * 
     * @package Apps
     * @subpackage Admin
     */

class AdminCommentsPage extends AdminBasePage
{
    // TODO: is ugly
    // TODO: this doesn't work in case of an exception while updating
    private $comment_action2comment_teaser = array(
        "delete" => "Comments",
        "update" => "Updated Comment",
        "markChecked" => "Checked Comment",
        "toggleHide" => "Hidden / Unhidden Comment",
        "showAll" => "All Comments",
        "showAbusive" => "Abusive Comments",
        "showNegative" => "Negative Comments"
    );
    
     /**
     * @var string
     */
    private $teaser; // default
    
    
    public function __construct($teaser)
    {
        parent::__construct();
        $this->teaser = $comment_action2comment_teaser[$teaser];
    }

    public function teaserHeadline()
    {
        return "<a href='admin'>{$this->words->get('AdminTools')}</a> &raquo; <a href='admin'>{$this->teaser}</a>";
    }
}
