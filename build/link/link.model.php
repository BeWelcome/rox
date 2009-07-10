<?php
/**
 * Link model
 * 
 * @package link
 * @author Philipp (philipp)
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License (GPL)
 * @version $Id$
 */
class LinkModel extends RoxModelBase
{
    function __construct()
    {
        parent::__construct();
    }


	/**
	* Functions needed to create the link network and store it to the db
	**/
    
	function createPath ($branch,$directlinks)
	{
		$first = $branch[0];
		$lastkey = count($branch)-1;
		$last = $branch[$lastkey];
		$degree=count($branch)-1;
		$path = array($first,$last,$degree);
		foreach ($branch as $key => $value) {
			if ($key >= 1) {
				array_push($path,(array($value,$directlinks[$branch[$key-1]][$value]['totype'],$directlinks[$branch[$key-1]][$value]['reversetype'])));
			}
		}
		return($path);
	} // createPath
	
	function createLinkList() 	{
		$preferences = $this->getLinkPreferences();
		echo "createLinkList getpreference ".count($preferences)." values created<br>" ;			

		$comments = $this->getComments();
		echo "createLinkList comments ".count($comments)." values created<br>" ;			
		$specialrelation = $this->getSpecialRelation();
		echo "createLinkList specialrelation ".count($specialrelation)." values created<br>" ;

		
		
		foreach ($comments as $comment) {
			if (isset($preferences[$comment->IdFromMember])) {
				if ($preferences[$comment->IdFromMember] == 'no') {
					continue;
				}
			} if (isset($preferences[$comment->IdToMember])) {
				if ($preferences[$comment->IdToMember] == 'no') {
					continue;
				}
			}
		 
			$directlinks[$comment->IdFromMember][$comment->IdToMember]['totype'][] = $comment->Quality;
			$directlinks[$comment->IdFromMember][$comment->IdToMember]['reversetype'][] = 0;
		}
		
		foreach ($specialrelation as $value) {
			if (isset($preferences[$value->IdOwner])) {
				if ($preferences[$value->IdOwner] == 'no') {
					continue;
				}
			} if (isset($preferences[$value->IdRelation])) {
				if ($preferences[$value->IdRelation] == 'no') {
					continue;
				}
			}
		
			$directlinks[$value->IdOwner][$value->IdRelation]['totype'][] = $value->Type;
			$directlinks[$value->IdOwner][$value->IdRelation]['reversetype'][] = 0;
		}
		
		echo "createLinkList Starting to process ".count($directlinks)." values for reversetype<br>" ;			
		foreach ($directlinks as $key1 => $value1) {
			foreach ($value1 as $key2 => $value2) {
				if (isset($directlinks[$key2][$key1])) {
					$directlinks[$key2][$key1]['reversetype'] = $directlinks[$key1][$key2]['totype'];
				}
			}
		}

		echo "createLinkList done ".count($directlinks)." values created<br>" ;			
		
	return $directlinks;
		
		
	} // end of createLinkList
	
	function getTree($directlinks,$startids) {


		$count = 0;
		$maxdepth= 3;
		$branch = array();
		$oldid = 0;

		foreach ($startids as $key) {
			echo "<br> ### ". $key ." ####<br>";
			$matrix = array();
			$matrix[0] = array($key);
			$nolist = array($key);

			$new = 1;
			$count=0;
			echo"<br> matrix:";
			//var_dump($matrix);
			$newlist = $nolist;
			while($new == 1 && $count < $maxdepth) {
				echo "<br> --- newstep ".$count."<br>";
				$new = 0;
				$count++;
				foreach ($matrix as $key => $value) {

					$last = $value[count($value)-1];

					if (array_key_exists($last,$directlinks)) {
						$added = array();
						foreach($directlinks[$last] as $key1 => $value1) {
							if (!in_array($key1,$nolist)) {
								$temparray = $value;
								array_push($temparray,$key1);

								$matrix[] = $temparray;

								array_push($added,$key1);
							}
						}
					}
					if(count($added)>0) {
						$newlist = array_merge($newlist,$added);
						$new = 1;
					} 
						
				}
				$nolist = $newlist;
				echo "<br>nolist:"; 
				//var_dump($nolist);
			}

			echo "<br> ".count($matrix). " values to write in link list<br>";
			foreach ($matrix as $key => $value) {
				var_dump($value);
				$path = $this->createPath($value,$directlinks);
				$lastid = count($value)-1;
				$degree = count($value)-1;
				$serpath = "'".serialize($path)."'";
				$fields = array('fromID' => "$value[0]", 'toID' => "$value[$lastid]", 'degree' => "$degree", 'rank' => 'rank', 'path' => "$serpath"	);
				$this->writeLinkList($fields);
			}
			echo "<br> ".count($matrix). " values written in link list<br>";
			
		}
	}
	
	

