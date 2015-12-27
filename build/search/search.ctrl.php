<?php
/**
 * Searchmembers controller class.
 *
 * @author shevek
 */
class SearchController extends RoxControllerBase
{
    private $model;
    /**
     * Constructor.
     */
    public function __construct() {
        parent::__construct();
        $this->model = new SearchModel();
    }

    /**
     * Redirects to my activities if a member is logged in otherwise shows upcoming activities
     */
    public function searchMembers() {
        $this->redirectAbsolute($this->router->url('searchmembers_text'));
    }

    /**
     * call back function for the simple search pages
     *
     * Calls the model to check if the location exists and is unique. If not returns a list of
     * possible locations to choose from.
     */
    public function searchMembersCallback(StdClass $args, ReadOnlyObject $action,
        ReadWriteObject $mem_redirect, ReadWriteObject $mem_resend) {

        $vars = $args->get;
        $errors = $this->model->checkSearchVarsOk($vars);
        $isAdvanced = isset($vars['search-advanced']);
        if (count($errors)>0)  {
            $mem_redirect->vars = $vars;
            $mem_redirect->errors = $errors;
            if ($isAdvanced) {
                return $this->router->url('searchmembers_text_advanced', array(), false);
            }
            return false;
        }
        $mem_redirect->results = $this->model->getResultsForLocation($vars);
        $mem_redirect->vars = $vars;
        if ($isAdvanced) {
            return $this->router->url('searchmembers_text_advanced', array(), false);
        }
        return true;
    }

    /**
     * Loads the search page with a map and allows to search for members with simple
     * options like number of guests and a place.
     *
     * If called with the url extended with /advanced it shows advanced options as well.
     */
    public function searchMembersOnMap() {
        $page = new SearchMembersMapPage();
        return $page;
    }

    /**
     * Loads the search page without a map and allows to search for members with simple
     * options like number of guests and a place.
     *
     * If called with the url extended with /advanced it shows advanced options as well.
     */
    public function searchMembersText() {
        $page = new SearchMembersTextPage();
        $page->member = $this->model->getLoggedInMember();
        $vars = $this->args_vars->get;
        $isAdvanced = (isset($this->request_vars[3]) && $this->request_vars[3] == 'advanced');
        if (empty($vars)) {
            $vars = array_merge($this->model->getDefaultSimpleOptions(), $this->model->getDefaultAdvancedOptions());
        } else {
            $isAdvanced = isset($vars['search-advanced']);
            $page->errors = $this->model->checkSearchVarsOk($vars);
            if (count($page->errors) == 0)  {
                $page->results = $this->model->getResultsForLocation($vars);
            }
        }
        if ($page->results) {
            switch ($page->results['type']) {
                case 'members':
                    $page->membersResultsReturned = true;
                    $params = new StdClass;
                    $params->strategy = new FullPagePager();
                    $params->page_url = "/search/members/text?" . http_build_query($vars);
                    $params->page_url_marker = 'search-page';
                    $params->page_method = 'get';
                    if ($page->member) {
                        $params->items = $page->results['countOfMembers'];
                    } else {
                        $params->items = $page->results['countOfPublicMembers'];
                    }
                    $activePage = 1;
                    if (isset($vars['search-page'])) {
                        $activePage = $vars['search-page'];
                    }
                    $params->active_page = $activePage;
                    $params->items_per_page = $vars['search-number-items'];
                    $page->pager = new PagerWidget($params);
                    break;
                case 'places':
                case 'admin1s':
                case 'countries':
                    $page->locationsResultsReturned = true;
                    $page->locations = $page->results['locations'];
                    break;
            }
        }
        $page->vars = $vars;
        if ($isAdvanced) {
            $page->showAdvanced = true;
        } else {
            $page->showAdvanced = false;
        }
        return $page;
    }

    /**
     *
     */
    public function loadAdvancedOptions() {
        header('Content-type: text/html, charset=utf-8');
        $vars = $this->model->getDefaultAdvancedOptions();
        include(SCRIPT_BASE . '/build/search/templates/advancedoptions_helper.php');
        include(SCRIPT_BASE . '/build/search/templates/advancedoptions.php');
        exit;
    }

    /**
     * Returns a JSON encoded list of possible locations based on the provided location
     */
    public function searchSuggestLocations()
    {
        $type = $this->route_vars['type'];
        $location = $this->args_vars->get['name'];
        $callback = $this->args_vars->get['callback'];
        $locations = $this->model->suggestLocations($location, $type);
        header('Content-type: application/javascript, charset=utf-8');
        $javascript = $callback . '(' . json_encode($locations) . ')';
        echo $javascript . "\n";
        exit;
    }

    /**
     * Returns a JSON encoded list of possible members starting with provided text
     */
    public function searchMemberUsernames()
    {
        $username = $this->args_vars->get['q'];
        $callback = $this->args_vars->get['callback'];
        $usernames = $this->model->suggestUsernames($username);
        header('Content-type: application/javascript, charset=utf-8');
        // $javascript = $callback . '(' . json_encode($usernames) . ')';
        $javascript = json_encode($usernames);
        echo $javascript . "\n";
        exit;
    }

    /**
     *
     */
    public function searchSuggestionsCallback(StdClass $args, ReadOnlyObject $action,
        ReadWriteObject $mem_redirect, ReadWriteObject $mem_resend) {

        $vars = $args->post;
        $errors = $this->model->checkSuggestionVarsOk($vars);
        if (count($errors)>0)  {
            $mem_redirect->vars = $vars;
            $mem_redirect->errors = $errors;
            return false;
        }
        $mem_redirect->results = $this->model->searchSuggestions($vars['text']);
        $mem_redirect->vars = $vars;
        return true;
    }
}
?>