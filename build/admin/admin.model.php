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
     * @author Felix van Hove <fvanhove@gmx.de>
     * @author Fake51
     */

    /**
     * admin model
     *
     * @package Apps
     * @subpackage Admin
     */
class AdminModel extends RoxModelBase
{
    
//{{{ accepter stuff
    /**
     * gets an array of members with a given status
     *
     * @param stsring $status
     * @access public
     * @return array
     */
    public function getMembersWithStatus($status, $pager)
    {
        $offset = ($pager->active_page - 1) * $pager->items_per_page;
        return $this->createEntity('Member')->findByWhereMany("Status = '{$this->dao->escape($status)}' ORDER BY id LIMIT {$offset}, {$pager->items_per_page}");
    }

    /**
     * counts members with a given status
     *
     * @param stsring $status
     * @access public
     * @return int
     */
    public function countMembersWithStatus($status)
    {
        return $this->createEntity('Member')->countWhere("Status = '{$this->dao->escape($status)}'");
    }

    public function getStatusOverview()
    {
        $result = array();
        $query = "SELECT status, count(id) AS count FROM members GROUP BY status ORDER BY status";
        if ($results = $this->dao->query($query))
        {
            while ($row = $results->fetch(PDB::FETCH_OBJ))
            {
                $result[$row->status] = $row->count;
            }
        }
        return $result;
    }

    /**
     * updates member statuses according to a post array
     *
     * @param array $post
     *
     * @access public
     * @return bool
     */
    public function processMembers(array $post)
    {
        if (empty($post) || empty($post['accept_action']))
        {
            return false;
        }
        $errors = array();
        foreach ($post['accept_action'] as $id => $action)
        {
            if (!($member = $this->createEntity('Member')->findById($id)))
            {
                continue;
            }
            switch (strtolower($action))
            {
                case 'accept':
                    $member->Status = 'Active';
                    if (!$this->sendAcceptedEmail($member) || !$member->update())
                    {
                        $errors[] = array('id' => $member->id, 'action' => 'accept');
                        $this->logWrite("Accepting of {$member->Username} - {$member->id} failed. Accepter: {$this->getLoggedInMember()->Username}", 'bug');
                    }
                    else
                    {
                        $this->logWrite("{$member->Username} - {$member->id} was accepted. Accepter: {$this->getLoggedInMember()->Username}", 'bug');
                    }
                    break;
                case 'reject':
                case 'needmore':
                case 'duplicated':
            }
        }
    }

    /**
     * sends out an email containing a welcoming message
     * to a newly accepted member
     *
     * @param Member $member - member to welcome
     *
     * @todo move this into a dedicated mail class - make this a subclass of a dedicated mail class, in fact
     * @access public
     * @return bool
     */
    public function sendAcceptedEmail(Member $member)
    {
        $site    = PVars::getObj('env')->baseuri;
        $words   = new MOD_words;
        $email   = MOD_crypt::AdminReadCrypted($member->Email);
        $subject = $words->get("SignupSubjAccepted", $member->Username);
        $text    = $words->get("SignupYouHaveBeenAccepted", $member->Username, $site, $site . "login");
        try
        {
            if (MOD_mail::sendEmail($subject, PVars::getObj('syshcvol')->AccepterSenderMail, $email, '', $text))
            {
                return true;
            }
        }
        catch (Exception $e)
        {
        }
        $this->logWrite("Sending welcoming email to {$member->Username} failed with message: {$e->getMessage()}", "bug");
        return false;
    }
//}}}

//{{{ volunteer board
    /**
     * returns the text glob object for accepters
     *
     * @access public
     * @return VolunteerBoard
     */
    public function getAccepterBoard()
    {
        return $this->getBoard('Accepters_board');
    }

    /**
     * returns a board given it's name
     *
     * @param string $name
     *
     * @access public
     * @return VolunteerBoard
     */
    public function getBoard($name)
    {
        return $this->createEntity('VolunteerBoard')->findByName($name);
    }

    /**
     * updates a given volunteer board
     *
     * @param string $boardname - name of board to update
     * @param string $text      - text to update with
     *
     * @access public
     * @return bool
     */
    public function updateVolunteerBoard($boardname, $text)
    {
        if (!($board = $this->createEntity('VolunteerBoard')->findByName($boardname)) || !($member = $this->getLoggedInMember()))
        {
            return false;
        }
        return $board->updateText($text, $member);
    }

//}}}

//{{{ comments
    /**
     * returns all comments marked bad
     *
     * @access public
     * @return array
     */
    public function getBadComments()
    {
        return $this->createEntity('Comment')->findByWhereMany("AdminAction NOT IN ('NothingNeeded', 'Checked')");
    }
//}}}

    public function procActivitylogs($vars, $level = 0)
    {
        
		$where = '';
		$username = $vars["username"];

		$cid = $this->_idMember($username);
		if ($level <= 1) {
			$cid = $_SESSION["IdMember"]; // Member with level 1 can only see his own rights
		}
		if ($cid != 0) {
			$where .= " AND IdMember=" . $cid;
		}
		
		$R = MOD_right::get();
		$level = $R->hasRight('Logs');
		
		
		$limitcount=$vars["limitcount"]; // Number of records per page
		$start_rec=$vars["start_rec"]; // Number of records per page
		
		
		$andS1 = $vars["andS1"];
		if ($andS1 != "") {
			$where .= " AND Str LIKE '%" . $andS1 . "%'";
		}
		
		$andS2 = $vars["andS2"];
		if ($andS2 != "") {
			$where .= " AND Str LIKE '%" . $andS2 . "%'";
		}
		
		$notAndS1 = $vars["notAndS1"];
		if ($notAndS1 != "") {
			$where .= " AND Str NOT LIKE '%" . $notAndS1 . "%'";
		}
		
		$notAndS2 = $vars["notAndS2"];
		if ($notAndS2 != "") {
			$where .= " AND Str NOT LIKE '%" . $notAndS2 . "%'";
		}
		
		$ip = $vars["ip"];
		if ($ip != "") {
			$where .= " AND IpAddress=" . ip2long($ip) . "";
		}
		
		$type = $vars["type"];
		if ($type != "") {
			$where .= " AND Type='" . $type . "'";
		}
		
		// If there is a Scope limit logs to the type in this Scope (unless it his own logs)
		if (!$R->hasRight('Logs', "\"All\"")) {
			$scope = RightScope("Logs");
			str_replace($scope, "\"", "'");
			$where .= " AND (Type IN (" . $scope . ") OR IdMember=" . $_SESSION["IdMember"] . ") ";
		}
		
		$tData = array ();
		$db = "";
		if (!empty($_SYSHCVOL['ARCH_DB'])) {
		    $db = $_SYSHCVOL['ARCH_DB'] . ".";
		}
		
		// not using: SQL_CALC_FOUND_ROWS and FOUND_ROWS()
		$query = "SELECT logs.*, Username " .
		        "FROM " . $db . ".logs LEFT JOIN members ON members.id=logs.IdMember " . 
		        "WHERE 1=1 " . $where . " " .
		        "ORDER BY created DESC LIMIT $start_rec," . $limitcount;
		$resultRecords = $this->dao->query($query);
		
		$query = "SELECT COUNT(*) AS n " .
		        "FROM " . $db . ".logs LEFT JOIN members ON members.id=logs.IdMember " . 
		        "WHERE 1=1 " . $where;
		$result = $this->dao->query($query);
		$altogether = $result->fetch(PDB::FETCH_OBJ);
		
		return array($altogether->n => $resultRecords);
    }

}
