<?php
/**
 * suggestions controller class.
 *
 * @author shevek
 */
class SuggestionsController extends RoxControllerBase
{
    const SUGGESTIONS_DUPLICATE = 0; // suggestion already existed and was there marked as duplicate by suggestion team
    const SUGGESTIONS_AWAIT_APPROVAL = 1; // wait for suggestion team to check
    const SUGGESTIONS_DISCUSSION = 2; // discuss the suggestion try to find solutions
    const SUGGESTIONS_ADD_OPTIONS = 4; // enter solutions into the system (10 days after start)
    const SUGGESTIONS_VOTING = 8; // allow voting (30 days after switching to discussion mode)
    const SUGGESTIONS_RANKING = 16; // Voting finished (30 days after voting started). Ranking can be done now.
    const SUGGESTIONS_REJECTED = 32; // Suggestion didn't reach the necessary level of approval (at least 'good')
    const SUGGESTIONS_IMPLEMENTING = 64; // Dev started implementing (no more ranking)
    const SUGGESTIONS_IMPLEMENTED = 128; // Dev finished implementation
    const SUGGESTIONS_DEV = 192;

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

    public function approveSuggestionCallback(StdClass $args, ReadOnlyObject $action,
        ReadWriteObject $mem_redirect, ReadWriteObject $mem_resend)
    {
    }

