<?php
/**
 * About model
 * 
 * @package about
 * @author The myTravelbook Team <http://www.sourceforge.net/projects/mytravelbook>
 * @copyright Copyright (c) 2005-2006, myTravelbook Team
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License (GPL)
 * @version $Id$
 */
class AboutModel extends PAppModel
{
    public function __construct()
    {
        parent::__construct();
    }
    
    //---------------------------------
    // needed for affiliations page
    //---------------------------------
    
    public function isVolunteer($idUser)
    {
        if (!$result = $this->dao->query('
SELECT *
FROM membersgroups
WHERE membersgroups.IdGroup = 17
AND membersgroups.Status="In" 
AND membersgroups.IdMember='. $idUser
        )) {
            // uuh
            return false;
        } else if (!$record = $result->fetch(PDB::FETCH_OBJ)) {
            // hoho !!
            return false;
        } else if (empty($record)) {
            // uu-u-uuuugh what's goin on
            return false;
        } else {
            return true;
        }
        // TODO: maybe the above error-catching-cascade can be improved?
    }
}




?>
