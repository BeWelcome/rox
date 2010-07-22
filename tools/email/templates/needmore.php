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
     * @package    Tools
     * @subpackage Email
     * @author     Fake51
     * @copyright  2009 BeVolunteer
     * @license    http://www.gnu.org/licenses/gpl-2.0.html GPL 2
     * @link       http://www.bewelcome.org
     */

    /**
     * email sent to new members that need to provide more details
     *
     * @package    Tools
     * @subpackage Email
     * @author     Fake51
     */
class EmailStatusNeedmore extends AbstractEmailTemplate
{

    /**
     * member entity
     *
     * @var Member
     */
    private $_member;

    public function init(array $args)
    {
        if (empty($args['member']) || !$args['member']->isLoaded())
        {
            return false;
        }
        $this->_member = $args['member'];
        $this->ready_state = true;
        return true;
    }

    public function getSubject()
    {
        if (!$this->ready_state) return '';
        return $this->getWords()->get("SignupNeedmoreTitle", $this->_member->Username);
    }

    public function getSender()
    {
        if (!$this->ready_state) return '';
        return PVars::getObj('syshcvol')->AccepterSenderMail;
    }

    public function getReceiver()
    {
        if (!$this->ready_state) return '';
        return MOD_crypt::AdminReadCrypted($this->_member->Email);
    }

    public function getEmailBody()
    {
        if (!$this->ready_state) return '';
        return $this->getWords()->get("SignupNeedMoreText", $this->_member->Username, PVars::getObj('env')->baseuri);
    }
}
