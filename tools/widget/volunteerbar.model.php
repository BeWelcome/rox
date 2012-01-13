<?php


class VolunteerbarModel extends PAppModel
{
    
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
    } // end of getNumberPendingLocalMessageToTrigger


    /**
     * Returns the number of people due to be accepted.
     * The number depends on the scope of the person logged on.
     *
     * @return integer Number of members with Pending status in user's scope
     */
    public function getNumberPersonsToBeAccepted() {
        $userRights = MOD_right::get();

        // Don't count for users without Accepter permission
        if ($userRights->hasRight('Accepter') < 1) {
            return 0;
        }

        // Can user accept for all regions?
        if ($userRights->hasRight('Accepter', 'All')) {
            // Simply select all members with Pending status
            $query = "
                SELECT SQL_CACHE
                    COUNT(*) AS count
                FROM
                    members
                WHERE
                    Status = 'Pending'
                ";
        } else {
            // Select members with Pending status in user's scope
            $scope = $userRights->RightScope('Accepter');
            $query = "
                SELECT SQL_CACHE
                    COUNT(*) AS count
                FROM
                    members,
                    countries,
                    geonames_cache
                WHERE
                    members.IdCity = geonames_cache.geonameid
                    AND
                    countries.id = geonames_cache.parentCountryId
                    AND (
                        countries.Name IN (" . $scope . ")
                        OR
                        countries.id IN (" . $scope . ")
                        )
                    AND
                    Status='Pending'
                ";
        }

        $result = $this->dao->query($query);
        $record = $result->fetch(PDB::FETCH_OBJ);
        return $record->count;
    }

    /**
     * Returns the number of people due to be checked to problems or what.
     * The number depends on the scope of the person logged on.
     *
     * $_AccepterScope="" is an optional value for accepter Scope which can be used for performance if it was already fetched from database
     * @return integer indicating the number of people in need to be checked
     */
    public function getNumberPersonsToBeChecked($_AccepterScope = "") {
        $R = MOD_right::get();
        if ($_AccepterScope != "") {
            $AccepterScope = $_AccepterScope ;
        } else {
            $AccepterScope = $R->RightScope('Accepter');
        }
        if ($AccepterScope == "") {
            return 0;
        }

        if ($R->hasRight('Accepter','All'))  {
            $query = "
                SELECT SQL_CACHE
                    COUNT(*) AS cnt
                FROM
                    pendingmandatory,
                    geonames_countries,
                    geonames_cache
                WHERE
                    (
                        pendingmandatory.Status = 'NeedMore'
                        OR
                        pendingmandatory.Status = 'Pending'
                    )
                    AND
                    geonames_cache.geonameid = pendingmandatory.IdCity
                    AND
                    geonames_countries.iso_alpha2 = geonames_cache.fk_countrycode
                ";
        } else {
            $query = "
                SELECT SQL_CACHE
                    COUNT(*) AS cnt
                FROM
                    pendingmandatory,
                    geonames_countries,
                    geonames_cache
                WHERE
                    (
                        pendingmandatory.Status = 'NeedMore'
                        OR
                        pendingmandatory.Status = 'Pending'
                    )
                    AND
                    geonames_cache.geonameid = pendingmandatory.IdCity
                    AND
                    geonames_countries.iso_alpha2 = geonames_cache.fk_countrycode
                    AND
                    (
                        geonames_countries.iso_alpha2 IN ($AccepterScope)
                        OR
                        geonames_countries.name IN ($AccepterScope)
                    )
                ";
        }
        $result = $this->dao->query($query);
        $record = $result->fetch(PDB::FETCH_OBJ);
        return $record->cnt;
    }

    /**
     * Returns the number of people due to be checked to problems or what.
     * The number depends on the scope of the person logged on.
     *
		 * $_GroupScope="" is an optional value for group Scope which can be used for performance if it was already fetched from database
     * @return integer indicating the number of people wiche need to be accepted 
         * in a Group if the current member has right to accept them
     */
    public function getNumberPersonsToAcceptInGroup($_GroupScope="")
    {
        		$R = MOD_right::get();
		 		if ($_GroupScope!="") {
        		 $GroupScope=$_GroupScope ;
				}
				else {
        		 $GroupScope=$R->RightScope('Group');
				}
				if ($GroupScope=="") return 0 ;

        		if ($R->hasRight('Group','All'))  {
                	$where="" ;
				 }
				 else {
                         $tt=explode(",",$GroupScope) ;
                         $where="(" ;
                         foreach ($tt as $Scope) {
                                         if ($where!="(") {
                                                $where.="," ;
                                         }
                                         $where=$where.$Scope;
                         }
                         $where=" and `groups`.`Name` in " .$where.")" ;
                }
        $query = 'SELECT SQL_CACHE COUNT(*) AS cnt FROM `membersgroups`,`groups` where `membersgroups`.`Status`="WantToBeIn" and `groups`.`id`=`membersgroups`.`IdGroup`'.$where ;
//   die($query) ;
        $result = $this->dao->query($query);
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
        $query = '
SELECT COUNT(*) AS cnt
FROM messages
WHERE Status=\'ToCheck\'
AND messages.WhenFirstRead=\'0000-00-00 00:00:00\'';
        $result = $this->dao->query($query);
        $record = $result->fetch(PDB::FETCH_OBJ);
        return $record->cnt;
    }
    
    /**
     * Returns the number of spam messages
     *
     * TODO: merge with other getNumberSpamToBeChecked() methods
     */
    public function getNumberSpamToBeChecked()
    {
        $query = '
SELECT COUNT(*) AS cnt
FROM messages, members AS mSender, members AS mReceiver
WHERE mSender.id=IdSender
AND messages.SpamInfo=\'SpamSayMember\'
AND mReceiver.id=IdReceiver
AND (
        mSender.Status=\'Active\'
    OR
        mSender.Status=\'Pending\'
    )
';
        $result = $this->dao->query($query);
        $record = $result->fetch(PDB::FETCH_OBJ);
        return $record->cnt;
    }

    
}


?>