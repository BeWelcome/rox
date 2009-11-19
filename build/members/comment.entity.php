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
     * represents a single comment
     *
     * @author Fake51
     * @package    Apps
     * @subpackage Entities
     */
class Comment extends RoxEntityBase
{

    protected $_table_name = 'comments';

    public function __construct($id = null)
    {
        parent::__construct();
        if ($id)
        {
            $this->findById(intval($id));
        }
    }

    /**
     * returns member entity for the commenting member
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
        return $this->createEntity('Member')->findById($this->IdFromMember);
    }

    /**
     * returns member entity for the commented upon member
     *
     * @access public
     * @return member|false
     */
    public function getToMember()
    {
        if (!$this->isLoaded())
        {
            return false;
        }
        return $this->createEntity('Member')->findById($this->IdToMember);
    }
}
