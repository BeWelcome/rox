<?php

use Symfony\Component\HttpFoundation\Response;

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

        switch (isset($request[0]) ? $request[0] : false)
        {
            case 'updatemandatory':
            case 'mypreferences':
            case 'editmyprofile':
            case 'myvisitors':
            case 'self':
            case 'myself':
            case 'my':
            case 'deleteprofile':
                // you are not supposed to open these pages when not logged in!
                $page = new MembersMustloginPage;
                break;
            case 'members':
            case 'people':
            default:
                if (!isset($request[1]) || empty($request[1]))
                {
                    // no member specified
                    $this->redirect("places");
                }
                else if ($request[1] == 'avatar')
                {
                    if (!isset($request[2]) || !$member = $this->getMember($request[2]))
                    {
                        PPHP::PExit();
                    }
                    PRequest::ignoreCurrentRequest();
                    $this->model->showAvatar($member->id);
                    break;
                }
                else if (!($member = $this->getMember($request[1])) || !$member->isBrowsable())
                {
                    // did not find such a member
                    $page = new MembersMembernotfoundPage;
                }
                else if (!$member->publicProfile)
                {
                    // this profile is not public
                    $page = new MembersMustloginPage;
                }
                else if ($member->status == 'ChoiceInactive' ) {
                    $page = new InactiveProfilePage();
                } else {
                    // found a member with given id or username. juhu
                    switch (isset($request[2]) ? $request[2] : false)
                    {
                        case 'comments':
                        case 'relations':
                            // not logged in users don't get to see comments page
                            $page = new MembersMustLoginPage;
                            break;
                        case 'groups':
                            $my_groups = $member->getGroups();
                            $params = new StdClass;
                            $params->strategy = new HalfPagePager('left');
                            $params->items = $my_groups;
                            $params->items_per_page = 10;
                            $pager = new PagerWidget($params);
                            $page = new MemberGroupsPage();
                            $page->my_groups = $my_groups;
                            $page->pager = $pager;
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

        $adminMember = false;
        $rights_self = $member_self->getOldRights();
        if (in_array("SafetyTeam", array_keys($rights_self)) || in_array("Admin", array_keys($rights_self))
            || in_array("Profile", array_keys($rights_self)))
        {
            $adminMember = true;
        }

        switch (isset($request[0]) ? $request[0] : false) {
            case 'setlocation':
                $page = new SetLocationPage();
                $geo = new Geo($member_self->IdCity);
                $vars = [];
                if ($geo) {
                    $vars['location'] = $geo->getFullName($_SESSION['lang']);
                } else {
                    $vars['location'] = '';
                }
                $vars['location-geoname-id'] = $member_self->IdCity;
                $vars['location-latitude'] = $member_self->Latitude;
                $vars['location-longitude'] = $member_self->Longitude;
                $page->vars = $vars;
                break;
            case 'mypreferences':
                $page = new MyPreferencesPage();
                break;
            case 'deleteprofile':
                $page = new DeleteProfilePage();
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
                $member = $this->model->getLoggedInMember();
                $showVisits = $member->getPreference(
                    'PreferenceShowProfileVisits', 'Yes');
                if ($showVisits == 'Yes') {
                    $page = new MyVisitorsPage();
                } else {
                    $this->redirect("members/" . $member->Username);
                }
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
            case 'flagcomment':
                if (isset($request[1]) && isset($request[2])) {
                    $username = $request[1];
                    $commentId = $request[2];
                    if (isset($request[3])) {
                        $commentPage = $request[3];
                    } else {
                        $commentPage = $username;
                    }
                    $reportResult = $this->model->reportBadComment($username,
                        $commentId);

                    if ($reportResult) {
                        $member = $this->model->getLoggedInMember();
                        $this->logWrite("{$member->Username} has reported"
                            . " comment ID: {$commentId} on user {$username}"
                            . " as problematic", 'comments');
                        $this->redirect('members/' . $commentPage
                            . '/comments');
                        $notice = $this->getWords()->CommentReported;
                        $this->setFlashNotice($notice);
                    } else {
                        $this->redirect('');
                    }
                } else {
                    $this->redirect('');
                }
                break;
            case 'people':
            case 'members':
            default:
                if (!isset($request[1]))
                {
                    // no member specified
                    $this->redirect("places");
                }
                elseif ($request[1] == 'reportcomment')
                {
                    if (isset($request[2]) && isset($request[3])
                        && $this->model->getLoggedInMember()) {

                        $username = $request[2];
                        $commentId = $request[3];
                        $redirect = 'flagcomment/' . $username . '/'
                            . $commentId;
                        // Use profile the comment was left on if available
                        // (needed to redirect user back to correct page)
                        if (isset($request[4])) {
                            $redirect .= '/' . $request[4];
                        }

                        // Prepare feedback data
                        $baseUri = PVars::getObj('env')->baseuri;
                        $data = array();
                        $data['Admin comment'] = $baseUri
                            . 'bw/admin/admincomments.php?IdComment='
                            . $commentId . '&action=All';
                        $data['Member comment page'] = $baseUri
                            . 'members/' . $username . '/comments';
                        $dataEncoded = urlencode(serialize($data));

                        // Redirect
                        $url = 'feedback?IdCategory=2&redirect='
                            . urlencode($redirect) . '&data=' . $dataEncoded;
                        $this->redirect($url);
                    } else {
                        $this->redirect('');
                    }
                }
                else if ($request[1] == 'avatar')
                {
                    if (!isset($request[2]) || !$member = $this->getMember($request[2]))
                        PPHP::PExit();
                    PRequest::ignoreCurrentRequest();
                    $this->model->showAvatar($member->id);
                    break;
                }
                else if (!($member = $this->getMember($request[1])))
                {
                    // did not find such a member
                    $page = new MembersMembernotfoundPage;
                }
                else
                {
                    //check if member can browse that profile
                    if (!$member->isBrowsable() && !$adminMember){
                        $page = new MembersMembernotfoundPage;
                        break;
                    }

                    // found a member with given id or username
                    $myself = false;
                    if ($member->id == $member_self->id)
                    {
                        // user is watching her own profile
                        $myself = true;
                    }
                    else
                    {
                        if (($logged_member = $this->model->getLoggedInMember()) and $logged_member->isNotActiveHidden())
                        {
                            $member->recordVisit($logged_member);
                        }
                    }
                    switch (isset($request[2]) ? $request[2] : false) {
                        case 'relations':
                            if (isset($request[3])) {
                                if ($request[3] == 'add') {
                                    if (!$myself) {
                                        $page = new AddRelationPage();
                                        if (isset($request[4]) && $request[4] == 'finish') {
                                            $page->relation_wait = true;
                                        }
                                    }
                                } elseif ($request[3] == 'delete') {
                                    // Make sure user is deleting their own relation and that ID is set
                                    if ($myself && isset($request[4])) {
                                        $id = intval($request[4]);
                                        if ($id > 0) {
                                            $deleteResult = $this->model->deleteRelation($id);
                                            if ($deleteResult) {
                                                $this->setFlashNotice($this->getWords()->Relation_deleted);
                                            } else {
                                                $this->setFlashError($this->getWords()->Relation_delete_error);
                                            }
                                        } else {
                                            $this->setFlashError($this->getWords()->Relation_delete_error);
                                        }
                                    }
                                    // Define redirect target
                                    // TODO: if there is a nicer way than using $_GET, please change this
                                    if ($_GET['redirect']) {
                                        $redirect = $_GET['redirect'];
                                    } else {
                                        // Redirect to relations page or homepage
                                        if (isset($_SESSION['Username'])) {
                                            $redirect = 'members/' . $_SESSION['Username'] . '/relations/';
                                        } else {
                                            $redirect = '';
                                        }
                                    }
                                    $this->redirect($redirect);
                                    return;
                                }
                            }

                            // Default relations page
                            if (!isset($page)) {
                                $page = new RelationsPage();
                            }
                            break;
                        case 'comments':
                            if (!$myself && isset($request[3]) && $request[3] == 'adminedit') {
                                $page = new AddCommentPage();
                                $page->adminedit;
                            } elseif (!$myself && isset($request[3]) && ($request[3] == 'add' || $request[3] == 'edit')) {
                                $page = new AddCommentPage();
                                $page->commentGuidelinesRead = $this->model->getCommentGuidelinesRead();
                            } else {
                                $page = new CommentsPage();
                            }
                            break;
                        case 'groups':
                            $my_groups = $member->getGroups();
                            $params = new stdClass();
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
                        case 'adminedit':
                            $rights = new MOD_right();
                            if ($rights->hasRight('Admin') || $rights->hasRight('SafetyTeam'))
                            {
                                $page = new EditMyProfilePage();
                                $page->adminedit = true;
                                $page->statuses = $this->model->getStatuses();
                                // $member->edit_mode = true;
                                if (isset($request[3]) && $request[3] == 'delete')
                                    $page = new DeleteTranslationPage();
                                if (in_array('finish',$request))
                                    $page->status = "finish";
                            }
                            else
                            {
                                $page = new MembersMembernotfoundPage;
                            }
                            break;
                        case 'profile':
                        case '':
                        case false:
                            $hideProfile = !$myself && $member->Status == 'ChoiceInactive' && !$adminMember;
                            if ($hideProfile) {
                                $page = new InactiveProfilePage();
                            } else {
                                $page = new ProfilePage();
                                $page->statuses = $this->model->getStatuses();
                            }

                            break;
                        default:
                            $hideProfile = !$myself && $member->Status == 'ChoiceInactive' && !$adminMember;
                            if ($hideProfile) {
                                $page = new InactiveProfilePage();
                            } else {
                                $page = new ProfilePage();
                                $this->model->set_profile_language($request[2]);
                                $page->statuses = $this->model->getStatuses();
                            }
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
        $page->loggedInMember = $this->model->getLoggedInMember();
        $page->model = $this->model;
        if ($page->member && $page->member->Status == 'PassedAway') {
            $page->passedAway = true;
        } else {
            $page->passedAway = false;
        }
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
            // geonameid
            if (empty($vars['location-geoname-id'])) {
                $errors[] = 'SignupErrorProvideLocation';
                unset($vars['location-geoname-id']);
            }

            if (count($errors) > 0) {
                // show form again
                $vars['errors'] = $errors;
                $mem_redirect->post = $vars;
                return false;
            }

            // set the location
            $result = $this->model->setLocation($vars);
            $errors['Geonameid'] = 'Geoname not set';
            if (count($result['errors']) > 0) {
                $mem_redirect->errors = $result['errors'];
            } else {
                $this->setFlashNotice('Successfully changed your location');
            }
            return false;
        }
    }

    public function updateMandatoryCallback($args, $action, $mem_redirect, $mem_resend)
    {
        throw new Exception('This should not be used - mandatory details are taken care of in edit my profile');
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

        if (isset($vars['PreferenceLanguage']) && $_SESSION['IdLanguage'] != $vars['PreferenceLanguage'])
        {
            $this->model->setSessionLanguage($vars['PreferenceLanguage']);
        }

        // set profile as public
        if( isset($vars['PreferencePublicProfile']) && $vars['PreferencePublicProfile'] != '') {
            $this->model->set_public_profile($vars['memberid'],($vars['PreferencePublicProfile'] == 'Yes') ? true : false);
        }
        // set new password
        if (isset($vars['passwordnew']) && strlen($vars['passwordnew']) > 0) {
            $m = $this->model->getMemberWithId($vars['memberid']);
            if (!$m->setPassword($vars['passwordnew'])){
                $mem_redirect->problems = array(0 => 'ChangePasswordNotUpdated');
            }
            $this->setFlashNotice($this->getWords()->get('PasswordSetFlashNotice'));
        }        
 
        return false;
    }

    public function commentCallback1($args, $action, $mem_redirect, $mem_resend)
    {
        return $this->commentCallback($args, $action, $mem_redirect, $mem_resend, 1);
    }

    public function commentCallback2($args, $action, $mem_redirect, $mem_resend)
    {
        return $this->commentCallback($args, $action, $mem_redirect, $mem_resend, 2);
    }

    public function commentCallback3($args, $action, $mem_redirect, $mem_resend)
    {
        return $this->commentCallback($args, $action, $mem_redirect, $mem_resend, 3);
    }

    /**
     * commentCallback
     *
     * @param Object $args
     * @param Object $action
     * @param Object $mem_redirect memory for the page after redirect
     * @param Object $mem_resend memory for resending the form
     * @return string relative request for redirect
     */
    public function commentCallback($args, $action, $mem_redirect, $mem_resend, $random)
    {
        $vars = $args->post;
        $request = $args->request;
        $errors = $this->model->checkCommentForm($vars, $random); // TODO: checkCommentForm still needs more finetuning

        if (count($errors) > 0) {
            // show form again
            $vars['errors'] = $errors;
            // if the form gets posted it means that it is allowed to edit the comment
            $vars['AllowEdit'] = 1;
            $mem_redirect->post = $vars;
            return false;
        }

        $member = $this->getMember($request[1]);
        $TCom = $member->get_comments_commenter($this->model->getLoggedInMember()->id);
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

            $uploadFailed = false;
			if (in_array('UploadedProfileImageTooBig', $errors) === false
				|| in_array('ProfileImageUploadFailed', $errors) === false
            ) {
            	$uploadFailed = true;
            } else {
				// check if uploaded file is image
				$img = new MOD_images_Image($_FILES['profile_picture']['tmp_name']);
				if (!$img->isImage()) {
					$errors[] = 'ProfileUploadNotImage';
					$uploadFailed = true;
				}
			}

            $vars['errors'] = array();
            if (count($errors) > 0) {
                $vars['errors'] = $errors;

                // Activate fieldset tab "Contact Info" if needed.
                if (in_array('SignupErrorInvalidBirthDate', $vars['errors']) === false
                	&& $uploadFailed === false) {
                    $vars['activeFieldset'] = 'contactinfo';
                }

                // show form again
                $mem_redirect->post = $vars;
                return false;
            }
            $rights = new MOD_right;
            if (!($rights->hasRight('Admin') || $rights->hasRight('SafetyTeam')))
            {
                $vars['memberid'] = $this->model->getLoggedInMember()->getPKValue();
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
     * callback for profile deletion
     *
     * @param stdClass       $args   - all sorts of variables
     * @param ReadOnlyObject $memory - memory related stuff
     * @param stuff          $stuff1
     * @param stuff          $stuff2
     *
     * @access public
     * @return mixed
     */
    public function retireProfile(StdClass $args, $memory, $stuff1, $stuff2)
    {
        if (empty($args->post) || !($member = $this->model->getLoggedInMember()))
        {
            return false;
        }
        $feedback = !empty($args->post['explanation']) ? $args->post['explanation'] : '';
        $member->removeProfile();
        $this->model->sendRetiringFeedback($feedback);
        $member->logOut();
        return $this->router->url('members_profile_retired', array(), false);
    }

    /**
     * displays a page with some text about the profile being retired
     *
     * @access public
     * @return RetiredPage
     */
    public function retired()
    {
        $page = new RetiredProfilePage();
        $page->model = $this->model;
        return $page;
    }

    /**
     * callback to set profile inactive
     *
     * @param stdClass       $args   - all sorts of variables
     * @param ReadOnlyObject $memory - memory related stuff
     * @param stuff          $stuff1
     * @param stuff          $stuff2
     *
     * @access public
     * @return mixed
     */
    public function setProfileInactiveCallback(StdClass $args, $memory, $stuff1, $stuff2)
    {
        if (empty($args->post) || !($member = $this->model->getLoggedInMember()))
        {
            return false;
        }
        // Update database
        $member->inactivateProfile();
        // Update session
        $_SESSION["MemberStatus"] = $_SESSION["Status"] = 'ChoiceInactive';
        $this->setFlashNotice($this->model->getWords()->get('ProfileSetInactiveSuccess'));
        return 'editmyprofile';
    }

    public function setActive() {
        $member = $this->model->getLoggedInMember();
        if (!$member) {
            $page = new MembersMustloginPage;
        } else {
            if ($member->Status != 'ChoiceInactive') {
                $this->redirectAbsolute('editmyprofile');
            } else {
                $page = new SetProfileActivePage();
            }
        }
        $page->member = $member;
        $page->myself = true;
        $page->model = $this->model;
        return $page;
    }

    /**
     * callback to set profile inactive
     *
     * @param stdClass       $args   - all sorts of variables
     * @param ReadOnlyObject $memory - memory related stuff
     * @param stuff          $stuff1
     * @param stuff          $stuff2
     *
     * @access public
     * @return mixed
     */
    public function setProfileActiveCallback(StdClass $args, $memory, $stuff1, $stuff2)
    {
        if (empty($args->post) || !($member = $this->model->getLoggedInMember()))
        {
            return false;
        }
        // Update database
        $member->activateProfile();
        // Update session
        $_SESSION["MemberStatus"] = $_SESSION["Status"] = 'Active';
        $this->setFlashNotice($this->model->getWords()->get('ProfileSetActiveSuccess'));
        return 'editmyprofile';
    }

    public function setInactive() {
        $member = $this->model->getLoggedInMember();
        if (!$member) {
            $page = new MembersMustloginPage;
        } else {
            if ($member->Status != 'Active') {
                $this->redirectAbsolute('editmyprofile');
            } else {
                // set to inactive only allowed in intervals of two weeks time
                $minimumTimeBetweenSwitches = 14 * 24 * 60 * 60;
                $page = new SetProfileInactivePage();
                $timeSinceLastSwitchToActive = time() - strtotime($member->LastSwitchToActive);
                if ($timeSinceLastSwitchToActive < $minimumTimeBetweenSwitches) {
                    $page->switchNotAllowed = true;
                } else {
                    $page->switchNotAllowed = false;
                }
            }
        }
        $page->member = $member;
        $page->myself = true;
        $page->model = $this->model;
        return $page;
    }

    /**
     * callback for password reset
     *
     * @param stdClass       $args   - all sorts of variables
     * @param ReadOnlyObject $memory - memory related stuff
     * @param stuff          $stuff1
     * @param stuff          $stuff2
     *
     * @access public
     * @return mixed
     */
    public function resetPasswordCallback(StdClass $args, ReadOnlyObject $action, ReadWriteObject $mem_redirect, ReadWriteObject $mem_resend)
    {
        $post = $args->post;
        if (empty($post['UsernameOrEmail']))
        {
            $mem_redirect->errors = array('ResetPasswordEmpty');
            return false;
        }
        $member = $this->model->getMemberWithUsername($post['UsernameOrEmail']);
        if (!$member) {
            $member = $this->model->getMemberFromEmail($post['UsernameOrEmail']);
        }
        if (!$member) {
            $mem_redirect->errors = array('ResetPasswordError');
            return false;
        }
        if ($member->canLogIn()) {
            $member->model = $this->model;

            // Generate random password (copied from bw)
            $totalChar = 8; // number of chars in the password
            $salt = "abcdefghijklmnpqrstuvwxyzABCDEFGHIJKLMNPQRSTUVWXYZ123456789";  // salt to select chars from
            srand((double)microtime()*1000000); // start the random generator
            $password=""; // set the inital variable
            for ($i=0;$i<$totalChar;$i++) {  // loop and create password
                $password = $password . substr ($salt, rand() % strlen($salt), 1);
            }

            // Alternate version using md5
            // $password = md5($member->Username . uniqid());
            $member->setPassword($password);
            $subject = $this->getWords()->get("ResetPasswordSubject");
            $body = $this->getWords()->get("ResetPasswordBody", $password, $member->Username);
            $member->sendMail($subject, $body);
            $this->setFlashNotice($this->getWords()->get('ResetPasswordFlashNotice'));
            return $this->router->url('members_reset_password_finish', array(), false);
        } else {
            $mem_redirect->errors = array('ResetPasswordNoLogin');
            return false;
        }
    }

    /**
     * displays the reset your password page
     *
     * @access public
     * @return ResetPasswordPage
     */
    public function resetPassword()
    {
        $page = new ResetPasswordPage();
        $page->model = $this->model;
        return $page;
    }

    /**
     * displays the a list of contacts/notes
     *
     * @access public
     * @return MemberNotesPage
     */
    public function mynotes()
    {
        $member = $this->model->getLoggedInMember();
        if (!$member) {
            return $page = new MembersMustloginPage;
        }
        $mynotes = $member->getNotes();
        $params = new StdClass;
        $params->strategy = new HalfPagePager('left');
        $params->items = $mynotes;
        $params->items_per_page = 20;
        $pager = new PagerWidget($params);
        $page = new MemberNotesPage();
        $page->mynotes = $mynotes;
        $page->pager = $pager;
        $page->model = $this->model;
        $page->member = $member;
        $page->myself = true;
        return $page;
    }

    /**
     * noteCallback
     *
     * @param Object $args
     * @param Object $action
     * @param Object $mem_redirect memory for the page after redirect
     * @param Object $mem_resend memory for resending the form
     * @return string relative request for redirect
     */
    public function addNoteCallback(StdClass $args, ReadOnlyObject $action, ReadWriteObject $mem_redirect, ReadWriteObject $mem_resend)
    {
        $vars = $args->post;
        $category="";
        $catselect = trim($vars['ProfileNoteCategory']);
        $catfree = trim($vars['ProfileNoteCategoryFree']);
        $request = $args->request;
        if (!empty($catselect) && !empty($catfree)) {
            if ($catselect != $catfree) {
                $vars['errors'] = array('ProfileNoteCategoryUnclear');
                $mem_redirect->post = $vars;
                return false;
            }
        }
        if (!empty($catselect)) {
            $category = $catselect;
        } else {
            $category = $catfree;
        }
        if (empty($category)) {
            $vars['errors'] = array('ProfileNoteCategoryNotSet');
            $mem_redirect->post = $vars;
            return false;
        }
        $this->model->writeNoteForMember($this->route_vars['username'], $category, $vars['ProfileNoteComment']);
        $vars['success'] = true;
        $mem_redirect->post = $vars;
        return false;
    }

    /**
     * displays the add or edit note page
     *
     * @access public
     * @return AddNotePagePage
     */
    public function addNote()
    {
        $loggedInMember = $this->model->getLoggedInMember();
        $member =$this->model->getMemberWithUsername($this->route_vars['username']);
        if (!$loggedInMember || !$member) {
            return $page = new MembersMustloginPage;
        }
        $page = new AddNotePage();
        $page->model = $this->model;
        $page->loggedInMember = $loggedInMember;
        $page->member = $member;
        return $page;
    }

    /**
     * noteCallback
     *
     * @param Object $args
     * @param Object $action
     * @param Object $mem_redirect memory for the page after redirect
     * @param Object $mem_resend memory for resending the form
     * @return string relative request for redirect
     */
    public function deleteNoteCallback(StdClass $args, ReadOnlyObject $action, ReadWriteObject $mem_redirect, ReadWriteObject $mem_resend)
    {
        $vars = $args->post;
        $this->model->deleteNoteForMember($vars['IdMember']);
        return $this->router->url('members_show_all_notes', array(), false);
    }

    /**
     * displays the add or edit note page
     *
     * @access public
     * @return AddNotePagePage
     */
    public function deleteNote()
    {
        $loggedInMember = $this->model->getLoggedInMember();
        $member =$this->model->getMemberWithUsername($this->route_vars['username']);
        if (!$loggedInMember || !$member) {
            return $page = new MembersMustloginPage;
        }
        $note = $loggedInMember->getNote($member);
        if (!$note) {
            $baseURL = PVars::getObj('env')->baseuri;
            return $this->redirectAbsolute($baseURL . 'members/' . $this->route_vars['username']);
        }
        $page = new DeleteNotePage();
        $page->model = $this->model;
        $page->loggedInMember = $loggedInMember;
        $page->member = $member;
        return $page;
    }

    /**
     * setStatusCallback
     *
     * @param Object $args
     * @param Object $action
     * @param Object $mem_redirect memory for the page after redirect
     * @param Object $mem_resend memory for resending the form
     * @return string relative request for redirect
     */
    public function setStatusCallback(StdClass $args, ReadOnlyObject $action, ReadWriteObject $mem_redirect,
                      ReadWriteObject $mem_resend)
    {
        $vars = $args->post;
        $success = $this->model->setStatus($vars['member-id'], $vars['new-status']);
        if ($success) {
            $this->setFlashNotice('Changed status');
        }
        return true;
    }

    public function dataRetention()
    {
        if ($_SERVER['REMOTE_ADDR'] !== '127.0.0.1') {
            header("Location: " . PVars::getObj('env')->baseuri);
            exit(0);
        }
        ob_start();
        try {
            $this->model->removeMembers();
        }
        catch(Exception $e) {
            ExceptionLogger::logException($e);
            header("Location: " . PVars::getObj('env')->baseuri);
            exit(0);
        }
        ob_end_clean();
        exit(0);
    }

    /**
     * @return Response
     */
    public function avatar($username, $size) {
        $member = $this->getMember($username);
        ob_start();
        $this->model->showAvatar($member->id, $size);
        return new Response(ob_end_clean());
    }
}
