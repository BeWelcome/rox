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
     * functionality that email templates must implement
     *
     * @package    Tools
     * @subpackage Email
     * @author     Fake51
     */
abstract class AbstractEmailTemplate extends RoxComponentBase
{
    protected $ready_state = false;

    /**
     * sets up vars needed to send email
     *
     * @param array $args - input needed
     *
     * @access public
     * @return bool
     */
    abstract public function init(array $args);

    /**
     * returns subject of mail to send
     *
     * @access public
     * @return string
     */
    abstract public function getSubject();

    /**
     * returns address of sender
     *
     * @access public
     * @return string
     */
    abstract public function getSender();

    /**
     * returns address of receiver
     *
     * @access public
     * @return string
     */
    abstract public function getReceiver();

    /**
     * returns title of email, if any
     *
     * @access public
     * @return string
     */
    public function getTitle()
    {
        return '';
    }

    /**
     * returns email body in plain text
     *
     * @access public
     * @return string
     */
    abstract public function getEmailBody();

    /**
     * returns email body in html, if supplied
     *
     * @access public
     * @return string
     */
    public function getEmailBodyHtml()
    {
        return '';
    }

    /**
     * returns attachments of email, if any
     *
     * @access public
     * @return array
     */
    public function getEmailAttachments()
    {
        return array();
    }
}