	/**
	/ rebuild the link database
	**/
	function rebuildLinks() {
	    $this->deleteLinkList();
		$directlinks = $this->createLinkList();
		foreach ($directlinks as $key => $value) {
		    $startids[] = $key;
		 }
		 $this->getTree($directlinks,$startids);
     }
     
     function rebuildMissingLinks() {
        $directlinks = $this->createLinkList();
        $existing_ids = $this->bulkLookup(
            "
            SELECT fromID FROM linklist GROUP BY fromID
            ");
       $e_ids = array();     
       foreach ($existing_ids as $v) {
		    $e_ids[] = $v->fromID;
		}
		//var_dump($e_ids);
		$startids = array();
	    foreach ($directlinks as $key => $value) {
		    if(!in_array($key,$e_ids)) {
		        $startids[] = $key;
		    }
        }
		$startids = array_slice($startids,0,100);
		echo"<br> processing members:".implode(',',$startids)." <br>";
        $this->getTree($directlinks,$startids);    
      }
      
      
      
     /**
     / update the link database to integrate links changed since last called
     **/
     function updateLinks() {
        $changed_ids = $this->getChanges();
        $directlinks= $this->createLinkList();
        if ($changed_ids != '') {
        var_dump($changed_ids);
            foreach ($changed_ids as $id) {
                $this->removeLink($id);
            }
            $this->getTree($directlinks,$changed_ids);
        }
     }
    
    

	/** 
	* write / flush database
	**/
	
	function writeLinkList($fields) {

        return $this->dao->query(
            "	
	INSERT INTO linklist
		SET
		id = 'NULL',
		fromID = ".$fields['fromID'].",
		toID = ".$fields['toID'].",
		degree = ".$fields['degree'].",
		rank = ".$fields['rank'].",
		path = ".$fields['path']."
		"
		);
    }
	
	function deleteLinkList() {
	
		return $this->dao->query(
			"TRUNCATE TABLE `linklist`"
		);
	}
	
	function removeLink($id) {
	    return $this->dao->query(
	        "
	        DELETE FROM linklist
	        WHERE fromID = ".$id
	        );
	}
	
	
	/**
	* functions collecting connection data from other parts of the system
	* - comments
	* - special relations
	**/
	

		
	/** 
	* retrieve link information from the comment system
	**/
	
	function getChanges() {
	    $lastupdate = $this->singleLookup(
	        "SELECT UNIX_TIMESTAMP(`updated`) as updated FROM `linklist` ORDER BY `updated` DESC LIMIT 1"
	        );
	     var_dump($lastupdate);
	     
	    $comments= $this->bulkLookup(
	        "
	        SELECT `IdFromMember` FROM `comments` WHERE UNIX_TIMESTAMP(`updated`) >= ".$lastupdate->updated."-120"
	        );
	    $relations=$this->bulkLookup(
	        "
	        SELECT `IdOwner` FROM `specialrelations` WHERE UNIX_TIMESTAMP(`updated`) >= ".$lastupdate->updated."-120"
	        );
	    $ids=array();
    
	    foreach($comments as $comment) {
	        $ids[] = $comment->IdFromMember;
        }
	    foreach($relations as $relation) {
	        $ids[] = $relation->IdOwner;
        }
        $changed_ids = array_unique($ids);
        foreach ($changed_ids as $id) {
            $links = $this->bulkLookup(
                "
                SELECT `fromID`,path FROM `linklist` WHERE `path` LIKE '%{i:0;i:".$id.";%' AND (`toID` != ".$id." AND fromID != ".$id.")
                ");
            if ($links) {
                foreach($links as $link) {
                    $ids[] = $link->fromID;
                }   
            }
        }

        return(array_unique($ids));
        
    }
	
	function getComments()
    {
		return $this->bulkLookup(
            "
			SELECT `comments`.`IdFromMember` AS `IdFromMember`,`comments`.`IdToMember` AS `IdToMember`,`comments`.`Quality` AS `Quality` , 
			`members`.`id`, `members`.`status`
			FROM `comments`, `members` 
			WHERE `IdToMember` = `members`.`id` 
			AND (`members`.`Status` in ('Active','ChoiceInactive','OutOfRemind','ActiveHidden'))
			AND NOT FIND_IN_SET('NeverMetInRealLife',`comments`.`Lenght`) 
			AND (FIND_IN_SET('hewasmyguest',`comments`.`Lenght`) or 
					 FIND_IN_SET('hehostedme',`comments`.`Lenght`) or  
					 FIND_IN_SET('OnlyOnce',`comments`.`Lenght`) or  
					 FIND_IN_SET('HeIsMyFamily',`comments`.`Lenght`) or  
					 FIND_IN_SET('HeHisMyOldCloseFriend',`comments`.`Lenght`) )  
			ORDER BY `IdFromMember`,`IdToMember` Asc
            "
        );
	}
	
	/**
	* retrieve link information from the special relation system 
	**/
	
		function getSpecialRelation()
    {
		return $this->bulkLookup(
            "
			SELECT `IdOwner`,`IdRelation`,`Type`, `members`.`id`, `members`.`status`
			FROM `specialrelations` , `members`
			WHERE `IdRelation` = `members`.`id` 
			AND (`members`.`Status` in ('Active','ChoiceInactive','OutOfRemind') )
			ORDER BY `IdOwner`,`IdRelation` Asc
            "
        );
	}
	
	
	/**
	* functions to retrieve link infromation from the db
	**/
	
	function dbFriendsID($fromid,$degree = 1,$limit = 10) {
			$ss="SELECT `toID`
			FROM `linklist` 
			WHERE linklist.fromID = $fromid AND linklist.degree = $degree
			LIMIT ".(int)$limit ;
			
//			echo $ss,"<br/>" ;
			return $this->bulkLookup($ss);
	}
	
	function dbFriends($fromid,$degree = 1,$limit = 10) {
		return $this->bulkLookup(
			"
			SELECT *
			FROM `linklist` 
			WHERE linklist.fromID = $fromid AND linklist.degree = $degree
			LIMIT ".(int)$limit
		);
	}
	
	
	function dbLinks($fromid,$toid,$limit=5) {
		return $this->bulkLookup(
			"
			SELECT * 
			FROM `linklist` 
			WHERE linklist.fromID = $fromid AND linklist.toID = $toid
			LIMIT ".(int)$limit
		);
		
	}	


	/**
	* retrieve useful information about members from the db
	**/
    function getMemberdata($ids)
    {
		$memberdata=array() ;
		if (count($ids)<=0) {
			return $memberdata ; // Returns nothing if no Id where given
		} 
		//var_dump($ids);
//		$idquery = implode(' OR `members`.`id` = ',$ids);
		$idquery = implode(',',$ids);
//		echo "\$idquery=".$idquery."<br />" ;
		//var_dump($idquery);
		
		$rPref=$this->singleLookup("select `id`,`DefaultValue` from `preferences` where `preferences`.`codeName` = 'PreferenceLinkPrivacy'") ;
		if (!isset($rPref->id)) {
			die ("You need to create  the preference : 'PreferenceLinkPrivacy'") ;
		}

        $result = $this->bulkLookup( "
			SELECT SQL_CACHE members.Username, 'NbComment',memberspreferences.Value as PreferenceLinkPrivacy,'NbTrust','Verified',members.id, members.id as IdMember, g1.Name AS City, g2.Name AS Country,`members`.`Status`
			FROM members
            JOIN addresses ON addresses.IdMember = members.id AND addresses.rank = 0
			LEFT JOIN geonames_cache AS g1 ON addresses.IdCity =  g1.geonameid 
			LEFT JOIN geonames_cache AS g2 ON g1.parentCountryId = g2.geonameid 
			LEFT JOIN memberspreferences ON  `memberspreferences`.`IdPreference`=".$rPref->id." and `memberspreferences`.`IdMember`=`members`.`id` 
			WHERE `members`.`id` in ($idquery) and (`members`.`Status` in ('Active','ChoiceInactive','OutOfRemind'))
			"
			);
		foreach ($result as $value) {
			if (empty($value->PreferenceLinkPrivacy)) {
				$value->PreferenceLinkPrivacy=$rPref->DefaultValue ;
			}
			if ($value->PreferenceLinkPrivacy=='no') continue ; // Skip member who have chosen PreferenceLinkPrivacy=='no'
			
			// Retrieve the verification level of this member
			$ss="select max(Type) as TypeVerif from verifiedmembers where IdVerified=".$value->IdMember ;
//			echo $ss ;
			$rowVerified=$this->singleLookup($ss) ;
			if (isset($rowVerified->TypeVerif)) {
				$value->Verified=$rowVerified->TypeVerif ;
			}
			else {
				$value->Verified="" ; // This is a not verified member so empty string
			}
			$ss="select count(*) as Cnt from comments where IdToMember=".$value->IdMember ;
			$rr=$this->singleLookup($ss);
			$value->NbComment=$rr->Cnt ;
			
			$ss="select count(*) as Cnt from comments where IdToMember=".$value->IdMember." and Quality='Good'";
			$rr=$this->singleLookup($ss);
			$value->NbTrust=$rr->Cnt ;
			
			$memberdata[$value->id] = $value;
		}
		return $memberdata;
    } // end of	getMemberdata
	

	/** 
	* retrieve the Preference setting for the link network (Yes, no, hidden)
	**/
	function getLinkPreferences() {
		$result =  $this->bulkLookup(
			"
			SELECT `IdMember`,`Value`,`preferences`.`DefaultValue`
			FROM `preferences`,`memberspreferences`
			WHERE `preferences`.`id` = `memberspreferences`.`IdPreference`
			AND `preferences`.`codeName` = 'PreferenceLinkPrivacy'
			"
		);
		
		foreach ($result as $value) {
			$prefarray[$value->IdMember] = $value->Value;
			}
		return $prefarray;
	} // end of getLinkPreferences

	/* *
	* helper functions to prepare output
	**/
		function getMemberID($username)
	{
		if (is_numeric($username)) return $username ; 
		$result = $this->singleLookup(
		"
		SELECT `id` 
		FROM `members`
		WHERE `Username` = '$username'
		"
		);
		if (isset($result->id)) {
			return($result->id) ;
		}
		else {
			return (-1) ;
		}
	}
	
	
	function getIdsFromPath($path)
	{
		$inpath = array($path[0]);
		for ($i=3; $i<3+$path[2]; $i++) {
			array_push($inpath, $path[$i][0]);
		}
		return $inpath;
	}
	
	/**	
	 *  often used functions to get data from the link system
	 * 
	 **/
	
	/**
	* returns an array of IDs
	* get $limit number of friends of the distance of $degree for $from member (id or username) 
	* without additional $degree / $limit parameters it returnsthe ID for 10 direct friends
	**/
	
	function getFriends($from,$degree = 1,$limit = 10)
	{
		if (!ctype_digit($from)) {
			$from = $this->getMemberID($from);
		}
		$result = $this->dbFriendsID($from,$degree,$limit);

		$friendIDs=array() ; // To initialize because if nothing is found we will have a void variable
		foreach ($result as $value) {
			$friendIDs[] = $value->toID;
		}
		$friendIDs = array_unique($friendIDs);	
		return $friendIDs;	

	}
	
	/**
	* returns an array (member ID as key) with useful memberdata for all IDs
	* get $limit number of friends of the distance of $degree for $from member (id or username) 
	* without additional $degree / $limit parameters it returnsthe ID for 10 direct friends
	**/
	function getFriendsFull($from,$degree = 1,$limit = 10)
	{
		$friendsData=array() ;
		if (!ctype_digit($from)) {
			$from = $this->getMemberID($from);
		}
		$result = $this->dbFriendsID($from,$degree,$limit);
		$friendIDs=array() ; // To initialize because if nothing is found we will have a void variable
		foreach ($result as $value) {
			$friendIDs[] = $value->toID;
		}
		$memberData = $this->getMemberdata($friendIDs);
		foreach ($memberData as $value) {
			$friendsData[$value->id]= $value;
		}
		//var_dump($friendsData);
		return $friendsData;		
	}
	
	function getDegree($from,$to)
	{
	}
	
	function getLinks($from,$to,$limit = 10) {
		if (!ctype_digit($from)) {
			$from = $this->getMemberID($from);
		}
		if (!ctype_digit($to)) {
			$to = $this->getMemberID($to);
		}
		$result = $this->dbLinks($from,$to,$limit);
		
		if (empty($result)) {
			return false;
		} else {
			foreach ($result as $key => $value) {
				$path[$key] = unserialize($value->path);
				$ids[$key] = $this->getIdsFromPath($path[$key]);
			}
			//var_dump($ids);
			return($ids);
		}
	}
			
	
	function getLinksFull($from,$to,$limit = 10)
	{
		if (!ctype_digit($from)) {
			$from = $this->getMemberID($from);
		}
		if (!ctype_digit($to)) {
			$to = $this->getMemberID($to);
		}
		$result = $this->dbLinks($from,$to,$limit);
		//var_dump($result);
		foreach ($result as $key => $value) {
			$path[$key] = unserialize($value->path);
			$ids[$key] = $this->getIdsFromPath($path[$key]);
		}
		if (isset($ids)) {
			$idlist = array();
			foreach ($ids as $value) {
				foreach ($value as $id) {
					array_push($idlist,$id);
				}
			}
			$idlist = array_unique($idlist);
			$memberData = $this->getMemberdata($idlist);
			foreach ($ids as $key1 => $value1) {
				foreach ($value1 as $key2 => $value2) {
					$linkdata[$key1][$key2]['memberdata'] = $memberData[$value2];
					if($key2 != 0) {
						$linkdata[$key1][$key2]['totype'] = $path[$key1][$key2+2][1];
						$linkdata[$key1][$key2]['reversetype'] = $path[$key1][$key2+2][2];
					}
				}
			}
		} else {
			$linkdata = false;
		}
		//echo "<br>";
		//var_dump($linkdata);
		//echo "<br>";
		return $linkdata;
	} // end of getLinksFull
	

	 

}


?>
