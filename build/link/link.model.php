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
	}
	
	function createLinkList()
	{
		$comments = $this->getcomments();
		$specialrelation = $this->getSpecialRelation();
		
		
		foreach ($comments as $comment) {
			$directlinks[$comment->IdFromMember][$comment->IdToMember]['totype'][] = $comment->Quality;
			$directlinks[$comment->IdFromMember][$comment->IdToMember]['reversetype'][] = 0;
		}
		
		foreach ($specialrelation as $value) {
			$directlinks[$value->IdOwner][$value->IdRelation]['totype'][] = $value->Type;
			$directlinks[$value->IdOwner][$value->IdRelation]['reversetype'][] = 0;
		}
		
		foreach ($directlinks as $key1 => $value1) {
			foreach ($value1 as $key2 => $value2) {
				if (isset($directlinks[$key2][$key1])) {
					$directlinks[$key2][$key1]['reversetype'] = $directlinks[$key1][$key2]['totype'];
				}
			}
		}
			
		foreach ($directlinks as $key1 => $value1) {
			foreach ($value1 as $key2 => $value2) {
				echo $key1." -> ".$key2." : ";
					foreach ($value2['totype'] as $totype) {
						echo $totype." ; ";
					}
					echo " // ";
					if(isset($value2['reversetype'])){
						foreach ($value2['reversetype'] as $reversetype) {
							echo $reversetype." ; ";
						}
					}
				echo "<br>";
			}
			echo "---------<br>";
		}
			
	return $directlinks;
		
		
	}
	
	function getTree() {
		echo "<br>in getTree<br>";
		$directlinks = $this->createLinkList();

		$count = 0;
		$depth= 1;
		$branch = array();
		$oldid = 0;

		foreach ($directlinks as $key => $value) {
			echo "<br> ### ". $key ." ####<br>";
			$matrix = array();
			$matrix[0] = array($key);
			$nolist = array($key);

			$new = 1;
			$count=0;
			echo"<br> matrix:";
			var_dump($matrix);
			$newlist = $nolist;
			while($new == 1 && $count < 50) {
				echo "<br> --- newstep ".$count."<br>";
				$new = 0;
				$count++;
				foreach ($matrix as $key => $value) {
					var_dump($value);
					$last = $value[count($value)-1];
					echo "<br> ";
					if (array_key_exists($last,$directlinks)) {
						$added = array();
						foreach($directlinks[$last] as $key1 => $value1) {
							if (!in_array($key1,$nolist)) {
								$temparray = $value;
								array_push($temparray,$key1);
								print_r($temparray);
								$matrix[] = $temparray;
								echo "<br>";
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
				var_dump($nolist);
			}
			foreach ($matrix as $key => $value) {
				echo "<br> write: ";
				var_dump($value);
				$path = $this->createPath($value,$directlinks);
				$lastid = count($value)-1;
				$degree = count($value)-1;
				$serpath = "'".serialize($path)."'";
				$fields = array('fromID' => "$value[0]", 'toID' => "$value[$lastid]", 'degree' => "$degree", 'rank' => 'rank', 'path' => "$serpath"	);
				$this->writeLinkList($fields);
			}
			
		}
	}
	
	

		
		
	// function extendBranch($id,&$matrix,$array,$nolist,$directlinks) {
		// if (array_key_exists($id,$directlinks)) {
			// $added = array();
			// foreach($directlinks[$id] as $key => $value) {
				// if (!in_array($key,$nolist)) {
					// $temparray = $array;
					// array_push($temparray,$key);
					// print_r($temparray);
					// $matrix[] = $temparray;
					// echo "<br>";
					// array_push($added,$key);
				// }
			// }
				// //var_dump($matrix);
			// echo "<br>--";
			// return $added;
		// }
	// }
			
	
	
	// function getBranches($id,$directlinks,&$inlist,&$count,$depth,$branch,$oldid) {

		// if ($count <= 50000) {
			// array_push($branch,$id); 
			// $oldid = $id;
			// $count++;
			// $depth++;
			// array_push($inlist,$oldid);
			// if (array_key_exists($id,$directlinks)) {
				// foreach ($directlinks[$id] as $key => $val) {
					//if (!in_array($key,$inlist)) {
						// $this->getBranches ($key,$directlinks,$inlist,$count,$depth,$branch,$oldid);
					// }
				// }
			// }
			// if ($depth>=2) {
			// //print_r($branch);
			// $path = $this->createPath($branch,$depth-1,$directlinks);
			// $lastid = count($branch)-1;
			// $serpath = "'".serialize($path)."'";
			// $fields = array('fromID' => "$branch[0]", 'toID' => "$branch[$lastid]", 'degree' => $depth-1, 'rank' => 'rank', 'path' => "$serpath"	);
			// $this->writeLinkList($fields);
			// }
		// } else {
			// echo "ende ".$count;
		// }
		// }
	// function getBranches($id,$directlinks,$inlist,&$count,$depth,$branch,$oldid,&$inlist2) {

		// if ($count <= 50000) {
						// $temparray = array();
			// array_push($branch,$id);
			// $oldid = $id;
			// $count++;
			// $depth++;
			// array_push($inlist,$oldid);
			// if (array_key_exists($id,$directlinks)) {
				// foreach ($directlinks[$id] as $key => $val) {
					// if (!in_array($key,$inlist) && !in_array($key,$inlist2)) {
						// $this->getBranches ($key,$directlinks,$inlist,$count,$depth,$branch,$oldid,$inlist2);
						// array_push($temparray,$key);
					// } 
				// }
			// }
			// array_push($inlist2,$temparray);
			// if ($depth>=2) {
			// print_r($branch);
			// print_r($inlist);
			// $path = $this->createPath($branch,$depth-1,$directlinks);
			// $lastid = count($branch)-1;
			// $serpath = "'".serialize($path)."'";
			// $fields = array('fromID' => "$branch[0]", 'toID' => "$branch[$lastid]", 'degree' => $depth-1, 'rank' => 'rank', 'path' => "$serpath"	);
			// $this->writeLinkList($fields);
			// }
		// } else {
			// echo "ende ".$count;
		// }
		// }		
		
		
		
		
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
	
	/**
	* functions collecting connection data from other parts of the system
	* - comments
	* - special relations
	**/
	
	function getComments()
    {
		return $this->bulkLookup(
            "
			SELECT `IdFromMember`,`IdToMember`,`Quality` FROM `comments` ORDER BY `IdFromMember`,`IdToMember` Asc
            "
        );
	}
	
		function getSpecialRelation()
    {
		return $this->bulkLookup(
            "
			SELECT `IdOwner`,`IdRelation`,`Type` FROM `specialrelations` ORDER BY `IdOwner`,`IdRelation` Asc
            "
        );
	}
	
	
	/**
	* functions to retrieve link infromation from the db
	**/
	
	function dbFriendsID($fromid,$degree = 1,$limit = 10) {
		return $this->bulkLookup(
			"
			SELECT `toID`
			FROM `linklist` 
			WHERE linklist.fromID = $fromid AND linklist.degree = $degree
			LIMIT ".(int)$limit
		);
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
		$idquery = implode(' OR `members`.`id` = ',$ids);
		//var_dump($idquery);
        $result = $this->bulkLookup( "
			SELECT SQL_CACHE members.Username, members.id, city.Name AS City, country.Name AS Country
			FROM `members` 
			LEFT JOIN cities AS city ON members.IdCity =  city.id 
			LEFT JOIN countries AS country ON city.IdCountry = country.id 
			WHERE (`members`.`id`= $idquery )
			"
			);
		foreach ($result as $value) {
			$memberdata[$value->id] = $value;
		}
		return $memberdata;
    } // end of	getMemberdata
	


	/* *
	* helper functions to prepare output
	**/
		function getMemberID($username)
	{
		$result = $this->singleLookup(
		"
		SELECT `id` 
		FROM `members`
		WHERE `Username` = '$username'
		"
		);
		$userid = $result->id;
		return $userid;
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
		if (!ctype_digit($from)) {
			$from = $this->getMemberID($from);
		}
		$result = $this->dbFriendsID($from,$degree,$limit);
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
		//var_dump($result);
		foreach ($result as $key => $value) {
			$path[$key] = unserialize($value->path);
			$ids[$key] = $this->getIdsFromPath($path[$key]);
		}
		//var_dump($ids);
		return($ids);
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
		echo "<br>";
		//var_dump($linkdata);
		echo "<br>";
		return $linkdata;
	}
	

	 


}


?>
