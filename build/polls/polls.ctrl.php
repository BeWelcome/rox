<?php

/**
 * polls controller
 *
 * @package polls
 * @author JeanYves
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License (GPL)
 * @version $Id$
 */
class PollsController extends RoxControllerBase
{
    /**
     * decide which page to show.
     * This method is called automatically
     */
    public function index($args)
    {
        $request = $args->request;
        $model = new PollsModel();

        // look at the request.
        switch (isset($request[1]) ? $request[1] : false) {
            case 'create':
                $page = new PollsPage("", "create");
                break;
            case 'list':
                switch(isset($request[2]) ? $request[2] : false)
                {
                    case 'all':
                        $page = new PollsPage("", "listall", $model->LoadList(""));
                        break;
                    case 'closed':
                        $page = new PollsPage("", "listClosed", $model->LoadList("Closed"));
                        break;
                    case 'open':
                        $page = new PollsPage("", "listOpen", $model->LoadList("Open"));
                        break;
                    case 'contributed':
                        $page = new PollsPage("", "list_contributed", $model->LoadContributed());
                        break;
                    case 'new':
                        $page = new PollsPage("", "listProject", $model->LoadList("Project"));
                        break;
                    default:
                        $page = new PollsPage("", "listall", $model->LoadList(""));
                }
                break;
            case 'cancelvote':
                $IdPoll = (isset($request[2]) ? $request[2] : false);
                MOD_log::get()->write("Prepare to contribute cancel vote #" . $IdPoll, "polls");
                if ($model->CancelVote($IdPoll, "", $this->session->get("IdMember"))) {
                    $page = new PollsPage("", "cancelvote");
                } else {
                    $page = new PollsPage("", "votenotcancelable");
                }
                break;
            case 'results':
            case 'seeresults':
                $IdPoll = (isset($request[2]) ? $request[2] : false);
                if ($Data = $model->GetPollResults($IdPoll)) {
                    $page = new PollsPage("", "seeresults", $Data);
                } else {
                    $page = new PollsPage("", "resultsnotyetavailable");
                }
                break;
            case 'contribute':
                $IdPoll = (isset($request[2]) ? $request[2] : false);
                MOD_log::get()->write("Prepare to contribute to poll #" . $IdPoll, "polls");
                if ($model->CanUserContribute($IdPoll)) {
                    $Data = $model->PrepareContribute($IdPoll);
                    $page = new PollsPage("", "contribute", $Data);
                } else {
                    $page = new PollsPage("", "sorryyoucannotcontribute");
                }
                break;
            case 'vote':
                // a nice trick to get all the post args as local variables...
                // they will all be prefixed by 'post_'
                extract($args->post, EXTR_PREFIX_ALL, 'post');

                $IdPoll = $post_IdPoll;
                if ($model->CanUserContribute($IdPoll)) {
                    MOD_log::get()->write("Tryin to vote for poll #" . $IdPoll, "polls");
                    $Data = $model->AddVote($args->post, "", $this->session->get("IdMember"));
                    $page = new PollsPage("", "votedone", $Data);
                } else {
                    MOD_log::get()->write("Refusing vote for poll #" . $IdPoll, "polls");
                    $page = new PollsPage("", "probablyallreadyvote");
                }
                break;
            case 'update':
                $IdPoll = (isset($request[2]) ? $request[2] : false);
                $page = new PollsPage("", "showpoll", $model->LoadPoll($IdPoll));
                break;

            case 'doupdatepoll':
                $IdPoll = $args->post["IdPoll"];
                $model->UpdatePoll($args->post);
                $page = new PollsPage("", "showpoll", $model->LoadPoll($IdPoll));
                break;

            case 'addchoice':
                $IdPoll = $args->post["IdPoll"];
                $model->AddChoice($args->post);
                $page = new PollsPage("", "showpoll", $model->LoadPoll($IdPoll));
                break;

            case 'updatechoice':
                $IdPoll = $args->post["IdPoll"];
                $model->UpdateChoice($args->post);
                $page = new PollsPage("", "showpoll", $model->LoadPoll($IdPoll));
                break;

            case 'createpoll':
                MOD_log::get()->write("Creating a poll ", "polls");
                $model->UpdatePoll($args->post);
                $page = new PollsPage("", "listall", $model->LoadList("Project"));
                break;
            case 'updatestatus':
                $model->UpdatePollStatus();
                $this->setFlashNotice('Updated polls status (set to closed after end date).');
                return $this->redirectAbsolute('/polls/list/all');
                break;
            case false:
            default :
            case '':
                // no request[1] was specified
                $page = new PollsPage("", "listOpen", $model->LoadList("Open")); // Without error
                break;
        }
        // return the $page object,
        // so the framework can call the "$page->render()" function.
        $page->member = $model->getLoggedInMember();
        return $page;
    }
}
