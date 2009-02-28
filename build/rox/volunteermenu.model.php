<?php

/**
 * An extra model class for the volunteermenu stuff
 *
 */
class VolunteermenuModel extends PAppModel
{
    /**
     * Returns the number of people due to be checked to become a member
     * of BW. The number depends on the scope of the person logged on.
     *
     * @return integer indicating the number of people waiting acceptance
     */
    public function getNumberPersonsToBeAccepted()
    {
        $R = MOD_right::get();
        $AccepterScope = mysql_real_escape_string($R->RightScope('Accepter'));
        if (count(explode('All', $AccepterScope)) > 1) {
            $InScope = " /* All countries */";
        } else {
            $InScope = "AND countries.id IN ($AccepterScope)";
        }
        $result = $this->dao->query(
            "
SELECT SQL_CACHE
    COUNT(*) AS cnt
FROM
    members,
    countries,
    cities
WHERE
    members.Status = 'Pending'  AND
    cities.id = members.IdCity  AND
    countries.id = cities.IdCountry
    $InScope
            "
        );
        $record = $result->fetch(PDB::FETCH_OBJ);
        return $record->cnt;
    }
    
    /**
     * Returns the number of people due to be checked to problems or what.
     * The number depends on the scope of the person logged on.
     *
     * @return integer indicating the number of people in need to be checked
     */
    public function getNumberPersonsToBeChecked($AccepterScope)
    {
        // FIXME: this if clause indicates a problem, doesn't it???
        // But you need database access to solve it.
        $AccepterScope = mysql_real_escape_string($AccepterScope);
        if (count(explode('All', $AccepterScope)) > 1) {
            $InScope = " /* All countries */";
        } else {
            $InScope = "AND countries.id IN (" . $AccepterScope . ")";
        }
        $result = $this->dao->query(
            "
SELECT SQL_CACHE
    COUNT(*) AS cnt
FROM
    pendingmandatory,
    countries,
    cities
WHERE
    pendingmandatory.Status = 'Pending'   AND
    cities.id = pendingmandatory.IdCity   AND
    countries.id = cities.IdCountry
    $InScope
            "
        );
        $record = $result->fetch(PDB::FETCH_OBJ);
        return $record->cnt;
    }
    
    /**
     * Returns the number of local volunteers messages to trigger
     * 
     *
     * @return integer indicating the number of Message
     */
    public function getNumberPendingLocalMess()
    {
        $R = MOD_right::get();
		
		if ($R->HasRight("ContactLocation","All"))  {
			$Query="select SQL_CACHE COUNT(*) AS cnt from localvolmessages where Status='ToSend'" ;
		}
		elseif ($R->HasRight("ContactLocation","CanTrigger")) {
			$Query="select SQL_CACHE COUNT(*) AS cnt from localvolmessages where Status='ToSend' and IdSender=".$_SESSION["IdMember"] ;
		}
		else {
			return(0) ;
		}
		 
        $result = $this->dao->query($Query);
        $record = $result->fetch(PDB::FETCH_OBJ);
        return $record->cnt;
    } // end of getNumberPendingLocalMess

	
    /**
     * Returns the number of people due to be checked to problems or what.
     * The number depends on the scope of the person logged on.
     *
     * @return integer indicating the number of people wiche need to be accepted 
         * in a Group if the current member has right to accept them
     */
    public function getNumberPersonsToAcceptInGroup($GroupScope)
    {
        // FIXME: this if clause indicates a problem, doesn't it???
        // But you need database access to solve it.
        $where="" ;
        if ($GroupScope!='"All"') {
            $tt=explode(",",$GroupScope) ;
            $where=" AND groups.Name IN (".implode(', ', $tt).")" ;
        }
        $result = $this->dao->query(
            "
SELECT SQL_CACHE
    COUNT(*) AS cnt
FROM
    membersgroups,
    groups
WHERE
    membersgroups.Status = 'WantToBeIn'  AND
    groups.id = membersgroups.IdGroup
    $where
            "
        );
        $record = $result->fetch(PDB::FETCH_OBJ);
        if (isset($record->cnt)) {
            return $record->cnt;
        }
        else {
            return(0) ;
        }
    } // end of getNumberPersonsToAcceptedInGroup

    /**
     * Returns the number of messages, which should be checked.
     *
     */
    public function getNumberMessagesToBeChecked()
    {
        $result = $this->dao->query(
            "
SELECT
    COUNT(*) AS cnt
FROM
    messages
WHERE
    Status = 'ToCheck'  AND
    messages.WhenFirstRead = '0000-00-00 00:00:00'
            "
        );
        $record = $result->fetch(PDB::FETCH_OBJ);
        return $record->cnt;
    }
    
    /**
     * Returns the number of spam messages
     *
     */
    public function getNumberSpamToBeChecked()
    {
        $result = $this->dao->query(
            "
SELECT
    COUNT(*) AS cnt
FROM
    messages,
    members AS mSender,
    members AS mReceiver
WHERE
    mSender.id        = IdSender         AND
    messages.SpamInfo = 'SpamSayMember'  AND
    mReceiver.id      = IdReceiver       AND
    mSender.Status    = 'Active'
            "
        );
        $record = $result->fetch(PDB::FETCH_OBJ);
        return $record->cnt;
    }
}


?>