<?php



require_once("../htdocs/bw/lib/rights.php") ; // Requiring BW right 


class ForumController extends RoxControllerBase
{
    private $_model;
    
    public function __construct() {
        parent::__construct();
        $this->_model = new Forums();
    }
    
    public function __destruct() {
        unset($this->_model);
    }
    
    /**
    * index is called when http request = ./forums
    */
    public function index($args = false)
    {
        $request = $args->request;
        $User = APP_User::login();
        
        $model = new ForumModel();
        
        $filters = new ReadWriteObject();
        $filters->tags = array();
        $this->parseRequest($request, $filters);
        // $model->prepareForum();
        // echo '<pre>'; print_r($filters); echo '</pre>';
        
        if (!$filters->toplevel_action) {
            if (!$filters->filtered) {
                // this is top level
                echo 'not filtered';
                $page = new ForumOverviewPage();
            } else if ($topic_id = $filters->topic_id) {
                if (!$topic = $model->getTopicById($topic_id)) {
                    // no such topic found.. hmm
                    $page = new ForumTopicNotFoundPage();
                } else {
                    // $model->prepareTopic(true);
                    $page = new ForumTopicPage();
                    $page->topic = $topic;
                }
            } else if ($board = $model->getBoardByFilters($filters)) {
                // this must be a filtered board..
                $page = new ForumFilteredBoardPage();
                $page->board = $board;
                $page->filters = $filters;
            } else {
                $page = new ForumOverviewPage();
            }
        } else switch ($filters->action) {
            default:
                echo 'action = '.$this->action.'<br>';
                $page = new ForumOverviewPage();
        }
        
        $page->active_page = $filters->i_page;
        $page->filters = $filters;
        $page->model = $model;
        return $page;
        
    } // end of index
    
    
    
    
    /**
    * Parses a request
    * Extracts the current action, geoname-id, country-code, admin-code, all tags and the threadid from the request uri
    */
    private function parseRequest($request, $filters)
    {
        if (!isset($request[1])) {
            // show the overview?
        } else if (eregi('^([0-9]+)(-.*)*', $request[1], $regs)) {
            $filters->topic_id = $regs[1];  // topic id
            foreach (array_slice($request, 2) as $r) {
                $this->parseRequestKeyword($r, $filters);
            }
            $filters->filtered = true;
        } else switch ($request_1 = $request[1]) {
            case 'suggestTags':
            case 'member':
            case 'modeditpost':
            case 'modedittag':
            case 'subscriptions':
            case 'subscribe':
            case 'rules':
                $filters->$request_1 = true;
                $filters->toplevel_action = $request_1;
                break;
            default:
                foreach (array_slice($request, 1) as $r) {
                    $this->parseRequestKeyword($r, $filters);
                }
        }
    }
    
    protected function parseRequestKeyword($r, $filters)
    {
        if (!$r) {
            // do nothing
        } else if (eregi('page([0-9]+)', $r, $regs)) {
            // $this->_model->setPage($regs[1]);
            $filters->i_page = $regs[1];
        } else switch($r) {
            case 'new':
            case 'edit':
            case 'reply':
            case 'modeditpost':
            case 'modedittag':
            case 'delete':
            case 'locationDropdowns':
                $filters->$r = true;
                $filters->action = $r;
                break;
            case '':
            default:
                $char = $r{0};
                $str_before_dash_array = explode('-', $r);
                $str_before_dash = $str_before_dash_array[0];
                $value = substr($str_before_dash, 1);
                
                $filter_keys_string = array(
                    'c' => 'country_code',
                    'k' => 'continent',
                    'a' => 'admin_code',
                );
                
                $filter_keys_id = array(
                    'g' => 'geoname_id',
                    's' => 'topic_id',
                    'm' => 'post_id',
                );
                
                if ('t' == $char) {
                    $filters->tags[] = $value;
                    $filters->filtered = true;
                } else if (isset($filter_keys_string[$char])) {
                    $key = $filter_keys_string[$char];
                    $filters->$key = $value;
                    $filters->filtered = true;
                } else if (isset($filter_keys_id[$char])) {
                    $key = $filter_keys_id[$char];
                    $filters->$key = (int) $value;
                    $filters->filtered = true;
                } else {
                    // do nothing
                }
        }
    }
}



?>