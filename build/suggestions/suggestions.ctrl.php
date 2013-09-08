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
        $this->redirectAbsolute($this->router->url('suggestions_votelist'));
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
            } else {
                $suggestion = $this->_model->updateSuggestion($args);
                $this->setFlashNotice('SuggestionEditSuccess');
            }
            return true;
        }
    }

    public function suggestionsEditCreate() {
        error_log("Edit/Create");
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

    public function suggestionsApproveList() {
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
    }

    public function suggestionsApprove() {
        error_log("approve");
        $loggedInMember = $this->_model->getLoggedInMember();
        if (!$loggedInMember) {
            $this->redirectAbsolute($this->router->url('suggestions_votelist'));
        }
        $id = $this->route_vars['id'];
        $suggestion = new Suggestion($id);
        error_log(print_r($suggestion, true));
        $page = new SuggestionsApprovePage($loggedInMember);
        $page->suggestion = $suggestion;
        return $page;
    }

    public function suggestionsDiscussList() {
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

    public function suggestionsAddOptionsList() {
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

    public function suggestionsVoteList() {
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

    public function suggestionsRankList() {
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

    public function suggestionsDevList() {
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

    public function suggestionsRejectedList() {
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

    public function suggestionsProcess() {
        $pageno = 0;
        if (isset($this->route_vars['pageno'])) {
            $pageno = $this->route_vars['pageno'] - 1;
        }
        $page = new SuggestionsProcessPage($this->_model->getLoggedInMember());
        return $page;
    }

    public function suggestionsTeam() {
        $pageno = 0;
        if (isset($this->route_vars['pageno'])) {
            $pageno = $this->route_vars['pageno'] - 1;
        }
        $page = new SuggestionsTeamPage($this->_model->getLoggedInMember());
        return $page;
    }
}
?>