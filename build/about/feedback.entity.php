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
        if (!$this->isLoaded() || !$this->getCategoryData())
        {
            return '';
        }
        return $this->feedback_category['name'];
    }

    /**
     * returns feedback category data
     *
     * @access public
     * @return array
     */
    public function getCategoryData()
    {
        if (!$this->isLoaded())
        {
            return array();
        }
        if (!$this->feedback_category)
        {
            if (!($result = $this->dao->query(<<<SQL
SELECT name from feedbackcategories WHERE id = {$this->IdFeedbackCategory}
SQL
                )) || !($fetch = $result->fetch(PDB::FETCH_ASSOC)))
            {
                return array();
            }
            $this->feedback_category = $fetch;
        }
        return $this->feedback_category;
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
        return $this->createEntity('Feedback')->findByWhere("IdFeedbackCategory = " . FeedbackModel::ACCOUNT . " AND IdMember = {$member->id}");
    }

    /**
     * creates a new feedback entry
     *
     * @param StdClass $category
     * @param string   $feedback - feedback from user
     * @param Member   $member
     * @param string   $status
     *
     * @access public
     * @return bool
     */
    public function createNew(StdClass $category, $feedback, Member $member = null, $status = 'open')
    {
        if ($this->isLoaded())
        {
            return false;
        }
        $this->IdFeedbackCategory = $category->id;
        $this->Discussion = $feedback;
        $this->Status = $status;
        $this->IdVolunteer = $category->IdVolunteer;
        $this->IdMember = $member ? $member->id : 0;
        $this->IdLanguage = $this->session->get('IdLanguage');
        $this->created = date('Y-m-d H:i:s');
        return !!$this->insert();
    }
}
