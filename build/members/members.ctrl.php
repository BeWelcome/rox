<?php


class MembersController extends RoxControllerBase
{

    public function __construct()
    {
        parent::__construct();
        $this->model = new MembersModel;
    }

    /**
     * still main function called by router
     *
     * @todo split controller up to use routing, when proper routing is working
     * @param object $args
     * @access public
     * @return object
     */
    public function index($args = false)
    {
        if ($member = $this->model->getLoggedInMember())
        {
            return $this->index_loggedIn($args, $member);
        }
        else
        {
            return $this->index_loggedOut($args);
        }
    }
    
    protected function index_loggedOut($args)
    {
        $request = $args->request;
        
        switch (isset($request[0]) ? $request[0] : false) {
            case 'updatemandatory':
            case 'mypreferences':
            case 'editmyprofile':
            case 'myvisitors':      
            case 'self':
            case 'myself':
            case 'my':
                // you are not supposed to open these pages when not logged in!
                $page = new MembersMustloginPage;
                break;
            case 'members':
            case 'people':
            default:
                if (!isset($request[1]) || empty($request[1])) {
                    // no member specified
                    $page = new MembersMembernotspecifiedPage;
                } else if ($request[1] == 'avatar') {
                    if (!isset($request[2]) || !$member = $this->getMember($request[2]))
                        PPHP::PExit();
                    PRequest::ignoreCurrentRequest();
                    $this->model->showAvatar($member->id);
                    break;
                } else if (!$member = $this->getMember($request[1])) {
                    // did not find such a member
                    $page = new MembersMembernotfoundPage;
                } else if (!$member->publicProfile) {
                    // this profile is not public
                    $page = new MembersMustloginPage;
                } else {
                    // found a member with given id or username. juhu
                    switch (isset($request[2]) ? $request[2] : false) {
                        case 'comments':
                            $page = new CommentsPage();
                            break;
                        case 'profile':
                        case '':
                        case false:
                            $page = new ProfilePage();
                            break;
                        default:
                            $page = new ProfilePage();
                            $this->model->set_profile_language($request[2]);
                            break;
                    }
                    $page->member = $member;
                }
        }
        $page->model = $this->model;
        return $page;
    }
    
    protected function index_loggedIn($args, $member_self)
    {
        $request = $args->request;
        
        $myself = true;
        
        switch (isset($request[0]) ? $request[0] : false) {
            case 'setlocation':
                $page = new SetLocationPage();
                break;
            case 'mypreferences':
                $page = new MyPreferencesPage();
                break;
            case 'editmyprofile':
                $page = new EditMyProfilePage();
                // $member->edit_mode = true;
                if (isset($request[1]))
                    $this->model->set_profile_language($request[1]);
				if (isset($request[2]) && $request[2] == 'delete')
					$page = new DeleteTranslationPage();
                if (in_array('finish',$request))
                    $page->status = "finish";
                break;
            case 'myvisitors':
                $page = new MyVisitorsPage();
                break;
            case 'self':
            case 'myself':
                $page = new ProfilePage;
                break;
            case 'my':
                switch (isset($request[1]) ? $request[1] : false) {
                    case 'preferences':
                        $page = new MyPreferencesPage();
                        break;
                    case 'visitors':
                        $page = new MyVisitorsPage();
                        return;                        
                    case 'messages':
                        $this->redirect("messages/received");
                        return;
                    case 'profile':
                    default:
                        $page = new ProfilePage;
                }
                break;
            case 'people':
            case 'members':
            default:
                if (!isset($request[1])) {
                    // no member specified
                    $page = new MembersMembernotspecifiedPage;
                    $member = false;
                } else if ($request[1] == 'avatar') {
                    if (!isset($request[2]) || !$member = $this->getMember($request[2]))
                        PPHP::PExit();
                    PRequest::ignoreCurrentRequest();
                    $this->model->showAvatar($member->id);
                    break;
                } else if (!$member = $this->getMember($request[1])) {
                    // did not find such a member
                    $page = new MembersMembernotfoundPage;
                } else {
                    // found a member with given id or username
                    $myself = false;
                    if ($member->id == $member_self->id) {
                        // user is watching her own profile
                        $myself = true;
                    }
                    switch (isset($request[2]) ? $request[2] : false) {
						case 'relations':
                            if (!$myself && isset($request[3]) && $request[3] == 'add') {
                                $page = new AddRelationPage();
								if (isset($request[4]) && $request[4] == 'finish') {
									$page->relation_wait = true;
								}
	                        } else {
                                $page = new RelationsPage();
                            }
							break;
                        case 'comments':
                            if (!$myself && isset($request[3]) && $request[3] == 'add') {
                                $page = new AddCommentPage();
                            } else {
                                $page = new CommentsPage();
                            }
                            break;
                        case 'groups':
                            $my_groups = $member->getGroups();
                            $params->strategy = new HalfPagePager('left');
                            $params->items = $my_groups;
                            $params->items_per_page = 10;
                            $pager = new PagerWidget($params);
                            $page = new MemberGroupsPage();
                            $page->my_groups = $my_groups;
                            $page->pager = $pager;
                            break;
                        case 'redesign':
                            $page = new ProfileRedesignPage();
                            break;
                        case 'profile':
                        case '':
                        case false:
                            $page = new ProfilePage();
                            break;
                        default:
                            $page = new ProfilePage();
                            $this->model->set_profile_language($request[2]);
                            break;
                    }
                }
        }
        if (!isset($member)) {
            $page->member = $member_self;
        } else if (is_object($member)) {
            $page->member = $member;
        }
        if (!empty($myself)) {
            $page->myself = true;
        }
        $page->model = $this->model;
        return $page;
    }
    