    public function postSuggestionCallback(StdClass $args, ReadOnlyObject $action,
        ReadWriteObject $mem_redirect, ReadWriteObject $mem_resend)
    {
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

    public function suggestionsUpdate() {
        if (!is_numeric($this->route_vars['id'])) {
            $this->redirectAbsolute($this->router->url('suggestions_votelist'));
        }
        $id = intval($this->route_vars['id']);
        $member = $this->_model->getLoggedInMember();
        if (!$member) {
            // redirect to voting list (to allow non members to see what's
            // going on
            $this->redirectAbsolute($this->router->url('suggestions_votelist'));
        }
        $suggestion= new Suggestion($id);
        switch($suggestion->state) {
            case self::SUGGESTIONS_AWAIT_APPROVAL:
                $page = new SuggestionsApprovePage();
                break;
            case self::SUGGESTIONS_DISCUSSION:
                $page = new SuggestionsDiscussPage();
                break;
            case self::SUGGESTIONS_ADD_OPTIONS:
                $page = new SuggestionsAddOptionsPage();
                break;
            case self::SUGGESTIONS_VOTING:
                // Members can change their ballot as long as the voting is running
                $page = new SuggestionsVotingPage();
                $page->votes = $this->_model->getVotesForLoggedInMember($suggestion);
                break;
        }
        $page->suggestion = $suggestion;
        $page->member = $member;
        return $page;
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
                $_SESSION['SuggestionStatus'] = array('SuggestionCreateSuccess', $suggestion);
            } else {
                $suggestion = $this->_model->updateSuggestion($args);
                $_SESSION['SuggestionStatus'] = array('SuggestionUpdateSuccess', $suggestion);
            }
            return $this->router->url('suggestion_approvelist', array(), false);
        }
    }

    public function suggestionsEditCreate() {
        $loggedInMember = $this->_model->getLoggedInMember();
        $hasSuggestionRights = $this->hasSuggestionRights();
        if (!$loggedInMember | !$hasSuggestionRights) {
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
        $page = new SuggestionsEditCreatePage();
        $page->hasSuggestionRights = $hasSuggestionRights;
        $page->member = $loggedInMember;
        $page->suggestion = $suggestion;
        return $page;
    }

    public function suggestionsApproveList() {
        $pageno = 0;
        if (isset($this->route_vars['pageno'])) {
            $pageno = $this->route_vars['pageno'] - 1;
        }
        $page = new SuggestionsApproveListPage();
        $page->member = $this->_model->getLoggedInMember();
        $page->hasSuggestionsRight = $this->hasSuggestionRights();
        $count = $this->_model->getSuggestionsCount(self::SUGGESTIONS_AWAIT_APPROVAL);
        $suggestions = $this->_model->getSuggestions(self::SUGGESTIONS_AWAIT_APPROVAL, $pageno, self::SUGGESTIONS_PER_PAGE);
        $page->suggestions = $suggestions;

        $page->pager = $this->getPager('approve', $count, $pageno);

        return $page;
    }

    public function suggestionsDiscussList() {
        $pageno = 0;
        if (isset($this->route_vars['pageno'])) {
            $pageno = $this->route_vars['pageno'] - 1;
        }
        $page = new SuggestionsDiscussListPage();
        $page->member = $this->_model->getLoggedInMember();
        $count = $this->_model->getSuggestionsCount(self::SUGGESTIONS_DISCUSSION);
        $suggestions = $this->_model->getSuggestions(self::SUGGESTIONS_DISCUSSION, $pageno, self::SUGGESTIONS_PER_PAGE);
        $page->suggestions = $suggestions;

        $page->pager = $this->getPager('discuss', $count, $pageno);

        return $page;
    }

    public function suggestionsAddOptionsList() {
        $pageno = 0;
        if (isset($this->route_vars['pageno'])) {
            $pageno = $this->route_vars['pageno'] - 1;
        }
        $page = new SuggestionsAddOptionsListPage();
        $page->member = $this->_model->getLoggedInMember();
        $count = $this->_model->getSuggestionsCount(self::SUGGESTIONS_ADD_OPTIONS);
        $suggestions = $this->_model->getSuggestions(self::SUGGESTIONS_ADD_OPTIONS, $pageno, self::SUGGESTIONS_PER_PAGE);
        $page->suggestions = $suggestions;

        $page->pager = $this->getPager('addoptions', $count, $pageno);

        return $page;
    }

    public function suggestionsVoteList() {
        $pageno = 0;
        if (isset($this->route_vars['pageno'])) {
            $pageno = $this->route_vars['pageno'] - 1;
        }
        $page = new SuggestionsVoteListPage();
        $page->member = $this->_model->getLoggedInMember();
        $hasSuggestionRights = $this->hasSuggestionRights();
        $count = $this->_model->getSuggestionsCount(self::SUGGESTIONS_VOTING);
        $suggestions = $this->_model->getSuggestions(self::SUGGESTIONS_VOTING, $pageno, self::SUGGESTIONS_PER_PAGE);
        $page->suggestions = $suggestions;
        $page->hasSuggestionRights = $hasSuggestionRights;
        $page->pager = $this->getPager('vote', $count, $pageno);

        return $page;
    }

    public function suggestionsRankList() {
        $pageno = 0;
        if (isset($this->route_vars['pageno'])) {
            $pageno = $this->route_vars['pageno'] - 1;
        }
        $page = new SuggestionsRankListPage();
        $page->member = $this->_model->getLoggedInMember();
        $count = $this->_model->getSuggestionsCount(self::SUGGESTIONS_RANKING);
        $suggestions = $this->_model->getSuggestions(self::SUGGESTIONS_RANKING, $pageno, self::SUGGESTIONS_PER_PAGE);
        $page->suggestions = $suggestions;

        $page->pager = $this->getPager('rank', $count, $pageno);

        return $page;
    }

    public function suggestionsDevList() {
        $pageno = 0;
        if (isset($this->route_vars['pageno'])) {
            $pageno = $this->route_vars['pageno'] - 1;
        }
        $page = new SuggestionsDevListPage();
        $page->member = $this->_model->getLoggedInMember();
        $count = $this->_model->getSuggestionsCount(self::SUGGESTIONS_DEV);
        $suggestions = $this->_model->getSuggestions(self::SUGGESTIONS_DEV, $pageno, self::SUGGESTIONS_PER_PAGE);
        $page->suggestions = $suggestions;

        $page->pager = $this->getPager('rank', $count, $pageno);

        return $page;
    }

    public function suggestionsRejectedList() {
        $pageno = 0;
        if (isset($this->route_vars['pageno'])) {
            $pageno = $this->route_vars['pageno'] - 1;
        }
        $page = new SuggestionsRejectedListPage();
        $page->member = $this->_model->getLoggedInMember();
        $count = $this->_model->getSuggestionsCount(self::SUGGESTIONS_REJECTED);
        $suggestions = $this->_model->getSuggestions(self::SUGGESTIONS_REJECTED, $pageno, self::SUGGESTIONS_PER_PAGE);
        $page->suggestions = $suggestions;

        $page->pager = $this->getPager('rejected', $count, $pageno);

        return $page;
    }

    public function suggestionsProcess() {
        $pageno = 0;
        if (isset($this->route_vars['pageno'])) {
            $pageno = $this->route_vars['pageno'] - 1;
        }
        $page = new SuggestionsProcessPage();
        $page->member = $this->_model->getLoggedInMember();
        return $page;
    }

    public function suggestionsTeam() {
        $pageno = 0;
        if (isset($this->route_vars['pageno'])) {
            $pageno = $this->route_vars['pageno'] - 1;
        }
        $page = new SuggestionsTeamPage();
        $page->member = $this->_model->getLoggedInMember();
        return $page;
    }
}
?>