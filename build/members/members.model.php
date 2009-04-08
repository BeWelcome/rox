<?php


class MembersModel extends RoxModelBase
{
    
    private $profile_language = null;
    
    /**
     * Constructor
     *
     * @param void
     */
    public function __construct()
    {
        parent::__construct();
        $this->bootstrap();
    }
    
    public function getMemberWithUsername($username)
    {
        return $this->createEntity('Member')->findByUsername($username);
    }
    
    public function getMemberWithId($id)
    {
        if (!($id = intval($id)))
        {
            return false;
        }

        return $this->createEntity('Member', $id);
    }



      public function get_relation_between_members($IdMember_rel) 
      {
          $myself = $this->getMemberWithId($_SESSION['IdMember']);
          $member = $this->getMemberWithId($IdMember_rel);
          $words = $this->getWords();
          $all_relations = $member->all_relations();
          $relation = array();
          $relation['member'] = array();
          if (count($all_relations) > 0) {
              foreach ($all_relations as $rel) {
                if ($rel->IdRelation == $myself->id)
                    $relation['member'] = $rel;
              }
          }
          $all_relations_myself = $myself->all_relations();
          $relation['myself'] = array();
          if (count($all_relations_myself) > 0) {
              foreach ($all_relations_myself as $rel) {
                if ($rel->IdRelation == $member->id)
                    $relation['myself'] = $rel;
              }
          }
          return $relation;
      }

    /**
     * set the location of a member
     */
    public function setLocation($IdMember,$geonameid = false)
    {
    
        // Address IdCity address must only consider Populated palces (definition of cities), it also must consider the address checking process
    
        $Rank=0 ; // Rank=0 means the main address, todo when we will deal with several addresses we will need to consider the other rank Values ;
        $IdMember = (int)$IdMember;
        $geonameid = (int)($geonameid);
        
        $errors = array();
        
        if (empty($IdMember)) {
            // name is not set:
            $errors['Name'] = 'Name not set';
        }
        if (empty($geonameid)) {
            // name is not set:
            $errors['Geonameid'] = 'Geoname not set';
        }
        
        // get Member's current Location
        $result = $this->singleLookup(
            "
SELECT  members.IdCity
FROM    members
WHERE   members.id = $IdMember
            "
        );
        if (!isset($result) || $result->IdCity != $geonameid) {
            // Check Geo and maybe add location 
            $geomodel = new GeoModel(); 
            if(!$geomodel->getDataById($geonameid)) {
                // if the geonameid is not in our DB, let's add it
                if (!$geomodel->addGeonameId($geonameid,'member_primary')) {
                    $vars['errors'] = array('geoinserterror');
                    return false;
                }
            } else {
                // the geonameid is in our DB, so just update the counters
                //get id for usagetype:
                $usagetypeId = $geomodel->getUsagetypeId('member_primary')->id;
                $update = $geomodel->updateUsageCounter($geonameid,$usagetypeId,'add');
            }
            
            $result = $this->singleLookup(
                "
UPDATE  addresses
SET     IdCity = $geonameid
WHERE   IdMember = $IdMember and Rank=".$Rank
            );
            
            // name is not set:
            if (!empty($result)) $errors['Geonameid'] = 'Geoname not set';
            
            $result = $this->singleLookup(
                "
UPDATE  members
SET     IdCity = $geonameid
WHERE   id = $IdMember
                "
            );
            if (!empty($result)) $errors['Geonameid'] = 'Member IdCity not set';
            else MOD_log::get()->write("The Member with the Id: ".$IdMember." changed his location to Geo-Id: ".$geonameid, "Members");
            return array(
                'errors' => $errors,
                'IdMember' => $result
                );
        } else {
            // geonameid hasn't changed
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
    ShortCode,
    Name
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
            $l->Name = 'English';
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
            $l->Name = 'English';
            $this->profile_language = $l;
            return $this->profile_language;
        }
    }
    