    protected function getMember($cid)
    {
        $model = new MembersModel;
        if (is_numeric($cid)) {
            return $model->getMemberWithId($cid);
        } else if (!empty($cid)) {
            return $model->getMemberWithUsername($cid);
        } else {
            return false;
        }
    }
    
    protected function redirect_myprofile()
    {
        if (isset($_SESSION['Username'])) { 
            $username = $_SESSION['Username'];
        } else {
            $username = 'henri';
        }
        $this->redirect("members/$username");
    }
    
    public function setLocationCallback($args, $action, $mem_redirect, $mem_resend)
    {
        $request = $args->request;
        if (isset($args->post)) {
            $mem_redirect->post = $args->post;
            foreach ($args->post as $key => $value) {
                $vars[$key] = $value;
            }
            
            $errors = array();
            // member id
            if (empty($vars['id'])) {
                $errors[] = 'GeoErrorProvideMemberId';
                unset($vars['id']);
            }
            // geonameid
            if (empty($vars['geonameid'])) {
                $errors[] = 'SignupErrorProvideLocation';
                unset($vars['geonameid']);
            }
            
            if (count($errors) > 0) {
                // show form again
                $vars['errors'] = $errors;
                $mem_redirect->post = $vars;
                return false;
            }
            
            // set the location
            $result = $this->model->setLocation($vars['id'],$vars['geonameid']);
            $errors['Geonameid'] = 'Geoname not set';
            if (count($result['errors']) > 0) {
                $mem_redirect->errors = $result['errors'];
            }
            return false;
        }
    }

    public function updateMandatoryCallback($args, $action, $mem_redirect, $mem_resend)
    {
        throw new Exception('This should not be used - mandatory details are taken care of in edit my profile');
        $request = $args->request;
        if (isset($args->post)) {
            foreach ($args->post as $key => $value) {
                $vars[$key] = $value;
            }
            
            $errors = $this->model->checkUpdateMandatory($vars);

            if (count($errors) > 0) {
                // show form again
                $vars['errors'] = $errors;
                $mem_redirect->post = $vars;
                return false;
            }
            $model->polishFormValues($vars);
            $model->setPendingMandatory($vars);
            $model->sendMandatoryForm($vars);
            return 'updatemandatory/finish';
        }
        return false;        
    }
    
