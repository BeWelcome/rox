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
     * generic functionality for creating emails
     *
     * @package    Tools
     * @subpackage Email
     * @author     Fake51
     */
class EmailTemplate extends RoxComponentBase
{
    /**
     * available email templates
     *
     * @var array
     */
    private static $_templates = array(
        'SignupAccepted'    => 'EmailAccepted',
    );

    /**
     * readiness status of template
     *
     * @var bool
     */
    private $_ready = false;

    /**
     * type of template to use
     *
     * @var string
     */
    private $_template_type;

    /**
     * routes calls to the concrete template, if it's been initialized
     * if not or if the result of that is bad, it routes the call
     * to the parent __call function
     *
     * @param string $method
     * @param array  $args
     *
     * @access public
     * @return mixed
     */
    public function __call($method, $args)
    {
        if ($this->_ready)
        {
            $result = call_user_func_array(array($this->_template_instance, $method), $args);
            if (!is_null($result))
            {
                return $result;
            }
        }
        return parent::__call($method, $args);
    }
    
    /**
     * instantiates the specific email template to use
     *
     * @param string $template - name to look up in $_templates
     *
     * @throws Exception
     * @access public
     * @return void
     */
    public function __construct($template)
    {
        if (!isset(self::$_templates[$template]))
        {
            throw new Exception("No such email template: {$template}");
        }
        $this->_template_type = $template;
        $this->_template_instance = new self::$_templates[$template];
    }

    /**
     * sets up the email template instance with the proper
     * args
     *
     * @param array $args
     *
     * @access public
     * @return bool
     */
    public function init(array $args)
    {
        $this->_ready = $this->_template_instance->init($args);
        return $this->_ready;
    }

    /**
     * sends the email off, using MOD_mail
     *
     * @access public
     * @return bool
     */
    public function send()
    {
        if (empty($this->_ready))
        {
            return false;
        }
        try
        {
            return (bool)MOD_mail::sendEmail($this->_template_instance->getSubject(), $this->_template_instance->getSender(), $this->_template_instance->getReceiver(), $this->_template_instance->getTitle(), $this->_template_instance->getEmailBody(), $this->_template_instance->getEmailBodyHtml(), $this->_template_instance->getEmailAttachments());
        }
        catch (Exception $e)
        {
            $this->logWrite("Failed to send email to {$this->_template_instance->getEmailAddress()}. Template type: {$this->_template_type}. Exception message: {$e->getMessage()}", 'bug');
        }
        return false;
    }
}
