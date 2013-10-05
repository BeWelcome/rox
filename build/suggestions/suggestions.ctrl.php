<?php
/**
 * suggestions controller class.
 *
 * @author shevek
 */
class SuggestionsController extends RoxControllerBase
{
    const SUGGESTIONS_PER_PAGE = 10;

    /**
     * Constructor.
     */
    public function __construct() {
        parent::__construct();
        $this->_model = new suggestionsModel();
    }

    public function suggestions() {
        $this->redirectAbsolute($this->router->url('suggestions_about'));
    }

    /**
     * Redirects for a given suggestion to the appropiate page
     */
    public function show() {
        $loggedInMember = $this->_model->getLoggedInMember();
        $id = $this->route_vars['id'];
        $suggestion = new Suggestion($id);
        $params = array('id' => $id);
        $url = $this->router->url('suggestions_view', $params);
        if ($loggedInMember) {
            switch ($suggestion->state) {
                case SuggestionsModel::SUGGESTIONS_AWAIT_APPROVAL:
                    if ($this->_model->hasSuggestionRight($loggedInMember)) {
                        $url = $this->router->url('suggestions_approve', $params);
                    }
                    break;
                case SuggestionsModel::SUGGESTIONS_DISCUSSION:
                    $url = $this->router->url('suggestions_discuss', $params);
                    break;
                case SuggestionsModel::SUGGESTIONS_ADD_OPTIONS:
                    $url = $this->router->url('suggestions_addoptions', $params);
                    break;
                case SuggestionsModel::SUGGESTIONS_VOTING:
                    $url = $this->router->url('suggestions_voting', $params);
                    break;
            }
        }
        $this->redirectAbsolute($url);
    }

    protected function getPager($url, $count, $pageno, $itemsPerPage = self::SUGGESTIONS_PER_PAGE) {
        $params = new StdClass;
        $params->strategy = new HalfPagePager('right');
        $params->page_url = 'suggestions/' . $url . '/';
        $params->page_url_marker = 'page';
        $params->page_method = 'url';
        $params->items = $count;
        $params->active_page = $this->pageno;
        $params->items_per_page = $itemsPerPage;
        $pager = new PagerWidget($params);
        return $pager;
    }

    public function voteSuggestionCallback(StdClass $args, ReadOnlyObject $action,
        ReadWriteObject $mem_redirect, ReadWriteObject $mem_resend)
    {
        $errors = $this->_model->checkVoteSuggestion($args);
        if (!empty($errors)) {
            $mem_redirect->errors = $errors;
            $mem_redirect->vars = $args->post;
            return false;
        }
        $member = $this->_model->getLoggedInMember();
        $suggestion = $this->_model->voteForSuggestion($member, $args);
        $this->setFlashNotice($this->getWords()->get('SuggestionsVoted', date('d.m.Y', $suggestion->votingendts)));
        return true;
    }

    public function editCreateSuggestionCallback(StdClass $args, ReadOnlyObject $action,
        ReadWriteObject $mem_redirect, ReadWriteObject $mem_resend)
    {
        $errors = $this->_model->checkEditCreateSuggestionVarsOk($args);
        if (count($errors) > 0) {
            $mem_redirect->errors = $errors;
            $mem_redirect->vars = $args->post;
            return false;
        } else {
            if ($args->post['suggestion-id'] == 0) {
                $suggestion = $this->_model->createSuggestion($args);
                $this->setFlashNotice('SuggestionCreateSuccess');
                return $this->router->url('suggestions_approvelist', array(), false);
            } else {
                $suggestion = $this->_model->editSuggestion($args);
                $this->setFlashNotice('SuggestionEditSuccess');
                return $this->redirect($this->router->url('suggestions_show', array('id' => $suggestion->id)), false);
            }
        }
    }

    public function editCreate() {
        $loggedInMember = $this->_model->getLoggedInMember();
        if (!$loggedInMember) {
            $this->redirectAbsolute($this->router->url('suggestions_votelist'));
        }
        if (isset($this->route_vars['id'])) {
            $id = $this->route_vars['id'];
            $suggestion = new Suggestion($id);
        } else {
            $suggestion = new Suggestion;
        }
        $pageno = 0;
        if (isset($this->route_vars['pageno'])) {
            $pageno = $this->route_vars['pageno'] - 1;
        }
        $page = new SuggestionsEditCreatePage($loggedInMember);
        $page->suggestion = $suggestion;
        return $page;
    }