    public function myPreferencesCallback($args, $action, $mem_redirect)
    {
        $vars = $args->post;
        $request = $args->request;
        $errors = $this->model->checkMyPreferences($vars);
        
        if (count($errors) > 0) {
            // show form again
            $mem_redirect->problems = $errors;
            $mem_redirect->post = $vars;
            return false;
        }
    
        if( !($User = APP_User::login()))
            return false;
        
        $this->model->editPreferences($vars);
        
        // set profile as public
        if( isset($vars['PreferencePublicProfile']) && $vars['PreferencePublicProfile'] != '') {   
            $this->model->set_public_profile($vars['memberid'],($vars['PreferencePublicProfile'] == 'Yes') ? true : false);
        }
        // set new password
        if( isset($vars['passwordnew']) && strlen($vars['passwordnew']) > 0) {
            $query = 'UPDATE `members` SET `PassWord` = PASSWORD(\''.trim($vars['passwordnew']).'\') WHERE `id` = '.$_SESSION['IdMember'];
            if( $this->model->dao->exec($query)) {
                $messages[] = 'ChangePasswordUpdated';
                $L = MOD_log::get();
                $L->write("Password changed", "change password");
            } else {
                $mem_redirect->problems = array(0 => 'ChangePasswordNotUpdated');
            }
        }
        return false;
    }
    
    /**
     * commentCallback - NOT FINISHED YET !
     *
     * @param Object $args
     * @param Object $action 
     * @param Object $mem_redirect memory for the page after redirect
     * @param Object $mem_resend memory for resending the form
     * @return string relative request for redirect
     */
    public function commentCallback($args, $action, $mem_redirect, $mem_resend)
    {
        $vars = $args->post;
        $request = $args->request;
        $errors = $this->model->checkCommentForm($vars);
        
        if (count($errors) > 0) {
            // show form again
            $mem_redirect->post = $vars;
            return false;
        }
        
        $member = $this->getMember($request[1]);
        $TCom = $member->get_comments_commenter($_SESSION['IdMember']);
        // add the comment!
        if (!$this->model->addComment(isset($TCom[0]) ? $TCom[0] : false,$vars)) return false;
        
        return 'members/'.$request[1].'/comments';
    }
    
    
    /**
     * handles edit profile form post - profile updating
     *
     * @param object $args
     * @param object $action
     * @param object $mem_redirect
     * @param object $mem_resend
     * @access public
     * @return string
     */
    public function editMyProfileCallback($args, $action, $mem_redirect, $mem_resend)
    {
        if (isset($args->post)) {
            $vars = $this->cleanVars($args->post);
            $request = $args->request;
            $errors = $this->model->checkProfileForm($vars);
            $vars['errors'] = array();
            if (count($errors) > 0) {
                // show form again
                $vars['errors'] = $errors;
                $mem_redirect->post = $vars;
                return false;
            }
            $vars['member'] = $this->getMember($vars['memberid']);
            $vars = $this->model->polishProfileFormValues($vars);
            $success = $this->model->updateProfile($vars);
            if (!$success) $mem_redirect->problems = array('Could not update profile');
            
            // Redirect to a nice location like editmyprofile/finish
            $str = implode('/',$request);
            if (in_array('finish',$request)) return $str;
            return $str.'/finish';
        }
    }
	
    public function deleteTranslationCallback($args, $action, $mem_redirect, $mem_resend)
    {
        if (isset($args->post)) {
            $vars = $args->post;
            $request = $args->request;
			if (isset($vars['choice']) && $vars['choice'] == 'yes' && isset($vars['memberid'])) {
				if (!isset($vars['profile_language'])) return false;
				$member = $this->getMember($vars['memberid']);
				$fields = $member->get_trads_fields();
				$trad_ids = array();
				foreach ($fields as $field)
					$trad_ids[] = $member->$field;
				$this->model->delete_translation_multiple($trad_ids,$vars['memberid'],$vars['profile_language']);
				// Redirect to a nice location like editmyprofile/finish
				return 'editmyprofile/finish';
            } else {
				return 'editmyprofile';
			}
        }
    }
	
    public function RelationCallback($args, $action, $mem_redirect, $mem_resend)
    {
        if (isset($args->post)) {
            $vars = $args->post;
            $request = $args->request;

			if (isset($vars['IdOwner']) && $vars['IdOwner'] == $_SESSION['IdMember'] && isset($vars['IdRelation'])) {
				if (isset($vars['action'])) {
					$member = $this->getMember($vars['IdRelation']);
					if (isset($vars['Type'])) $vars['stype'] = $vars['Type'];
					else {
						$TabRelationsType = $member->get_TabRelationsType();
						$stype=""; 
						$tt=$TabRelationsType;
						$max=count($tt);
						for ($ii = 0; $ii < $max; $ii++) {
							if (isset($vars["Type_" . $tt[$ii]]) && $vars["Type_" . $tt[$ii]] == "on") {
							  if ($stype!="") $stype.=",";
							  $stype.=$tt[$ii];
							}
						}
						$relations = $member->get_relations();
						$vars['stype'] = $stype;
					}
					switch ($vars['action']) {
					case 'add':
						$blub = $this->model->addRelation($vars);
						break;
					case 'update':
						$this->model->updateRelation($vars);
						break;
					case 'confirm':
						$vars['confirm'] = 'Yes';
						$blub = $this->model->addRelation($vars);
						$this->model->confirmRelation($vars);
						break;
					default:
					}
				}
				// Redirect to a nice location like editmyprofile/finish
				$str = implode('/',$request);
				if (in_array('finish',$request)) return $str;
				return $str.'/finish';
            }
			return false;
        }
    }

