<?php
/**
 * Searchmembers controller class.
 *
 * @author shevek
 */
class SearchController extends RoxControllerBase
{
    /**
     * Constructor.
     */
    public function __construct() {
        parent::__construct();
        $this->_model = new SearchModel();
    }

    /**
     * Redirects to my activities if a member is logged in otherwise shows upcoming activities
     */
    public function searchMembers() {
        error_log(print_r($this->request_vars, true));
        error_log('Before');
        $this->redirectAbsolute($this->router->url('searchmembers_text'));
    }

    /**
     * call back function for the simple search pages
     *
     * Calls the model to check if the location exists and is unique. If not returns a list of
     * possible locations to choose from.
     */
    public function searchMembersSimpleCallback(StdClass $args, ReadOnlyObject $action,
        ReadWriteObject $mem_redirect, ReadWriteObject $mem_resend) {
        $vars = $args->post;
        $mem_redirect->results = $this->_model->getResultsForLocation($vars);
        $mem_redirect->vars = $vars;
        error_log(print_r($mem_redirect->results, true));
        error_log(print_r($mem_redirect->vars, true));
        return true;
    }

    /**
     * Loads the search page with a map and allows to search for members with simple
     * options like number of guests and a place.
     */
    public function searchMembersOnMap() {
        error_log(print_r($this->request_vars, true));
        error_log('After');
        $page = new SearchMembersMapPage();
        return $page;
    }

    /**
     * Loads the search page without a map and allows to search for members with simple
     * options like number of guests and a place.
     */
    public function searchMembersText() {
        $page = new SearchMembersTextPage();
        return $page;
    }

    /**
     * Returns a JSON encoded list of possible locations based on the provided location
     */
    public function searchSuggestLocations() {
        $type = $this->route_vars['type'];
        $location = $this->args_vars->get['name'];
        $callback = $this->args_vars->get['callback'];
        $locations = $this->_model->suggestLocations($location, $type, $callback);
        header('Content-type: application/javascript, charset=utf-8');
        $javascript = $callback . '(' . json_encode($locations) . ')';
        echo $javascript . "\n";
        exit;
    }
}
?>