    public function approveList() {
        $pageno = 0;
        if (isset($this->route_vars['pageno'])) {
            $pageno = $this->route_vars['pageno'] - 1;
        }
        $page = new SuggestionsApproveListPage($this->_model->getLoggedInMember());
        $count = $this->_model->getSuggestionsCount(SuggestionsModel::SUGGESTIONS_AWAIT_APPROVAL);
        $suggestions = $this->_model->getSuggestions(SuggestionsModel::SUGGESTIONS_AWAIT_APPROVAL, $pageno, self::SUGGESTIONS_PER_PAGE);
        $page->suggestions = $suggestions;

        $page->pager = $this->getPager('approve', $count, $pageno);

        return $page;
    }

    public function approveSuggestionCallback(StdClass $args, ReadOnlyObject $action,
        ReadWriteObject $mem_redirect, ReadWriteObject $mem_resend)
    {
        if (array_key_exists('suggestion-approve', $args->post)) {
            $this->_model->approveSuggestion($args->post['suggestion-id']);
            $this->setFlashNotice('SuggestionApproved');
        }
        if (array_key_exists('suggestion-duplicate', $args->post)) {
            $this->_model->markDuplicateSuggestion($args->post['suggestion-id']);
            $this->setFlashNotice('SuggestionSetAsDuplicate');
        }
        return $this->router->url('suggestions_approvelist', array(), false);
    }

    public function approve() {
        $loggedInMember = $this->_model->getLoggedInMember();
        if (!$loggedInMember) {
            $this->redirectAbsolute($this->router->url('suggestions_votelist'));
        }
        $id = $this->route_vars['id'];
        $suggestion = new Suggestion($id);
        $page = new SuggestionsApprovePage($loggedInMember);
        $page->suggestion = $suggestion;
        return $page;
    }

    public function discussList() {
        $pageno = 0;
        if (isset($this->route_vars['pageno'])) {
            $pageno = $this->route_vars['pageno'] - 1;
        }
        $page = new SuggestionsDiscussListPage($this->_model->getLoggedInMember());
        $count = $this->_model->getSuggestionsCount(SuggestionsModel::SUGGESTIONS_DISCUSSION);
        $suggestions = $this->_model->getSuggestions(SuggestionsModel::SUGGESTIONS_DISCUSSION, $pageno, self::SUGGESTIONS_PER_PAGE);
        $page->suggestions = $suggestions;

        $page->pager = $this->getPager('discuss', $count, $pageno);

        return $page;
    }

    public function discuss() {
        $loggedInMember = $this->_model->getLoggedInMember();
        if (!$loggedInMember) {
            $this->redirectAbsolute($this->router->url('suggestions_votelist'));
        }
        $id = $this->route_vars['id'];
        $suggestion = new Suggestion($id);
        $page = new SuggestionsDiscussPage($loggedInMember);
        $page->suggestion = $suggestion;
        $page->model = $this->_model;
        return $page;
    }

    public function discussReply() {
        $loggedInMember = $this->_model->getLoggedInMember();
        if (!$loggedInMember) {
            $this->redirectAbsolute($this->router->url('suggestions_votelist'));
        }
        $id = $this->route_vars['id'];
        $suggestion = new Suggestion($id);
        $page = new SuggestionsDiscussReplyPage($loggedInMember);
        $page->suggestion = $suggestion;
        $page->model = $this->_model;
        return $page;
    }

    public function editOptionCallback(StdClass $args, ReadOnlyObject $action,
        ReadWriteObject $mem_redirect, ReadWriteObject $mem_resend)
    {
        $errors = $this->_model->checkAddOptionVarsOk($args);
        if (count($errors) > 0) {
            $mem_redirect->errors = $errors;
            $mem_redirect->vars = $args->post;
            return false;
        }
        $this->_model->editOption($args);
        $this->setFlashNotice('SuggestionEditOptionSuccess');
        return $this->router->url('suggestions_addoptions',
            array(
                'id' => $args->post['suggestion-id'],
            ), false);
    }

