<?php

/**
 * contactlocals controller
 *
 * @package ContactlocalsController
 * @author JeanYves
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License (GPL)
 * @version $Id$
 * The purpose of this package is to allow, according to his right,
 * a local volunteers to contact the members in his scope
 */
require_once("../htdocs/bw/lib/rights.php") ; // Requiring BW right 
// TODO: use the MyTB right.. (MOD_right)
// no, not for now
class ContactlocalController extends RoxControllerBase
{

    public function __construct() {
        parent::__construct();

    }
    /**
     * decide which page to show.
     * This method is called automatically
     */
    public function index($args=false)
    {
        $User = APP_User::login(); // The user must be logged in

        $request = $args->request;
        $model = new ContactlocalsModel;

        
        if (!isset($_SESSION['IdMember'])) {
            $page = new MessagesMustloginPage();
            $page->setRedirectURL(implode('/',$request));
        		return $page;
        } 
//        print_r($args->post);
		if (!MOD_right::get()->HasRight("ContactLocation")) {
			$page = new ContactlocalsPage("","MissRight","ContactLocation");
		}
		else {
			// look at the request.
			switch (isset($request[1]) ? $request[1] : false) {
				case 'listall':
					$page = new ContactlocalsPage("","listall",$model->LoadList(""));
					break;
				case 'addlocation':
					$model->AddLocation($args->post) ;
					$data->IdMess=$model->IdMess ;
					$tt=$model->LoadList($data->IdMess) ;
					$data->localmessage=$tt[0] ;
					$data->PossibleLocations=$model->GetAllowedLocation() ;
					$data->PossibleLanguages=$model->GetMemberLanguages() ;
					$page = new ContactlocalsPage("","preparenewmessage",$data);
					break ;
				case 'updatetranslation':
					$model->UpdateTranslation($args->post) ;
					$data->IdMess=$model->IdMess ;
					$tt=$model->LoadList($data->IdMess) ;
					$data->localmessage=$tt[0] ;
					$data->PossibleLocations=$model->GetAllowedLocation() ;
					$data->PossibleLanguages=$model->GetMemberLanguages() ;
					$page = new ContactlocalsPage("","preparenewmessage",$data);
					break ;
					
				case 'deletetranslation':
					$model->DelTranslation($args->post) ;
					$data->IdMess=$model->IdMess ;
					$tt=$model->LoadList($data->IdMess) ;
					$data->localmessage=$tt[0] ;
					$data->PossibleLocations=$model->GetAllowedLocation() ;
					$data->PossibleLanguages=$model->GetMemberLanguages() ;
					$page = new ContactlocalsPage("","preparenewmessage",$data);
					break ;
					
				case 'addtranslation':
					$model->AddTranslation($args->post) ;
					$data->IdMess=$model->IdMess ;
					$tt=$model->LoadList($data->IdMess) ;
					$data->localmessage=$tt[0] ;
					$data->PossibleLocations=$model->GetAllowedLocation() ;
					$data->PossibleLanguages=$model->GetMemberLanguages() ;
					$page = new ContactlocalsPage("","preparenewmessage",$data);
					break ;
					
					
				case 'dellocation':
					$model->DelLocation($args->post) ;
					$data->IdMess=$model->IdMess ;
					$tt=$model->LoadList($data->IdMess) ;
					$data->localmessage=$tt[0] ;
					$data->PossibleLocations=$model->GetAllowedLocation() ;
					$data->PossibleLanguages=$model->GetMemberLanguages() ;
					$page = new ContactlocalsPage("","preparenewmessage",$data);
					break ;
				case 'preparenewmessage':
					$data->PossibleLocations=$model->GetAllowedLocation() ;
					$data->PossibleLanguages=$model->GetMemberLanguages() ;
					$page = new ContactlocalsPage("","preparenewmessage",$data);
					break ;
				case 'delete':
					$data->IdMess=$request[2] ;
					$model->DeleteMessage($data->IdMess) ;
					$page = new ContactlocalsPage("","",$model->LoadList()); // Without error
					break;
				case 'modify':
					$data->IdMess=$request[2] ;
					$tt=$model->LoadList($data->IdMess) ;
					$data->localmessage=$tt[0] ;
					$data->PossibleLocations=$model->GetAllowedLocation() ;
					$data->PossibleLanguages=$model->GetMemberLanguages() ;
					$page = new ContactlocalsPage("","preparenewmessage",$data);
					break ;
				case 'recordnewmessage':
					$model->recordnewmessage($args->post) ;
					$data=$model->LoadList($model->IdMess) ;
					$data->IdMess=$model->IdMess ;
					$page = new ContactlocalsPage("","preparenewmessage",$data);
					break ;
				case 'update':
					$IdPoll=(isset($request[2]) ? $request[2]: false) ;
					$page = new PollsPage("","showpoll",$model->LoadPoll($IdPoll));
					break ;
					
					
				case false:
				default :
				case '':
					// no request[1] was specified
					$page = new ContactlocalsPage("","",$model->LoadList()); // Without error
					break;
			}
		} // end of else not has right
        // return the $page object,
        // so the framework can call the "$page->render()" function.
        return $page;
    }
} // end of ContactlocalsController


?>