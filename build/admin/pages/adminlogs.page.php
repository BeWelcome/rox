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
     * page telling people they can't access the admin app as they have no rights
     * 
     * @package Apps
     * @subpackage Admin
     */

class AdminLogsPage extends AdminBasePage
{
    /**
     * determines the output displayed on the page
     * errors       = php error log
     * exceptions   = exception log
     * mysql        = mysql log
     * apache       = apache error log
     *
     * @var string
     */
    private $type;

    /**
     * name of log file to read from
     *
     * @var string
     */
    protected $logfile;

    /**
     * base name of log file to read from
     *
     * @var string
     */
    protected $logname;

    /**
     * this page needs a type, determining what it'll display
     *
     * @param string $type - see above for possible values
     * @access public
     * @throws Exception
     */
    public function __construct($type)
    {
        parent::__construct();
        $this->type = $type;

        switch (strtolower($type))
        {
            case 'php':
                $this->logfile = '../../logs/php_errors.log';
                break;
            case 'exception':
                $this->logfile = '../../logs/exception.log';
                break;
            case 'mysql':
                $this->logfile = '../../logs/mysql/mysql-slow.log';
                break;
            case 'apache':
                $this->logfile = '../../logs/www.bewelcome.org-error.log';
                break;
            default:
                throw new Exception('Bad type specified for log in AdminLogsPage');
        }
        $this->logname = basename($this->logfile);
    }

    /**
     * attempts to open a log file file
     *
     * @param int $lines - number of lines to read from tail
     * @access private
     * @return array
     * @throws Exception
     */
    protected function tailLogFile($lines = 100)
    {
        if (empty($lines))
        {
            throw new Exception("Could not open file: {$this->logfile}");
        }
        $filename = escapeshellarg($this->logfile);
        $lines = intval($lines);
        exec("tail -n {$lines} '{$filename}' 2>&1", $return);
        return $return;
    }
}