    public function editOption() {
        $loggedInMember = $this->_model->getLoggedInMember();
        if (!$loggedInMember) {
            $this->redirectAbsolute($this->router->url('suggestions_votelist'));
        }
        $id = $this->route_vars['id'];
        $optionId = $this->route_vars['optid'];
        $suggestion = new Suggestion($id);
        $page = new SuggestionsEditOptionPage($loggedInMember);
        $page->suggestion = $suggestion;
        $page->option = new SuggestionOption($optionId);
        $page->model = $this->_model;
        return $page;
    }

    public function deleteOptionCallback(StdClass $args, ReadOnlyObject $action,
        ReadWriteObject $mem_redirect, ReadWriteObject $mem_resend)
    {
        $this->_model->deleteOption($args);
        $this->setFlashNotice('SuggestionDeleteOptionSuccess');
        return $this->router->url('suggestions_addoptions', array('id' => $suggestion->id), false);
    }

    public function deleteOption() {
        $loggedInMember = $this->_model->getLoggedInMember();
        if (!$loggedInMember) {
            $this->redirectAbsolute($this->router->url('suggestions_votelist'));
        }
        $id = $this->route_vars['id'];
        $optionId = $this->route_vars['optid'];
        $suggestion = new Suggestion($id);
        $page = new SuggestionsDeleteOptionPage($loggedInMember);
        $page->suggestion = $suggestion;
        $page->option = new SuggestionOption($optionId);
        $page->model = $this->_model;
        return $page;
    }

    public function restoreOption() {
        $loggedInMember = $this->_model->getLoggedInMember();
        if (!$loggedInMember) {
            $this->redirectAbsolute($this->router->url('suggestions_addoptions'));
        }
        $optionId = $this->route_vars['optid'];
        $this->_model->restoreOption($optionId);
        $this->setFlashNotice('SuggestionRestoreOptionSuccess');
        return $this->router->url('suggestions_addoptions', array('id' => $this->route_vars['id']), false);
    }

    public function addOptionCallback(StdClass $args, ReadOnlyObject $action,
        ReadWriteObject $mem_redirect, ReadWriteObject $mem_resend)
    {
        $errors = $this->_model->checkAddOptionVarsOk($args);
        if (count($errors) > 0) {
            $mem_redirect->errors = $errors;
            $mem_redirect->vars = $args->post;
            return false;
        }
        $this->_model->addOption($args);
        $this->setFlashNotice('SuggestionAddOptionSuccess');
        return true;
    }

    public function changeStateCallback(StdClass $args, ReadOnlyObject $action,
        ReadWriteObject $mem_redirect, ReadWriteObject $mem_resend)
    {
        $errors = $this->_model->checkChangeStateVarsOk($args);
        if (count($errors) > 0) {
            $mem_redirect->errors = $errors;
            $mem_redirect->vars = $args->post;
            return false;
        }
        $this->_model->changeState($args);
        $this->setFlashNotice('SuggestionChangeStatusSuccess');
        return $this->redirect($this->router->url('suggestions_show', array('id' => $args->post['suggestion-id'])), false);
    }

    public function addOptions() {
        $loggedInMember = $this->_model->getLoggedInMember();
        $id = $this->route_vars['id'];
        $suggestion = new Suggestion($id);
        $page = new SuggestionsAddOptionsPage($loggedInMember);
        $page->suggestion = $suggestion;
        $page->model = $this->_model;
        return $page;
    }

    public function addOptionsReply() {
        $loggedInMember = $this->_model->getLoggedInMember();
        if (!$loggedInMember) {
            $this->redirectAbsolute($this->router->url('suggestions_votelist'));
        }
        $id = $this->route_vars['id'];
        $suggestion = new Suggestion($id);
        $page = new SuggestionsAddOptionsReplyPage($loggedInMember);
        $page->suggestion = $suggestion;
        $page->model = $this->_model;
        return $page;
    }