    /**
     * trims all values posted back to controller
     *
     * @param array $post_vars
     * @access private
     * @return array
     */
    private function cleanVars($post_vars)
    {
        $vars = array();
        foreach ($post_vars as $key => $var)
        {
            if (is_string($var))
            {
                $var = trim($var);
            }
            $vars[$key] = $var;
        }
        return $vars;
    }
}

/* htdocs/bw/updatemandatory.php


require_once "lib/init.php";
require_once "lib/FunctionsLogin.php";
require_once "layout/error.php";
require_once "layout/updatemandatory.php";
?>
<?php

if (!IsLoggedIn("Pending,NeedMore")) {
	MustLogIn();
}

// Find parameters
$IdMember = $_SESSION['IdMember'];

if ((HasRight("Accepter")) or ((HasRight("SafetyTeam"))) and (GetStrParam("cid") != "")) { // Accepter or SafetyTeam can alter these data
	$IdMember = IdMember(GetStrParam("cid", $_SESSION['IdMember']));
	$ReadCrypted = "AdminReadCrypted"; // In this case the AdminReadCrypted will be used
	// Restriction an accepter can only see/update mandatory data of someone in his Scope country
	$AccepterScope = RightScope('Accepter');
	$AccepterScope = str_replace("'", "\"", $AccepterScope); // To be sure than nobody used ' instead of " (todo : this test will be to remoev some day)
	if (($AccepterScope != "\"All\"")and($IdMember!=$_SESSION['IdMember'])) {
	   $rr=LoadRow("select IdCountry,countries.Name as CountryName,Username from members,cities,countries where cities.id=members.IdCity and cities.IdCountry=countries.id and members.id=".$IdMember) ;
	   if (isset($rr->IdCountry)) {
	   	  $tt=explode(",",$AccepterScope) ;
		  	if ((!in_array($rr->IdCountry,$tt)) and (!in_array("\"".$rr->CountryName."\"",$tt))) {
					 $ss=$AccepterScope ;
					 for ($ii=0;$ii<sizeof($tt);$ii++) {
					 		 if (is_numeric($tt[$ii])) {
							 		$ss=$ss.",".getcountryname($tt[$ii]) ;
							 }
					 }				 
		  	 	 die ("sorry Your accepter Scope is only for ".$ss." This member is in ".$rr->CountryName) ;
		  	} 
	   }
	}
	$StrLog="Viewing member [<b>".fUsername($IdMember)."</b>] data with right [".$AccepterScope."]" ;
	if (HasRight("SafetyTeam")) {
		 		$StrLog=$StrLog." <b>With SafetyTeam Right</b>" ;
	}
	LogStr($StrLog,"updatemandatory") ; 
	$IsVolunteerAtWork = true;
} else {
	$IsVolunteerAtWork = false;
	$ReadCrypted = "AdminReadCrypted"; // In this case the MemberReadCrypted will be used (only owner can decrypt)
}
$m = LoadRow("select * from members where id=" . $IdMember);

// ************************************************
// RF notes: $m = row from db loaded with member_id
// first, check if we're returning from a post, set
// variables accordingly (typically named as their
// fields)
// ************************************************

if (isset ($_POST['FirstName'])) { // If return from form
	$Username = $m->Username;
	$SecondName = GetStrParam("SecondName");
	$FirstName = GetStrParam("FirstName");
	$LastName = GetStrParam("LastName");
	$StreetName = GetStrParam("StreetName");
	$Zip = GetStrParam("Zip");
	$HouseNumber = GetStrParam("HouseNumber");
	$IdCountry = GetParam("IdCountry");
	$IdCity = GetParam("IdCity",0);
	$IdRegion = GetParam("IdRegion");
	$Gender = GetStrParam("Gender");
	$BirthDate = GetStrParam("BirthDate");
	$MemberStatus = GetStrParam("Status");
	if (GetStrParam("HideBirthDate") == "on") {
		$HideBirthDate = "Yes";
	} else {
		$HideBirthDate = "No";
	}
	if (GetStrParam("HideGender") == "on") {
		$HideGender = "Yes";
	} else {
		$HideGender = "No";
	}
} // end if return from form
else {

// ************************************************
// if not returning from post, fill same variables
// with data from loaded DB row
// ************************************************

	$Username = $m->Username;
	$MemberStatus = $m->Status;
	$FirstName = $ReadCrypted ($m->FirstName);
	$SecondName = $ReadCrypted ($m->SecondName);
	$LastName = $ReadCrypted ($m->LastName);

	$StreetName = "";
	$Zip = "";
	$HouseNumber = "";
	$IdCountry = 0;
	$IdCity = 0;
	$IdRegion = 0;
	$rAdresse = LoadRow("select StreetName,Zip,HouseNumber,countries.id as IdCountry,cities.IdRegion as IdRegion,cities.Name as CityName,cities.id as IdCity from addresses,countries,cities where IdMember=" . $IdMember . " and addresses.IdCity=cities.id  and countries.id=cities.IdCountry");
	if (isset ($rAdresse->IdCity)) {
		$IdCountry = $rAdresse->IdCountry;
		$IdCity = $rAdresse->IdCity;
		$IdRegion = $rAdresse->IdRegion;

		$CityName=$rAdresse->CityName;

		$StreetName = $ReadCrypted ($rAdresse->StreetName);
		$Zip = $ReadCrypted ($rAdresse->Zip);
		$HouseNumber = $ReadCrypted ($rAdresse->HouseNumber);
	}

	$Gender = $m->Gender;
	$HideGender = $m->HideGender;

	$ttdate = explode("-", $m->BirthDate);
	$BirthDate = $ttdate[2] . "-" . $ttdate[1] . "-" . $ttdate[0]; // resort BirthDate

	$HideBirthDate = $m->HideBirthDate;
}

$MessageError = "";

// ************************************************
// where things get ugly - take action based on ...
// probably post variable
// ************************************************

switch (GetParam("action")) {
	case "needmore" : // check parameters
	case "updatemandatory" : // check parameters

// ************************************************
// validate input vars (and load username once more
// just for kicks)
// ************************************************

		$Username = $m->Username; // retrieve Username
			$IdCity = 0;
			$IdRegion = 0;
			$MessageError .= ww('SignupErrorProvideCountry') . "<br />";
		}
		if ($IdCity <= 0) {
			$MessageError .= ww('SignupErrorProvideCity') . "<br />";
		}
		if (strlen($StreetName) <= 1) {
			$MessageError .= ww('SignupErrorProvideStreetName') . "<br />";
		}
		if (strlen($Zip) < 1) {
			$MessageError .= ww('SignupErrorProvideZip') . "<br />";
		}
		if (strlen($HouseNumber) < 1) {
			$MessageError .= ww('SignupErrorProvideHouseNumber') . "<br />";
		}
		if (strlen($Gender) < 1) {
			$MessageError .= ww('SignupErrorProvideGender', ww('IdontSay')) . "<br />";
		}

		$ttdate = explode("-", $BirthDate);
		$DB_BirthDate = $ttdate[2] . "-" . $ttdate[1] . "-" . $ttdate[0]; // resort BirthDate
		if (!checkdate($ttdate[1], $ttdate[0], $ttdate[2])) {
			$MessageError .= ww('SignupErrorBirthDate') . "<br />";
		}
		elseif (fage_value($DB_BirthDate) < $_SYSHCVOL['AgeMinForApplying']) {
			//			  echo "fage_value(",$DB_BirthDate,")=",fage_value($DB_BirthDate),"<br />";
			$MessageError .= ww('SignupErrorBirthDateToLow', $_SYSHCVOL['AgeMinForApplying']) . "<br />";
		}

		if (empty($IdCity)) { // if there was no city return by the form because of some bug
		   if (!empty($rr->IdCity)) $IdCity=$rr->IdCity ; // try with the one of the address if any
		   else {
		   	  $IdCity=$m->IdCity ; // or try with the prévious one
		   }
		}
		if (empty($IdCity)) { 
			$MessageError .= ww('SignupErrorProvideCity') . "<br />";
		}


// ************************************************
// if there was an error validating fields, display
// updatemandatory form once more, along with errors
// and the exit
// ************************************************

		if ($MessageError != "") {
			DisplayUpdateMandatory($Username, $FirstName, $SecondName, $LastName, $IdCountry, $IdRegion, $IdCity, $HouseNumber, $StreetName, $Zip, $Gender, $MessageError, $BirthDate, $HideBirthDate, $HideGender, $MemberStatus,stripslashes(GetStrParam("CityName","")));
			exit (0);
		}


     	$IdAddress=0;
		// in case the update is made by a volunteer
		$rr = LoadRow("select * from addresses where IdMember=" . $m->id." and Rank=0");
		if (isset ($rr->id)) { // if the member already has an address
			$IdAddress=$rr->id;
		}
		if (($IsVolunteerAtWork)or($m->Status=='NeedMore')or($m->Status=='Pending')) {
			// todo store previous values
			if ($IdAddress!=0) { // if the member already has an address
				$str = "update addresses set IdCity=" . $IdCity . ",HouseNumber=" . NewReplaceInCrypted($HouseNumber,"addresses.HouseNumber",$IdAddress,$rr->HouseNumber, $m->id) . ",StreetName=" . NewReplaceInCrypted($StreetName,"addresses.StreetName",$IdAddress, $rr->StreetName, $m->id) . ",Zip=" . NewReplaceInCrypted($Zip,"addresses.Zip",$IdAddress, $rr->Zip, $m->id) . " where id=" . $IdAddress;
				sql_query($str);
			} else {
				$str = "insert into addresses(IdMember,IdCity,HouseNumber,StreetName,Zip,created,Explanation) Values(" . $_SESSION['IdMember'] . "," . $IdCity . "," . NewInsertInCrypted("addresses.HouseNumber",0,$HouseNumber) . "," . NewInsertInCrypted("addresses.StreetNamer",0,$StreetName) . "," . NewInsertInCrypted("addresses.Zip",0,$Zip) . ",now(),\"Address created by volunteer\")";
				sql_query($str);
			    $IdAddress=mysql_insert_id();
				LogStr("Doing a mandatoryupdate on <b>" . $Username . "</b> creating address", "updatemandatory");
			}
			$m->FirstName = NewReplaceInCrypted($FirstName,"members.FirstName",$m->id, $m->FirstName, $m->id,IsCryptedValue($m->FirstName));
			$m->SecondName = NewReplaceInCrypted($SecondName,"members.SecondName",$m->id, $m->SecondName, $m->id,IsCryptedValue($m->SecondName));
			$m->LastName = NewReplaceInCrypted(stripslashes($LastName),"members.LastName",$m->id, $m->LastName, $m->id,IsCryptedValue($m->LastName));

			$str = "update members set FirstName=" . $m->FirstName . ",SecondName=" . $m->SecondName . ",LastName=" . $m->LastName . ",Gender='" . $Gender . "',HideGender='" . $HideGender . "',BirthDate='" . $DB_BirthDate . "',HideBirthDate='" . $HideBirthDate . "',IdCity=" . $IdCity . " where id=" . $m->id;
			sql_query($str);
			$slog = "Doing a mandatoryupdate on <b>" . $Username . "</b>";
			if (($IsVolunteerAtWork) and ($MemberStatus != $m->Status)) {
				$str = "update members set Status='" . $MemberStatus . "' where id=" . $m->id;
				sql_query($str);
				LogStr("Changing Status from " . $m->Status . " to " . $MemberStatus . " for member <b>" . $Username . "</b>", "updatemandatory");
			}
			elseif ($m->Status=='NeedMore') {
				$str = "update members set Status='Pending' where id=" . $m->id;
				sql_query($str);
				$slog=" Completing profile after NeedMore ";
				if (GetStrParam("Comment") != "") {
				   $slog .= "<br /><i>" . stripslashes(GetStrParam("Comment")) . "</i>";
				}
				LogStr($slog, "updatemandatory");
				DisplayUpdateMandatoryDone(ww('UpdateAfterNeedmoreConfirmed', $m->Username));
				exit (0);
			}
			

			if (GetStrParam("Comment") != "") {
				$slog .= "<br /><i>" . stripslashes(GetStrParam("Comment")) . "</i>";
			}
			LogStr($slog, "updatemandatory");
		} else { // not volunteer action


// ************************************************
// in case it's not a volunteer doing the update,
// change it to a request for updating details instead
// by sticking the data into pendingmandatory table
// ************************************************

			$Email = GetEmail();

// a member can only choose to hide or to show his gender / birth date and have it to take action immediately
	  		if (($HideGender!=$m->HideGender) or ($HideBirthDate!=$m->HideBirthDate)) { 
			   $str = "update members set HideGender='" . $HideGender . "',HideBirthDate='" . $HideBirthDate . "' where id=" . $m->id;
			   LogStr("mandatoryupdate changing Hide Gender (".$HideGender."/".$m->HideGender.") or HideBirthDate (".$HideBirthDate."/".$m->HideBirthDate.")", "updatemandatory");
			   sql_query($str);
			}
			
			$str = "insert into pendingmandatory(IdCity,FirstName,SecondName,LastName,HouseNumber,StreetName,Zip,Comment,IdAddress,IdMember) ";
			$str .= " values(" . GetParam("IdCity") . ",'" . GetStrParam("FirstName") . "','" . GetStrParam("SecondName") . "','" . GetStrParam("LastName") . "','" . GetStrParam("HouseNumber") . "','" . GetStrParam("StreetName") . "','" . GetStrParam("Zip") . "','" . GetStrParam("Comment") . "',".$IdAddress.",".$IdMember.")";
			sql_query($str);
			LogStr("Adding a mandatoryupdate request", "updatemandatory");

			$subj = ww("UpdateMantatorySubj", $_SYSHCVOL['SiteName']);
			$text = ww("UpdateMantatoryMailConfirm", $FirstName, $SecondName, $LastName, $_SYSHCVOL['SiteName']);
			$defLanguage = $_SESSION['IdLanguage'];
			bw_mail($Email, $subj, $text, "", $_SYSHCVOL['UpdateMandatorySenderMail'], $defLanguage, "yes", "", "");

			// Notify volunteers that an updater has updated
			$subj = "Update mandatory " . $Username . " from " . getcountryname($IdCountry) . " has updated";
			$text = " updater is " . $FirstName . " " . strtoupper($LastName) . "\n";
			$text .= "using language " . LanguageName($_SESSION['IdLanguage']) . "\n";
			if (GetStrParam("Comment")!="") $text .= "Feedback :<font color=green><b>" . GetStrParam("Comment") . "</font></b>\n";
			else $text .= "No Feedback \n";
			$text .= GetStrParam("ProfileSummary");
			$text .= "<a href=\"https:/".$_SYSHCVOL['MainDir']."admin/adminmandatory.php\">go to update</a>\n";
			bw_mail($_SYSHCVOL['MailToNotifyWhenNewMemberSignup'], $subj, $text, "", $_SYSHCVOL['UpdateMandatorySenderMail'], 0, "html", "", "");
			DisplayUpdateMandatoryDone(ww('UpdateMantatoryConfirm', $Email));
			exit (0);
		}

	case "change_country" :
	case ww('SubmitChooseRegion') :
		DisplayUpdateMandatory($Username, $FirstName, $SecondName, $LastName, $IdCountry, $IdRegion, $IdCity, $HouseNumber, $StreetName, $Zip, $Gender, $MessageError, $BirthDate, $HideBirthDate, $HideGender, $MemberStatus,stripslashes(GetStrParam("CityName","")));
		exit (0);
	case "change_region" :
	case ww('SubmitChooseCity') :
		DisplayUpdateMandatory($Username, $FirstName, $SecondName, $LastName, $IdCountry, $IdRegion, $IdCity, $HouseNumber, $StreetName, $Zip, $Gender, $MessageError, $BirthDate, $HideBirthDate, $HideGender, $MemberStatus,stripslashes(GetStrParam("CityName","")));
		exit (0);
}
DisplayUpdateMandatory($Username, $FirstName, $SecondName, $LastName, $IdCountry, $IdRegion, $IdCity, $HouseNumber, $StreetName, $Zip, $Gender, $MessageError, $BirthDate, $HideBirthDate, $HideGender, $MemberStatus,$CityName);
*/


?>
