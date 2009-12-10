<?php
/*
Copyright (c) 2009 BeVolunteer

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
     * represents a single feedback
     *
     * @author     Fake51
     * @package    Apps
     * @subpackage Entities
     */
class Feedback extends RoxEntityBase
{

    protected $_table_name = 'feedbacks';

    public function __construct($id = null)
    {
        parent::__construct();
        if ($id)
        {
            $this->findById(intval($id));
        }
    }

    /**
     * returns member entity that submitted the feedback
     *
     * @access public
     * @return member|false
     */
    public function getFromMember()
    {
        if (!$this->isLoaded())
        {
            return false;
        }
        return $this->createEntity('Member')->findById($this->IdMember);
    }

    /**
     * returns name of feedback category
     *
     * @access public
     * @return string
     */
    public function getCategory()
    {
        if (!$this->isLoaded())
        {
            return '';
        }
        if (!($result = $this->dao->query(<<<SQL
SELECT name from feedbackcategories WHERE id = {$this->IdFeedbackCategory}
SQL
)) || !($fetch = $result->fetch(PDB::FETCH_ASSOC)))
        {
            return '';
        }
        return $fetch['name'];
    }

    /**
     * returns signup feedback for a member
     *
     * @param Member $member - member to check for
     *
     * @access public
     * @return Feedback
     */
    public function getSignupFeedback(Member $member)
    {
        if (!$member->isLoaded())
        {
            return false;
        }
        return $this->createEntity('Feedback')->findByWhere("IdFeedbackCategory = " . FeedbackModel::FEEDBACK_AT_SIGNUP . " AND IdMember = {$member->id}");
    }
}