    public function addOptionsList() {
        $pageno = 0;
        if (isset($this->route_vars['pageno'])) {
            $pageno = $this->route_vars['pageno'] - 1;
        }
        $page = new SuggestionsAddOptionsListPage($this->_model->getLoggedInMember());
        $count = $this->_model->getSuggestionsCount(SuggestionsModel::SUGGESTIONS_ADD_OPTIONS);
        $suggestions = $this->_model->getSuggestions(SuggestionsModel::SUGGESTIONS_ADD_OPTIONS, $pageno, self::SUGGESTIONS_PER_PAGE);
        $page->suggestions = $suggestions;

        $page->pager = $this->getPager('addoptions', $count, $pageno);

        return $page;
    }

    public function voteList() {
        $pageno = 0;
        if (isset($this->route_vars['pageno'])) {
            $pageno = $this->route_vars['pageno'] - 1;
        }
        $page = new SuggestionsVoteListPage($this->_model->getLoggedInMember());
        $count = $this->_model->getSuggestionsCount(SuggestionsModel::SUGGESTIONS_VOTING);
        $suggestions = $this->_model->getSuggestions(SuggestionsModel::SUGGESTIONS_VOTING, $pageno, self::SUGGESTIONS_PER_PAGE);
        $page->suggestions = $suggestions;
        $page->pager = $this->getPager('vote', $count, $pageno);

        return $page;
    }

    public function vote() {
        $loggedInMember = $this->_model->getLoggedInMember();
        if (!$loggedInMember) {
            $this->redirectAbsolute($this->router->url('suggestions_votelist'));
        }
        $id = $this->route_vars['id'];
        $suggestion = new Suggestion($id);
        $page = new SuggestionsVotingPage($loggedInMember);
        $page->votes = $this->_model->getVotesForLoggedInMember($suggestion);
        $page->suggestion = $suggestion;
        return $page;
    }

    public function rankList() {
        $pageno = 0;
        if (isset($this->route_vars['pageno'])) {
            $pageno = $this->route_vars['pageno'] - 1;
        }
        $page = new SuggestionsRankListPage($this->_model->getLoggedInMember());
        $count = $this->_model->getSuggestionsCount(SuggestionsModel::SUGGESTIONS_RANKING);
        $suggestions = $this->_model->getSuggestions(SuggestionsModel::SUGGESTIONS_RANKING, $pageno, self::SUGGESTIONS_PER_PAGE);
        $page->suggestions = $suggestions;

        $page->pager = $this->getPager('rank', $count, $pageno);

        return $page;
    }

    public function devList() {
        $pageno = 0;
        if (isset($this->route_vars['pageno'])) {
            $pageno = $this->route_vars['pageno'] - 1;
        }
        $page = new SuggestionsDevListPage($this->_model->getLoggedInMember());
        $count = $this->_model->getSuggestionsCount(SuggestionsModel::SUGGESTIONS_DEV);
        $suggestions = $this->_model->getSuggestions(SuggestionsModel::SUGGESTIONS_DEV, $pageno, self::SUGGESTIONS_PER_PAGE);
        $page->suggestions = $suggestions;

        $page->pager = $this->getPager('rank', $count, $pageno);

        return $page;
    }

    public function rejectedList() {
        $pageno = 0;
        if (isset($this->route_vars['pageno'])) {
            $pageno = $this->route_vars['pageno'] - 1;
        }
        $page = new SuggestionsRejectedListPage($this->_model->getLoggedInMember());
        $count = $this->_model->getSuggestionsCount(SuggestionsModel::SUGGESTIONS_REJECTED);
        $suggestions = $this->_model->getSuggestions(SuggestionsModel::SUGGESTIONS_REJECTED, $pageno, self::SUGGESTIONS_PER_PAGE);
        $page->suggestions = $suggestions;

        $page->pager = $this->getPager('rejected', $count, $pageno);

        return $page;
    }

    public function team() {
        $page = new SuggestionsTeamPage($this->_model->getLoggedInMember());
        return $page;
    }

    public function about() {
        $page = new SuggestionsAboutPage($this->_model->getLoggedInMember());
        return $page;
    }

    public function view() {
        $page = new SuggestionsViewPage($this->_model->getLoggedInMember());
        $page->suggestion = new Suggestion($this->route_vars['id']);
        return $page;
    }
}
?>