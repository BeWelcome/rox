<?php
/*

Copyright (c) 2007 BeVolunteer

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
 * Collection of methods to check ips against DNS Block Lists
 *
 * An example for its use:
 * $dnsblock = MOD_dnsblock::get();	// get the singleton instance
 * if ($dnsblock->checkIp($ipStr)) {
 *     // blocked
 * } else {
 *     // not blocked
 * }
 *
 * @author Jonas Hörsch <coroa@online.de>
 */
class MOD_dnsblock
{
    /**
     * Singleton instance
     *
     * @var MOD_dnsblock
     * @access private
     */
    private static $_instance;

    private function __construct()
    {
        if (! (
               $configDNSBlock = PVars::getObj('dnsblock') &&
               isset($configDNSBlock->dns)
               )
            ) {
            // fallback
            $this->_dns_list = array('sbl.spamhaus.org', 'sbl-xbl.spamhaus.org');
        } else {
            $this->_dns_list = array_map('trim', explode(',', $configDNSBlock->dns));
        }
    }

    /**
     * singleton getter
     *
     * @param void
     * @return PApps
     */
    public static function get()
    {
        if (!isset(self::$_instance)) {
            $c = __CLASS__;
            self::$_instance = new $c;
        }
        return self::$_instance;
    }

    /**
     * Check ip and cache the result in a session variable
     *
     * @access public
     * @param string $ip
     * @return int On how many DNS Blocklists does $ip figure
     */
    public function checkIp($ip) {
        if (isset($_SESSION['dnsblock_' . $ip])) {
            return intval($_SESSION['dnsblock_' . $ip]);
        } elseif ($ip == "127.0.0.1") {
            // never check localhost
            return 0;
        }

        $ip_exploded = explode('.', $ip);
        if (count($ip_exploded) != 4) {
            // this was no ipv4 - ip, abort for now
            return 0;
        } else {
            $ip_reverse = implode('.', array_reverse($ip_exploded));
            $no_blocked = 0;

            if (!isset($this->_dns_list) or empty($this->_dns_list)) {
                // why is not set log the issue
                MOD_log::get()->write("DNSBlock List is not set or empty. Not checking ...", "DNSBlock");
            } else {
                foreach ($this->_dns_list as $dns)
                    if (checkdnsrr($ip_reverse . '.' . $dns, "A"))
                        $no_blocked ++;

                MOD_log::get()->write("Ip $ip returns as blocked by $no_blocked servers", "DNSBlock");
            }

            $_SESSION['dnsblock_' . $ip] = $no_blocked;
            return $no_blocked;
        }
    }

    /**
     * Check remote ip
     *
     * @access public
     * @param void
     * @return int On how many DNS Blocklists does the remote ip figure
     */
    public function checkRemoteIp() {
        // return $this->checkIp('41.83.15.228'); // check with bad ip for testing

        return $this->checkIp($_SERVER['REMOTE_ADDR']);
    }
}
?>
