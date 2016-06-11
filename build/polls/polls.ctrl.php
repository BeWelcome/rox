<?php

/**
 * verifymembers controller
 *
 * @package verifymembers
 * @author JeanYves
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License (GPL)
 * @version $Id$
 */
require_once("../htdocs/bw/lib/rights.php") ; // Requiring BW right 
// TODO: use the MyTB right.. (MOD_right)
// no, not for now
class PollsController extends RoxControllerBase
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
        $model = new PollsModel;

        
        if (!$this->_session->has( 'IdMember' )) {
            $page = new MessagesMustloginPage();
            $page->setRedirectURL(implode('/',$request));
        		return $page;
        } 
//        print_r($args->post);
        
        // look at the request.
        switch (isset($request[1]) ? $request[1] : false) {
            case 'listall':
                $page = new PollsPage("","listall",$model->LoadList(""));
                break;
            case 'create':
                $page = new PollsPage("","create");
                break ;
						case 'listClose':
                $page = new PollsPage("","listClose",$model->LoadList("Close"));
                break ;
						case 'listOpen':
                $page = new PollsPage("","listOpen",$model->LoadList("Open"));
                break ;
						case 'listProject':
                $page = new PollsPage("","listProject",$model->LoadList("Project"));
                break ;
            case 'cancelvote':
								$IdPoll=(isset($request[2]) ? $request[2]: false) ;
      					MOD_log::get()->write("Prepare to contribute cancel vote #".$IdPoll,"polls") ; 				
								if ($model->CancelVote($IdPoll,"",$this->_session->get("IdMember"))) {
                	$page = new PollsPage("","cancelvote");
								}
								else {
                	$page = new PollsPage("","votenotcancelable");
								} 
                break ;
            case 'seeresults':
								$IdPoll=(isset($request[2]) ? $request[2]: false) ;
								if ($Data=$model->GetPollResults($IdPoll)) {
                	$page = new PollsPage("","seeresults",$Data);
								}
								else {
                	$page = new PollsPage("","resultsnotyetavailable");
								} 
                break ;
            case 'contribute':
								$IdPoll=(isset($request[2]) ? $request[2]: false) ;
      					MOD_log::get()->write("Prepare to contribute to poll #".$IdPoll,"polls") ; 				
								if ($model->CanUserContribute($IdPoll)) {
									$Data=$model->PrepareContribute($IdPoll) ;
                	$page = new PollsPage("","contribute",$Data);
								}
								else {
                	$page = new PollsPage("","sorryyoucannotcontribute");
								} 
                break ;
            case 'vote':
                // a nice trick to get all the post args as local variables...
                // they will all be prefixed by 'post_'
                extract($args->post, EXTR_PREFIX_ALL, 'post');

								$IdPoll=$post_IdPoll ;
								if ($model->CanUserContribute($IdPoll)) {
      						MOD_log::get()->write("Tryin to vote for poll #".$IdPoll,"polls") ; 				
									$Data=$model->AddVote($args->post,"",$this->_session->get("IdMember")) ;
                	$page = new PollsPage("","votedone",$Data);
								}
								else {
      						MOD_log::get()->write("Refusing vote for poll #".$IdPoll,"polls") ; 				
                	$page = new PollsPage("","probablyallreadyvote");
								} 
                break ;
            case 'update':
								$IdPoll=(isset($request[2]) ? $request[2]: false) ;
                $page = new PollsPage("","showpoll",$model->LoadPoll($IdPoll));
                break ;
								
            case 'doupdatepoll':
								$IdPoll=$args->post["IdPoll"] ;
								$model->UpdatePoll($args->post) ;				
                $page = new PollsPage("","showpoll",$model->LoadPoll($IdPoll));
                break ;
								
            case 'addchoice':
								$IdPoll=$args->post["IdPoll"] ;
								$model->AddChoice($args->post) ;				
                $page = new PollsPage("","showpoll",$model->LoadPoll($IdPoll));
                break ;
								
            case 'updatechoice':
								$IdPoll=$args->post["IdPoll"] ;
								$model->UpdateChoice($args->post) ;				
                $page = new PollsPage("","showpoll",$model->LoadPoll($IdPoll));
                break ;
								
            case 'createpoll':
      					MOD_log::get()->write("Creating a poll ","polls") ; 
								$model->UpdatePoll($args->post) ;				
                $page = new PollsPage("","listall",$model->LoadList("Project"));
                break ;
								
            case false:
            default :
            case '':
                // no request[1] was specified
                $page = new PollsPage("","",$model->LoadList("Open")); // Without error
                break;
        }
        // return the $page object,
        // so the framework can call the "$page->render()" function.
        return $page;
    }
}


?>