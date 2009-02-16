<?php


class MembersModel extends RoxModelBase
{
    
    private $profile_language = null;
    
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


    /**
     * set the location of a member
     */
    public function setLocation($IdMember,$geonameid = false)
    {
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
WHERE   IdMember = $IdMember
                "
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
     * Get languages spoken by member
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
    
    

    // checkCommentForm - NOT FINISHED YET !
    public function checkCommentForm(&$vars)
    {
        $errors = array();
        
        
        // sample!
        if (empty($vars['geonameid']) || empty($vars['countryname'])) {
            $errors[] = 'SignupErrorProvideLocation';
        }
        
    }
    
    // checkCommentForm - NOT FINISHED YET !
    public function addComment($TCom,&$vars)
    {
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
        '" . $vars['TextWhere'] . "',
        '" . $vars['TextFree'] . "',
        '" . $AdminAction . "',now()
    )"
    ;
            $qry = $this->dao->query($str);
            if(!$qry) $return = false;
		} else {
			$str = "
UPDATE
    comments
SET 
    AdminAction='" . $AdminAction . "',
    IdToMember=" . $vars['IdMember'] . ",
    IdFromMember=" . $_SESSION['IdMember'] . ",
    Lenght='" . $LenghtComments . "',
    Quality='" . $vars['Quality'] . "',
    TextFree='" . $vars['TextFree'] . "'
WHERE
    id=" . $TCom->id;
			$qry = $this->dao->query($str);
            if(!$qry) $return = false;
		}
        
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
		$str = ",ProfileSummary=" . $words->ReplaceInMTrad($vars['ProfileSummary'],"members.ProfileSummary", $IdMember, $m->ProfileSummary, $IdMember);
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
        // e-mail, names/members
        // ********************************************************************
        // $cryptedfieldsEmail = MOD_crypt::insertCrypted($vars['email'],"members.Email", $memberID, $memberID, "always") ;
        // $cryptedfieldsFirstname =  MOD_crypt::insertCrypted($vars['firstname'],"members.FirstName", $memberID, $memberID) ;
        // $cryptedfieldsSecondname  =  MOD_crypt::insertCrypted($vars['secondname'],"members.SecondName", $memberID, $memberID) ;
        // $cryptedfieldsLastname =  MOD_crypt::insertCrypted($vars['lastname'],"members.LastName", $memberID, $memberID) ;
        // $query = '
// UPDATE
	// `members`
// SET
	// `Email`=' . $cryptedfieldsEmail . ',
	// `FirstName`=' . $cryptedfieldsFirstname . ',
	// `SecondName`=' . $cryptedfieldsSecondname . ',
	// `LastName`=' . $cryptedfieldsLastname . '
// WHERE
	// `id` = ' . $memberID;
        
        // $this->dao->query($query);
        
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

}


?>