    /**
     * Set the languages spoken by member
     */
    public function set_language_spoken($IdLanguage,$Level,$IdMember) 
    {
        $lang = $this->dao->query("
DELETE 
FROM
    memberslanguageslevel
WHERE
    IdLanguage = '$IdLanguage' AND
    IdMember = '$IdMember'
        ");
        $s = $this->dao->query("
REPLACE INTO
    memberslanguageslevel
    (
    IdLanguage,
    Level,
    IdMember
    )
VALUES
    (
    '$IdLanguage',
    '$Level',
    '$IdMember'
    )
        ");
    }
    
    /**
     * Delete a profile translation for a member
     */
    public function delete_translation_multiple($trad_ids = array(),$IdOwner, $lang_id) 
    {
        $words = new MOD_words();
        foreach ($trad_ids as $trad_id){
            $words->deleteMTrad($trad_id, $IdOwner, $lang_id);
        }
    }
        
    /**
     * Set the preferred language for a member
     */
    public function set_preference($IdMember,$IdPreference,$Value) 
    {
        $rr = $this->singleLookup("select memberspreferences.id as id from memberspreferences,preferences where IdMember=" . $IdMember . " and IdPreference=preferences.id and preferences.id=" . $IdPreference );
        if (isset ($rr->id)) {
        // LogStr("updating one preference " . $rPref->codeName . "To Value <b>/" . $Value . " </b>", "Update Preference");
        $s = $this->dao->query("
UPDATE
    memberspreferences
SET
    Value = '".$this->dao->escape($Value)."'
WHERE
    id = ". $rr->id
        );
        if(!$s) var_dump('AAARGH 2 ');

        } else {
        $s = $this->dao->query("
INSERT INTO
    memberspreferences
    (
    IdMember,
    IdPreference,
    Value,
    created
    )
VALUES
    (
    '$IdMember',
    '$IdPreference',
    '$Value',
    NOW()
    )
        ");
        // LogStr("inserting one preference " . $rPref->codeName . "To Value <b>/" . $Value . " </b>", "Update Preference");
    if(!$s) var_dump('AAARGH 2 ');
        }
    }
    
    /**
     * Set a member's profile public/private
     */
    public function set_public_profile ($IdMember,$Public = false) 
    {
        $rr = $this->singleLookup(
            "
SELECT *
FROM memberspublicprofiles 
WHERE IdMember = ".$IdMember
         );
        if (!$rr && $Public == true) {
        $s = $this->dao->query("
INSERT INTO
    memberspublicprofiles
    (
    IdMember,
    created,
    Type
    )
VALUES
    (
    '$IdMember',
    NOW(),
    'normal'
    )
        ");
        } elseif ($rr && $Public == false) {
        $s = $this->dao->query("
DELETE FROM
    memberspublicprofiles
WHERE
    id = ". $rr->id
        );
        }
    }
    
    
    

    // checkCommentForm - NOT FINISHED YET !
    public function checkCommentForm(&$vars)
    {
        $errors = array();
        
        
        // sample!
        if (empty($vars['geonameid']) || empty($vars['countryname'])) {
            $errors[] = 'SignupErrorProvideLocation';
        }
        
    }
    
    public function addComment($TCom,&$vars)
    {
        $return = true;
        // Mark if an admin's check is needed for this comment (in case it is "bad")
        $AdminAction = "NothingNeeded";
        if ($vars['Quality'] == "Bad") {
            $AdminAction = "AdminCommentMustCheck";
            // notify OTRS
            //Load the files we'll need
            // require_once "bw/lib/swift/Swift.php";
            // require_once "bw/lib/swift/Swift/Connection/SMTP.php";
            // require_once "bw/lib/swift/Swift/Message/Encoder.php";
            // $swift =& new Swift(new Swift_Connection_SMTP("localhost"));
            // $subj = "Bad comment from  " .$mCommenter->Username.  " about " . fUsername($IdMember) ;
            // $text = "Please check the comments. A bad comment was posted by " . $mCommenter->Username.  " about " . fUsername($IdMember) . "\n";
            // $text .= $mCommenter->Username . "\n" . ww("CommentQuality_" . $Quality) . "\n" . GetStrParam("TextWhere") . "\n" . GetStrParam("Commenter");
            // bw_mail($_SYSHCVOL['CommentNotificationSenderMail'], $subj, $text, "", $_SYSHCVOL['CommentNotificationSenderMail'], $defLanguage, "no", "", "");
        }
        $syshcvol = PVars::getObj('syshcvol');
        $max = count($syshcvol->LenghtComments);
        $tt = $syshcvol->LenghtComments;
        $LenghtComments = "";
        for ($ii = 0; $ii < $max; $ii++) {
            $var = $tt[$ii];
            if (isset ($vars["Comment_" . $var])) {
                if ($LenghtComments != "")
                    $LenghtComments = $LenghtComments . ",";
                $LenghtComments = $LenghtComments . $var;
            }
        }
        if (!isset ($TCom->id)) {
            $str = "
INSERT INTO
    comments (
        IdToMember,
        IdFromMember,
        Lenght,
        Quality,
        TextWhere,
        TextFree,
        AdminAction,
        created
    )
    values (
        " . $vars['IdMember'] . ",
        " . $_SESSION['IdMember'] . ",
        '" . $LenghtComments . "','" . $vars['Quality'] . "',
        '" . $this->dao->escape($vars['TextWhere']) . "',
        '" . $this->dao->escape($vars['TextFree']) . "',
        '" . $AdminAction . "',now()
    )"
    ;
            $qry = $this->dao->query($str);
            if(!$qry) $return = false;
        } else {
            $textfree_add = ($vars['TextFree'] != '') ? ('<hr>' . $vars['TextFree']) : '';
            $str = "
UPDATE
    comments
SET 
    AdminAction='" . $AdminAction . "',
    IdToMember=" . $vars['IdMember'] . ",
    IdFromMember=" . $_SESSION['IdMember'] . ",
    Lenght='" . $LenghtComments . "',
    Quality='" . $vars['Quality'] . "',
    TextWhere='" . $this->dao->escape($vars['TextWhere']) . "',
    TextFree='" . $this->dao->escape($TCom->TextFree . $textfree_add) . "'
WHERE
    id=" . $TCom->id;
            $qry = $this->dao->exec($str);
            if(!$qry) $return = false;
        }
        if ($return != false) {
            // Create a note (member-notification) for this action
            $c_add = ($vars['Quality'] == "Bad") ? '_bad' : '';
            $note = array('IdMember' => $vars['IdMember'], 'IdRelMember' => $_SESSION['IdMember'], 'Type' => 'profile_comment'.$c_add, 'Link' => 'members/'.$vars['IdMember'].'/comments','WordCode' => 'Notify_profile_comment');
            $noteEntity = $this->createEntity('Note');
            $noteEntity->createNote($note);
        }
        return $return;
        
    }
    
    public function addRelation(&$vars)
    {
        $return = true;
        $words = new MOD_words();
        $TData= $this->singleLookup("select * from specialrelations where IdRelation=".$vars["IdRelation"]." and IdOwner=".$_SESSION["IdMember"]);
        
        if (!isset ($TData->id)) {
            $str = "
INSERT INTO
    specialrelations (
        IdOwner,
        IdRelation,
        Type,
        Comment,
        created
    )
    values (
        ".$_SESSION["IdMember"].",
        ".$vars['IdRelation'].",
        '".stripslashes($vars['stype'])."',
        ".$words->InsertInMTrad($this->dao->escape($vars['Comment']),"specialrelations.Comment",0).",
        now()
    )"
    ;
            $qry = $this->dao->query($str);
            if(!$qry) $return = false;
        } else $return = false;
        if ($return != false) {
            // Create a note (member-notification) for this action
            $note = array('IdMember' => $vars['IdRelation'], 'IdRelMember' => $_SESSION['IdMember'], 'Type' => 'relation', 'Link' => 'members/'.$vars['IdOwner'].'/relations/add','WordCode' => 'Notify_relation_new');
            $noteEntity = $this->createEntity('Note');
            $noteEntity->createNote($note);
        }
        return $return;
        
    }
    
    public function updateRelation(&$vars)
    {
        $return = true;
        $words = new MOD_words();
        $TData= $this->singleLookup("select * from specialrelations where IdRelation=".$vars["IdRelation"]." and IdOwner=".$_SESSION["IdMember"]);
        
        if (isset ($TData->id)) {
            $str = "
UPDATE
    specialrelations
SET
    Type = '".stripslashes($vars['stype'])."',
    Comment = ".$words->InsertInMTrad($this->dao->escape($vars['Comment']),"specialrelations.Comment",0)."
WHERE
    IdOwner = ".$_SESSION["IdMember"]." AND
    IdRelation = ".$vars['IdRelation']."
            ";
            $qry = $this->dao->query($str);
            if(!$qry) $return = false;
        } else $return = false;
        if ($return != false) {
            // Create a note (member-notification) for this action
            $note = array('IdMember' => $vars['IdRelation'], 'IdRelMember' => $_SESSION['IdMember'], 'Type' => 'relation', 'Link' => 'members/'.$vars['IdOwner'].'/relations/add','WordCode' => 'Notify_relation_update');
            $noteEntity = $this->createEntity('Note');
            $noteEntity->createNote($note);
        }
        return $return;
        
    }
    
    public function confirmRelation(&$vars)
    {
        $return = true;
        $words = new MOD_words();
        $TData = array();
        $TData[1]= $this->singleLookup("select * from specialrelations where IdOwner=".$vars['IdOwner']." AND IdRelation=".$vars['IdRelation']);
        $TData[2]= $this->singleLookup("select * from specialrelations where IdOwner=".$vars['IdRelation']." AND IdRelation=".$vars['IdOwner']);
        if (isset($TData) && count($TData[1]) > 0 && count($TData[2]) > 0 && isset($vars['confirm'])) {
            foreach ($TData as $rel) {
                $IdOwner = $rel->IdOwner;
                $IdRelation = $rel->IdRelation;
                $str = "
UPDATE
    specialrelations
SET
    Confirmed = '".$vars['confirm']."'
WHERE
    IdOwner = ".$IdOwner." AND
    IdRelation = ".$IdRelation."
                ";
                $qry = $this->dao->query($str);
                if(!$qry) $return = false;
                if ($return != false) {
                    // Create a note (member-notification) for this action
                    $note = array('IdMember' => $IdRelation, 'IdRelMember' => $IdOwner, 'Type' => 'relation', 'Link' => 'members/'.$IdOwner.'/relations/add','WordCode' => 'Notify_relation_confirm_'.$vars['confirm']);
                    $noteEntity = $this->createEntity('Note');
                    $noteEntity->createNote($note);
                }
            }
        } else $return = false;
        return $return;
    }    
	
    public function deleteRelation(&$vars)
    {
        $return = false;
        $words = new MOD_words();
        $TData = array();
        $TData[1]= $this->singleLookup("select * from specialrelations where IdOwner=".$vars['IdOwner']." AND IdRelation=".$vars['IdRelation']);
        $TData[2]= $this->singleLookup("select * from specialrelations where IdOwner=".$vars['IdRelation']." AND IdRelation=".$vars['IdOwner']);
        if (isset($TData) && isset($TData[1]->IdOwner) && count($TData[1]) > 0 && count($TData[2]) > 0 && isset($vars['confirm'])) {
            foreach ($TData as $rel) {
                $IdOwner = $rel->IdOwner;
                $IdRelation = $rel->IdRelation;
                $str = "
DELETE FROM
    specialrelations
WHERE
    IdOwner = ".$IdOwner." AND
    IdRelation = ".$IdRelation."
                ";
                $qry = $this->dao->query($str);
                if(!$qry) $return = false;
                if ($return != false) {
                    // Create a note (member-notification) for this action
                    $note = array('IdMember' => $IdRelation, 'IdRelMember' => $IdOwner, 'Type' => 'relation', 'Link' => 'members/'.$IdRelation.'/relations/','WordCode' => 'Notify_relation_delete');
                    $noteEntity = $this->createEntity('Note');
                    $noteEntity->createNote($note);
                }
            }
        } else $return = false;
        return $return;
    }
	
    /**
     * Check form values of Mandatory form,
     * should always be analog to /build/signup/signup.model.php !!
     *
     * @param unknown_type $vars
     * @return unknown
     */
    public function checkUpdateMandatoryForm(&$vars)
    {
        $errors = array();

        // geonameid
        if (empty($vars['geonameid']) || empty($vars['countryname'])) {
            $errors[] = 'SignupErrorProvideLocation';
            unset($vars['geonameid']);
        }
            
        // housenumber
        if (!isset($vars['housenumber']) || 
            !preg_match(self::HANDLE_PREGEXP_HOUSENUMBER, $vars['housenumber'])) {
            $errors[] = 'SignupErrorProvideHouseNumber';
        }
        
        // street
        if (empty($vars['street']) || 
            !preg_match(self::HANDLE_PREGEXP_STREET, $vars['street'])) {
            $errors[] = 'SignupErrorProvideStreetName';
        }
        
        // zip
        if (!isset($vars['zip'])) {
            $errors[] = 'SignupErrorProvideZip';
        }
        
        // username
        if (!isset($vars['username']) || 
                !preg_match(self::HANDLE_PREGEXP, $vars['username']) ||
                strpos($vars['username'], 'xn--') !== false) {
            $errors[] = 'SignupErrorWrongUsername';
        } elseif (MOD_member::getMember_username($vars['username']) != 0) {
            $errors[] = 'SignupErrorUsernameAlreadyTaken';
        }
        
        // email (e-mail duplicates in BW database allowed)
        if (!isset($vars['email']) || !PFunctions::isEmailAddress($vars['email'])) {
            $errors[] = 'SignupErrorInvalidEmail';
        }
        
        // password
        if (!isset($vars['password']) || !isset($vars['passwordcheck']) ||
                strlen($vars['password']) < 6 || 
                strcmp($vars['password'], $vars['passwordcheck']) != 0
        ) {
            $errors[] = 'SignupErrorPasswordCheck';
        }
        
        // firstname, lastname
        if (empty($vars['firstname']) || !preg_match(self::HANDLE_PREGEXP_FIRSTNAME, $vars['firstname']) ||
            empty($vars['lastname']) || !preg_match(self::HANDLE_PREGEXP_LASTNAME, $vars['lastname'])
        ) {
            $errors[] = 'SignupErrorFullNameRequired';
        }
             
        // (skipped:) secondname

        // gender
        if (empty($vars['gender']) || ($vars['gender']!='female' && $vars['gender']!='male')) {
            $errors[] = 'SignupErrorProvideGender';
        }
        
        // birthyear
        $birthmonth = 12;
        if (!empty($vars['birthmonth'])) {
            $birthmonth = $vars['birthmonth'];
        }
        $birthday = 28;    // TODO: could sometimes be 29, 30, 31
        if (!empty($vars['birthday'])) {
            $birthday = $vars['birthday'];
        }
        if (empty($vars['birthyear']) || !checkdate($birthmonth, $birthday, $vars['birthyear'])) {
            $errors[] = 'SignupErrorBirthDate';
        } else {
            $vars['iso_date'] =  $vars['birthyear'] . "-" . $birthmonth . "-" . $birthday;
            if ($this->ageValue($vars['iso_date']) < self::YOUNGEST_MEMBER) {
                $errors[] = 'SignupErrorBirthDateToLow';
            }
        }
        
        // (skipped:) birthmonth

        // (skipped:) birthday

        // (skipped:) age hidden
        
        return $errors;
    }

    /**
     * Check form values of MyPreferences form,
     *
     * @param unknown_type $vars
     * @return unknown
     */
    public function checkMyPreferences(&$vars)
    {
        $errors = array();
        $log = MOD_log::get();

        // Password Check
        if (isset($vars['passwordnew']) && $vars['passwordnew'] != '') {
            $query = "select id from members where id=" . $_SESSION["IdMember"] . " and PassWord=PASSWORD('" . trim($vars['passwordold']) . "')";
            $qry = $this->dao->query($query);
            $rr = $qry->fetch(PDB::FETCH_OBJ);
            if (!$rr || !array_key_exists('id', $rr))
                $errors[] = 'ChangePasswordInvalidPasswordError';
            if( isset($vars['passwordnew']) && strlen($vars['passwordnew']) > 0) {
                if( strlen($vars['passwordnew']) < 6) {
                    $errors[] = 'ChangePasswordPasswordLengthError';
                }
                if(isset($vars['passwordconfirm'])) {
                    if(strlen(trim($vars['passwordconfirm'])) == 0) {
                        $errors[] = 'ChangePasswordConfirmPasswordError';
                    } elseif(trim($vars['passwordnew']) != trim($vars['passwordconfirm'])) {
                        $errors[] = 'ChangePasswordMatchError';
                    }
                }
            }
        }
        
        // Languages Check
        if (isset($vars['PreferenceLanguage'])) {
            $squery = "
SELECT
    id,
    Name
FROM
    languages
ORDER BY
    Name" ;
            $qry = $this->dao->query($squery);
            $langok = false;
            while ($rp = $qry->fetch(PDB::FETCH_OBJ)) {
              $rp->id;
              if ($vars['PreferenceLanguage'] == $rp->id)
                  $langok = true;
            }
            if ($langok == false) {
                $errors[] = 'PreferenceLanguageError'; 
            }
        }
        
        // email (e-mail duplicates in BW database allowed)
        // if (!isset($vars['Email']) || !PFunctions::isEmailAddress($vars['Email'])) {
            // $errors[] = 'SignupErrorInvalidEmail';
            // $log->write("Editmyprofile: Invalid Email update with value " .$vars['Email'], "Email Update");
        // }
        
        return $errors;
    }

    /**
     * Edit a members preferences, one at a time
     * 
     */
    public function editPreferences(&$vars)
    {
        // set other preferences
        $query = "select * from preferences";
        $rr = $this->bulkLookup($query);
        foreach ($rr as $rWhile) { // browse all preference
            if (isset($vars[$rWhile->codeName]) && $vars[$rWhile->codeName] != '')
                $result = $this->set_preference($vars['memberid'], $rWhile->id, $vars[$rWhile->codeName]);
        }
    }

    /**
     * Check form values of Mandatory form,
     * should always be analog to /build/signup/signup.model.php !!
     *
     * @param unknown_type $vars
     * @return unknown
     */
    public function checkProfileForm(&$vars)
    {
        $errors = array();
        $log = MOD_log::get();
        
        // email (e-mail duplicates in BW database allowed)
        if (!isset($vars['Email']) || !PFunctions::isEmailAddress($vars['Email'])) {
            $errors[] = 'SignupErrorInvalidEmail';
            $log->write("Editmyprofile: Invalid Email update with value " .$vars['Email'], "Email Update");
        }

        // (skipped:) birthday

        // (skipped:) age hidden
        return $errors;
    }
    

    /**
     * Update Member's Profile
     *
     * @param unknown_type $vars
     * @return unknown
     */
    public function updateProfile(&$vars)
    {
        $IdMember = (int)$vars['memberid'];
        $words = new MOD_words();
        $rights = new MOD_right();
        $log = MOD_log::get();
        $m = $vars['member'];
        $CanTranslate = false;
        // $CanTranslate = CanTranslate($vars["memberid"], $_SESSION['IdMember']);
        $ReadCrypted = "AdminReadCrypted"; // This might be changed in the future
        if ($rights->hasRight('Admin') /* or $CanTranslate */) { // admin or CanTranslate can alter other profiles 
            $ReadCrypted = "AdminReadCrypted"; // In this case the AdminReadCrypted will be used
        }
        foreach ($vars['languages_selected'] as $lang) {
            $this->set_language_spoken($lang->IdLanguage,$lang->Level,$IdMember);
        }
        
        // Set the language that ReplaceinMTrad uses for writing
        $words->setlangWrite($vars['profile_language']);

        // Mostly copied from old BW editmyprofile.php:        
        // $str = "HideGender='" . $vars['HideGender'] . "'";
        $str = ",ProfileSummary=" . $words->ReplaceInMTrad($this->cleanupText($vars['ProfileSummary']),"members.ProfileSummary", $IdMember, $m->ProfileSummary, $IdMember);
        $str .= ",WebSite='" . $vars['WebSite'] . "'";
        $str .= ",Accomodation='" . $vars['Accomodation'] . "'";
        $str .= ",Organizations=" . $words->ReplaceInMTrad($vars['Organizations'],"members.Organizations", $IdMember, $m->Organizations, $IdMember);
        $str .= ",Occupation=" . $words->ReplaceInMTrad($vars['Occupation'],"members.Occupation", $IdMember, $m->Occupation, $IdMember);
        $str .= ",ILiveWith=" . $words->ReplaceInMTrad($vars['ILiveWith'],"members.ILiveWith", $IdMember, $m->ILiveWith, $IdMember);
        $str .= ",MaxGuest=" . $vars['MaxGuest'];
        $str .= ",MaxLenghtOfStay=" . $words->ReplaceInMTrad($vars['MaxLenghtOfStay'],"members.MaxLenghtOfStay", $IdMember, $m->MaxLenghtOfStay, $IdMember);
        $str .= ",AdditionalAccomodationInfo=" . $words->ReplaceInMTrad($vars['AdditionalAccomodationInfo'],"members.AdditionalAccomodationInfo", $IdMember, $m->AdditionalAccomodationInfo, $IdMember);
        $str .= ",TypicOffer='" . $vars['TypicOffer'] . "'";
        $str .= ",Restrictions='" . $vars['Restrictions'] . "'";
        $str .= ",OtherRestrictions=" . $words->ReplaceInMTrad($vars['OtherRestrictions'],"members.OtherRestrictions", $IdMember, $m->OtherRestrictions, $IdMember);
        $str .= ",Hobbies=" . $words->ReplaceInMTrad($vars['Hobbies'],"members.Hobbies", $IdMember, $m->Hobbies, $IdMember);
        $str .= ",Books=" . $words->ReplaceInMTrad($vars['Books'],"members.Books", $IdMember, $m->Books, $IdMember);
        $str .= ",Music=" . $words->ReplaceInMTrad($vars['Music'],"members.Music", $IdMember, $m->Music, $IdMember);
        $str .= ",Movies=" . $words->ReplaceInMTrad($vars['Movies'],"members.Movies", $IdMember, $m->Movies, $IdMember);
        $str .= ",PastTrips=" . $words->ReplaceInMTrad($vars['PastTrips'],"members.PastTrips", $IdMember, $m->PastTrips, $IdMember);
        $str .= ",PlannedTrips=" . $words->ReplaceInMTrad($vars['PlannedTrips'],"members.PlannedTrips", $IdMember, $m->PlannedTrips, $IdMember);
        $str .= ",PleaseBring=" . $words->ReplaceInMTrad($vars['PleaseBring'],"members.PleaseBring", $IdMember, $m->PleaseBring, $IdMember);
        $str .= ",OfferGuests=" . $words->ReplaceInMTrad($vars['OfferGuests'],"members.OfferGuests", $IdMember, $m->OfferGuests, $IdMember);
        $str .= ",OfferHosts=" . $words->ReplaceInMTrad($vars['OfferHosts'],"members.OfferHosts", $IdMember, $m->OfferHosts, $IdMember);
        $str .= ",PublicTransport=" . $words->ReplaceInMTrad($vars['PublicTransport'],"members.PublicTransport", $IdMember, $m->PublicTransport, $IdMember);
        
        if (!$CanTranslate) { // a volunteer translator will not be allowed to update crypted data        
            $str .= ",Email='" . MOD_crypt::NewReplaceInCrypted($vars['Email'],"members.Email",$IdMember, $m->Email, $IdMember, $this->ShallICrypt($vars,"Email"));
            $str .= "',HomePhoneNumber='" . MOD_crypt::NewReplaceInCrypted($vars['HomePhoneNumber'],"members.HomePhoneNumber",$IdMember, $m->HomePhoneNumber, $IdMember, $this->ShallICrypt($vars,"HomePhoneNumber"));
            $str .= "',CellPhoneNumber='" . MOD_crypt::NewReplaceInCrypted($vars['CellPhoneNumber'],"members.CellPhoneNumber",$IdMember, $m->CellPhoneNumber, $IdMember, $this->ShallICrypt($vars,"CellPhoneNumber"));
            $str .= "',WorkPhoneNumber='" . MOD_crypt::NewReplaceInCrypted($vars['WorkPhoneNumber'],"members.WorkPhoneNumber",$IdMember, $m->WorkPhoneNumber, $IdMember, $this->ShallICrypt($vars,"WorkPhoneNumber"));
            $str .= "',chat_SKYPE='" . MOD_crypt::NewReplaceInCrypted($vars['chat_SKYPE'],"members.chat_SKYPE",$IdMember, $m->chat_SKYPE, $IdMember, $this->ShallICrypt($vars,"chat_SKYPE"));
            $str .= "',chat_MSN='" . MOD_crypt::NewReplaceInCrypted($vars['chat_MSN'],"members.chat_MSN",$IdMember, $m->chat_MSN, $IdMember, $this->ShallICrypt($vars,"chat_MSN"));
            $str .= "',chat_AOL='" . MOD_crypt::NewReplaceInCrypted($vars['chat_AOL'],"members.chat_AOL",$IdMember, $m->chat_AOL, $IdMember, $this->ShallICrypt($vars,"chat_AOL"));
            $str .= "',chat_YAHOO='" . MOD_crypt::NewReplaceInCrypted($vars['chat_YAHOO'],"members.chat_YAHOO",$IdMember, $m->chat_YAHOO, $IdMember, $this->ShallICrypt($vars,"chat_YAHOO"));
            $str .= "',chat_ICQ='" . MOD_crypt::NewReplaceInCrypted($vars['chat_ICQ'],"members.chat_ICQ",$IdMember, $m->chat_ICQ, $IdMember, $this->ShallICrypt($vars,"chat_ICQ"));
            $str .= "',chat_Others='" . MOD_crypt::NewReplaceInCrypted($vars['chat_Others'],"members.chat_Others",$IdMember, $m->chat_Others, $IdMember, $this->ShallICrypt($vars,"chat_Others"));
            $str .= "',chat_GOOGLE='" . MOD_crypt::NewReplaceInCrypted($vars['chat_GOOGLE'],"members.chat_GOOGLE",$IdMember,$m->chat_GOOGLE, $IdMember, $this->ShallICrypt($vars,"chat_GOOGLE"));        
            $str .= "'";        
        }

// Endcopy
        
        $query = '
UPDATE `members`
SET
    `HideBirthDate` = \'' . $vars['HideBirthDate'] . '\'';

$query .= $str;
$query .= '
WHERE
    `id` = \'' . $IdMember . '\'
';
        $status = $this->dao->query($query);
        
        if (!$CanTranslate) { // a volunteer translator will not be allowed to update crypted data        
            // Only update hide/unhide for identity fields
            MOD_crypt::NewReplaceInCrypted(addslashes(MOD_crypt::$ReadCrypted($m->FirstName)),"members.FirstName",$IdMember, $m->FirstName, $IdMember, $this->ShallICrypt($vars, "FirstName"));
            MOD_crypt::NewReplaceInCrypted(addslashes(MOD_crypt::$ReadCrypted($m->SecondName)),"members.SecondName",$IdMember, $m->SecondName, $IdMember, $this->ShallICrypt($vars, "SecondName"));
            MOD_crypt::NewReplaceInCrypted(addslashes(MOD_crypt::$ReadCrypted($m->LastName)),"members.LastName",$IdMember, $m->LastName, $IdMember, $this->ShallICrypt($vars, "LastName"));
            
            //MOD_crypt::NewReplaceInCrypted(addslashes($m->Zip),"addresses.Zip",$rAdresse->IdAddress,$m->Zip,$IdMember,$this->ShallICrypt($vars, "Zip"));
            //MOD_crypt::NewReplaceInCrypted(addslashes($m->HouseNumber),"addresses.HouseNumber",$m->IdAddress,$rAdresse->HouseNumber,$IdMember,$this->ShallICrypt($vars, "Address"));
            //MOD_crypt::NewReplaceInCrypted(addslashes($m->StreetName),"addresses.StreetName",$m->IdAddress,$rAdresse->StreetName,$IdMember,$this->ShallICrypt($vars, "Address"));


            // if email has changed
            if ($vars["Email"] != $m->email) {
                $log->write("Email updated (previous was " . $m->email . ")", "Email Update");
            }                
        }
        
        // ********************************************************************
        // address/addresses
        // ********************************************************************
        // $query = '
// INSERT INTO addresses
// (
    // `IdMember`,
    // `IdCity`,
    // `HouseNumber`,
    // `StreetName`,
    // `Zip`,
    // `created`,
    // `Explanation`
// )
// VALUES
// (
    // ' . $memberID . ',
    // ' . $vars['geonameid'] . ',
    // 0,
    // 0,
    // 0,
    // now(),
    // "Signup addresse")';
        // $s = $this->dao->query($query);
        // if( !$s->insertId()) {
            // $vars['errors'] = array('inserror');
            // return false;
        // }
        // $IdAddress = $s->insertId();
        // $cryptedfieldsHousenumber = MOD_crypt::insertCrypted($vars['housenumber'], "addresses.HouseNumber", $IdAddress, $memberID);
        // $cryptedfieldsStreet = MOD_crypt::insertCrypted($vars['street'], "addresses.StreetName", $IdAddress, $memberID);
        // $cryptedfieldsZip = MOD_crypt::insertCrypted($vars['zip'], "addresses.Zip", $IdAddress, $memberID);
        // $query = '
// UPDATE addresses
// SET
    // `HouseNumber` = ' . $cryptedfieldsHousenumber . ',
    // `StreetName` = ' . $cryptedfieldsStreet . ',
    // `Zip` = ' . $cryptedfieldsZip . '
// WHERE `id` = ' . $IdAddress . '
        // ';
        // $s = $this->dao->query($query);
        // if( !$s->insertId()) {
            // $vars['errors'] = array('inserror');
            // return false;
        // }

        // MOD_log::get()->writeIdMember($memberID,"member  <b>".$vars['username']."</b> is signuping with success in city [".$CityName."]  using language (".$_SESSION["lang"]." IdMember=#".$memberID." (With New Signup !)","Signup");

        if (!empty($_FILES['profile_picture']) && !empty($_FILES['profile_picture']['tmp_name']))
        {
            if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] == 0)
                $this->avatarMake($vars['memberid'],$_FILES['profile_picture']['tmp_name']);
        }
        
        return $status;
    }
    
    public function polishProfileFormValues($vars)
    {
        $m = $vars['member'];
        
        // Prepare $vars
        $vars['ProfileSummary'] = $this->dao->escape($vars['ProfileSummary']);
        // $vars['BirthDate'] = $member->BirthDate;
        if (!isset($vars['HideBirthDate'])) $vars['HideBirthDate'] = 'No';
        // $vars['Occupation'] = ($member->Occupation > 0) ? $member->get_trad('ProfileOccupation', $profile_language) : '';
        
        // update $vars for $languages
        if(!isset($vars['languages_selected'])) { 
            $vars['languages_selected'] = array();
        }
        $ii = 0;
        $ii2 = 0;
        $lang_used = array();
        foreach($vars['memberslanguages'] as $lang) {
            if (ctype_digit($lang) and !in_array($lang,$lang_used)) { // check $lang is numeric, hence a legal IdLanguage
                $vars['languages_selected'][$ii]->IdLanguage = $lang;
                $vars['languages_selected'][$ii]->Level = $vars['memberslanguageslevel'][$ii2];
                array_push($lang_used, $vars['languages_selected'][$ii]->IdLanguage);
                $ii++;
            }
            $ii2++;
        }
        
        if (!isset($vars['IsHidden_FirstName'])) $vars['IsHidden_FirstName'] = 'No';
        if (!isset($vars['IsHidden_SecondName'])) $vars['IsHidden_SecondName'] = 'No';
        if (!isset($vars['IsHidden_LastName'])) $vars['IsHidden_LastName'] = 'No';
        if (!isset($vars['IsHidden_Address'])) $vars['IsHidden_Address'] = 'No';
        if (!isset($vars['IsHidden_Zip'])) $vars['IsHidden_Zip'] = 'No';
        if (!isset($vars['IsHidden_HomePhoneNumber'])) $vars['IsHidden_HomePhoneNumber'] = 'No';
        if (!isset($vars['IsHidden_CellPhoneNumber'])) $vars['IsHidden_CellPhoneNumber']  = 'No';
        if (!isset($vars['IsHidden_WorkPhoneNumber'])) $vars['IsHidden_WorkPhoneNumber'] = 'No';
        
        // $vars['Email'] = $member->email;
        // $vars['WebSite'] = $member->WebSite;
        
        // $vars['messengers'] = $member->messengers();
        $vars['Accomodation'] = $this->dao->escape($vars['Accomodation']);
        $vars['MaxLenghtOfStay'] = $this->dao->escape($vars['MaxLenghtOfStay']);
        $vars['ILiveWith'] = $this->dao->escape($vars['ILiveWith']);
        $vars['OfferGuests'] = $this->dao->escape($vars['OfferGuests']);
        $vars['OfferHosts'] = $this->dao->escape($vars['OfferHosts']);
        
        // Analyse TypicOffer list
        $TypicOffer = $m->TabTypicOffer;
        $max = count($TypicOffer);
        $vars['TypicOffer'] = "";
        for ($ii = 0; $ii < $max; $ii++) {
            if (isset($vars["check_" . $TypicOffer[$ii]]) && $vars["check_" . $TypicOffer[$ii]] == "on") {
                if ($vars['TypicOffer'] != "")
                    $vars['TypicOffer'] .= ",";
                $vars['TypicOffer'] .= $TypicOffer[$ii];
            }
        } // end of for $ii
        
        // Analyse Restrictions list
        $TabRestrictions = $m->TabRestrictions;
        $max = count($TabRestrictions);
        $vars['Restrictions'] = "";
        for ($ii = 0; $ii < $max; $ii++) {
            if (isset($vars["check_" . $TabRestrictions[$ii]]) && $vars["check_" . $TabRestrictions[$ii]] == "on") {
                if ($vars['Restrictions'] != "")
                    $vars['Restrictions'] .= ",";
                $vars['Restrictions'] .= $TabRestrictions[$ii];
            }
        } // end of for $ii
            
        $vars['PublicTransport'] = $this->dao->escape($vars['PublicTransport']);
        $vars['Restrictions'] = $this->dao->escape($vars['Restrictions']);
        $vars['OtherRestrictions'] = $this->dao->escape($vars['OtherRestrictions']);
        $vars['AdditionalAccomodationInfo'] = $this->dao->escape($vars['AdditionalAccomodationInfo']);
        $vars['OfferHosts'] = $this->dao->escape($vars['OfferHosts']);
        $vars['OfferGuests'] = $this->dao->escape($vars['OfferGuests']);
        $vars['Hobbies'] = $this->dao->escape($vars['Hobbies']);
        $vars['Books'] = $this->dao->escape($vars['Books']);
        $vars['Music'] = $this->dao->escape($vars['Music']);
        $vars['Movies'] = $this->dao->escape($vars['Movies']);
        $vars['Organizations'] = $this->dao->escape($vars['Organizations']);
        $vars['PastTrips'] = $this->dao->escape($vars['PastTrips']);
        $vars['PlannedTrips'] = $this->dao->escape($vars['PlannedTrips']);

        return $vars;
    }
    
    // Return the crypting criteria according of IsHidden_* field of a checkbox
    protected function ShallICrypt($vars, $ss) {
        if (isset($vars["IsHidden_" . $ss]) and $vars["IsHidden_" . $ss] == "Yes")
            return ("crypted");
        else
            return ("not crypted");
    } // end of ShallICrypt
        
    /**
     * Shows a members picture in different sizes
     *
     */
    public function showAvatar($memberId = false)
    {
        $file = (int)$memberId;
        if (isset($_GET)) {
            if (isset($_GET['xs']) or isset($_GET['50_50']))
                $suffix = '_xs';
            elseif (isset($_GET['30_30']))
                $suffix = '_30_30';
            else $suffix = '';
            $file .= $suffix;
        }

        if (!$this->hasAvatar($memberId)) {
            header('Content-type: image/png');
            @copy(HTDOCS_BASE.'images/misc/empty_avatar'.(isset($suffix) ? $suffix : '').'.png', 'php://output');
            PPHP::PExit();
        }
        $img = new MOD_images_Image($this->avatarDir->dirName().'/'.$file);
        if (!$img->isImage()) {
            header('Content-type: image/png');
            @copy(HTDOCS_BASE.'images/misc/empty_avatar'.(isset($suffix) ? $suffix : '').'.png', 'php://output');
            PPHP::PExit();
        }
        $size = $img->getImageSize();
        header('Content-type: '.image_type_to_mime_type($size[2]));
        $this->avatarDir->readFile($file);
        PPHP::PExit();
    }
        
    public function hasAvatar($memberid)
    {
        if ($this->avatarDir->fileExists((int)$memberid))
            return true;
        $img_path = $this->getOldPicture($memberid);
        $this->avatarMake($memberid,$img_path);
    }
    
    
    public function getOldPicture($memberid) {
        $s = $this->dao->query('
SELECT 
    `membersphotos`.`FilePath` as FilePath
FROM     
    `members` 
LEFT JOIN 
    `membersphotos` on `membersphotos`.`IdMember`=`members`.`id` 
WHERE 
    `members`.`id`=\'' . $memberid . '\' AND
    `members`.`Status`=\'Active\' 
ORDER BY membersphotos.SortOrder
');
        // look if any of the pics exists
        while ($row = $s->fetch(PDB::FETCH_OBJ)) {
            $path = str_replace("/bw", "", $row->FilePath);
            $full_path = getcwd().'/bw'.$path;
            if (PPHP::os() == 'WIN') {
                $full_path = str_replace("/", "\\", $full_path);
            }
            if(is_file($full_path)) {
                return $full_path;
            }
        }
        return false;       
    }    
        
    public function avatarMake($memberid,$img_file)
    {
        $img = new MOD_images_Image($img_file);
        if( !$img->isImage())
            return false;
        $size = $img->getImageSize();
        $type = $size[2];
        // maybe this should be changed by configuration
        if( $type != IMAGETYPE_GIF && $type != IMAGETYPE_JPEG && $type != IMAGETYPE_PNG)
            return false;
        $max_x = $size[0];
        $max_y = $size[1];
        if( $max_x > 100)
            $max_x = 100;
        // if( $max_y > 100)
            // $max_y = 100;
        $img->createThumb($this->avatarDir->dirName(), $memberid.'_original', $size[0], $size[1], true, 'ratio');
        $img->createThumb($this->avatarDir->dirName(), $memberid, $max_x, $max_y, true, '');
        $img->createThumb($this->avatarDir->dirName(), $memberid.'_xs', 50, 50, true, 'square');
        $img->createThumb($this->avatarDir->dirName(), $memberid.'_30_30', 30, 30, true, 'square');
        return true;
    }

    public function bootstrap()
    {
        $this->avatarDir = new PDataDir('user/avatars');
    }
    
/*
* cleanupText
*
*
*
*/
    private function cleanupText($txt) {
        $str = '<html><head><meta http-equiv="Content-Type" content="text/html; charset=utf-8"/></head><body>'.$txt.'</body></html>'; 
        $doc = DOMDocument::loadHTML($str);
        if ($doc) {
            $sanitize = new PSafeHTML($doc);
            $sanitize->allow('html');
            $sanitize->allow('body');
            $sanitize->allow('p');
            $sanitize->allow('div');
            $sanitize->allow('b');
            $sanitize->allow('i');
            $sanitize->allow('u');
            $sanitize->allow('a');
            $sanitize->allow('em');
            $sanitize->allow('strong');
            $sanitize->allow('hr');
            $sanitize->allow('span');
            $sanitize->allow('ul');
            $sanitize->allow('li');
            $sanitize->allow('font');
            $sanitize->allow('strike');
            $sanitize->allow('br');
            $sanitize->allow('blockquote');
            $sanitize->allow('h1');
            $sanitize->allow('h2');
            $sanitize->allow('h3');
            $sanitize->allow('h4');
            $sanitize->allow('h5');
        
            $sanitize->allowAttribute('color');    
            $sanitize->allowAttribute('bgcolor');            
            $sanitize->allowAttribute('href');
            $sanitize->allowAttribute('style');
            $sanitize->allowAttribute('class');
            $sanitize->allowAttribute('width');
            $sanitize->allowAttribute('height');
            $sanitize->allowAttribute('src');
            $sanitize->allowAttribute('alt');
            $sanitize->allowAttribute('title');
            $sanitize->clean();
            $doc = $sanitize->getDoc();
            $nodes = $doc->x->query('/html/body/node()');
            $ret = '';
            foreach ($nodes as $node) {
                $ret .= $doc->saveXML($node);
            }
            return $ret;
        } else {
            // invalid HTML
            return '';
        }
    } // end of cleanupText
}


?>
