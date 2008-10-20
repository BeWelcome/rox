<?php


class MembersModel extends RoxModelBase
{
    
    private $profile_language = null;
    
    public function getMemberWithUsername($username)
    {
        $username = mysql_real_escape_string($username);
        if ($values = $this->singleLookup_assoc(
            "
SELECT *
FROM members
WHERE Username = '$username'
            "
        )) {
            return new Member($values, $this->dao);
        } else {
            return false;
        }
    }
    
    public function getMemberWithId($id)
    {
        $id = (int)$id;
        if ($values = $this->singleLookup_assoc(
            "
SELECT *
FROM members
WHERE id = $id
            "
        )) {
            return new Member($values, $this->dao);
        } else {
            return false;
        }
    }
    
    
    /**
     * Not totally sure it belongs here - but better this
     * than member object? As it's more of a business of this
     * model to know about different states of the member 
     * object to be displayed..
     */
    public function set_profile_language($langcode)
    {
        //TODO: check that 
        //1) this is a language recognized by the bw system
        //2) there's content for this member in this language
        //else: use english = the default already set
        $langcode = mysql_real_escape_string($langcode);
        if ($language = $this->singleLookup(
            "
SELECT SQL_CACHE
    id,
    ShortCode
FROM
    languages
WHERE
    shortcode = '$langcode'
            "
        )) {
            $this->profile_language = $language;
        } else {
            $l = new stdClass;
            $l->id = 0;
            $l->ShortCode = 'en';
            $this->profile_language = $l;
        }
    }
    
    
    public function get_profile_language()
    {
        if(isset($this->profile_language)) {
            return $this->profile_language;
        } else {
            $l = new stdClass;
            $l->id = 0;
            $l->ShortCode = 'en';
            $this->profile_language = $l;
            echo "l:";
            return $this->profile_language;
        }
    }

    // checkCommentForm - NOT FINISHED YET !
    public function checkCommentForm(&$vars)
    {
        $errors = array();
        
        // geonameid
        if (empty($vars['geonameid']) || empty($vars['countryname'])) {
            $errors[] = 'SignupErrorProvideLocation';
        }
        
        $TCom = $this->get_comments_commenter($_SESSION['IdMember']);
        
        // Mark if an admin's check is needed for this comment (in case it is "bad")
		$AdminAction = "NothingNeeded";
		if ($Quality == "Bad") {
			$AdminAction = "AdminCommentMustCheck";
		}
		if (!isset ($TCom->id)) {
			$str = "insert into comments(IdToMember,IdFromMember,Lenght,Quality,TextWhere,TextFree,AdminAction,created) values (" . $IdMember . "," . $_SESSION['IdMember'] . ",'" . $LenghtComments . "','" . $Quality . "','" . $TextWhere . "','" . $TextFree . "','" . $AdminAction . "',now())";
			$qry = sql_query($str) or bw_error($str);
		    $TCom->id = mysql_insert_id() ;
		} else {
			$str = "update comments set AdminAction='" . $AdminAction . "',IdToMember=" . $IdMember . ",IdFromMember=" . $_SESSION['IdMember'] . ",Lenght='" . $LenghtComments . "',Quality='" . $Quality . "',TextFree='" . $TextFree . "' where id=" . $TCom->id;
			$qry = sql_query($str) or bw_error($str);
		}
    }
    

}


?>
