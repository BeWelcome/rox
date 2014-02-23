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

        $vars = $args->post;
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
        if (isset($this->request_vars[3]) && $this->request_vars[3] == 'advanced') {
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
    public function searchSuggestLocations() {
        $type = $this->route_vars['type'];
        $location = $this->args_vars->get['name'];
        $callback = $this->args_vars->get['callback'];
        $locations = $this->model->suggestLocations($location, $type, $callback);
        header('Content-type: application/javascript, charset=utf-8');
        $javascript = $callback . '(' . json_encode($locations) . ')';
        echo $javascript . "\n";
        exit;
    }
}
?>