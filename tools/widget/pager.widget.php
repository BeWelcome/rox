<?php

class PagerWidget extends RoxWidget
{
    private $_prepared = false;
    private $pager_strategy;
    private $page_url;
    private $page_url_marker;
    private $page_method;
    private $items_total;
    private $items_per_page;
    private $active_page;

    /**
     *
     * @param object $params_object - standard object filled with vars
     * @access public
     */
    public function __construct($params_object)
    {
        parent::__construct();
        $this->pager_strategy = $params_object->strategy;
        $this->page_url =  ((isset($params_object->page_url)) ? $params_object->page_url : null);
        $this->page_url_marker = ((isset($params_object->page_url_marker)) ? $params_object->page_url_marker : 'page');
        $this->page_method = ((isset($params_object->page_method)) ? strtolower($params_object->page_method) : 'get');
        if (is_array($params_object->items))
        {
            $this->items_total = count($params_object->items);
        }
        else
        {
            $this->items_total = $params_object->items;
        }
        $this->items_per_page = ((isset($params_object->items_per_page)) ? $params_object->items_per_page : 15);
        $this->active_page = ((isset($params_object->active_page)) ? $params_object->active_page : null);
        $this->prepare();
    }

    public function __get($var)
    {
        if (isset($this->$var))
        {
            return $this->$var;
        }
        parent::__get($var);
    }

    /**
     * outputs a list of list links, to reflect paging
     *
     * @access public
     * @return void
     */
    public function render()
    {
        $this->prepare();
        $this->pager_strategy->render();
    }

    /**
     * returns rather than renders the pager
     *
     * @access public
     * @return string
     */
    public function getHtml()
    {
        $this->prepare();
        return $this->pager_strategy->getHtml();
    }

    /**
     * a check of whether number of items exceeds items per page
     *
     * @access public
     * @return bool
     */
    public function needsPagination()
    {
        return ($this->numberOfPages() > 1);
    }

    /**
     * makes sure that all needed vars are initialised
     * also makes sure that all vars are passed to the strategy
     *
     * @access protected
     * @todo implement strategy init
     */
    protected function prepare()
    {
        if ($this->_prepared) return;
        $this->_prepared = true;

        $router = new RequestRouter;
        $req = $router->getRequestAndArgs();

        if (!$this->page_url)
        {
            $this->page_url = $req->request_uri;
        }

        if (!$this->active_page)
        {
            $this->active_page = (($page = $this->checkRequestForPageNumber($req)) ? $page : 1);
        }
        $this->cleanPageUrl();

        // need to adjust active page index
        if ($this->active_page > $this->numberOfPages()) {
            $this->active_page = $this->numberOfPages();
        }
        if ($this->active_page < 1) {
            $this->active_page = 1;
        }

        // todo: init strategy object here
        $this->pager_strategy->pager = $this;
        $this->pager_strategy->pages = $this->numberOfPages();
        $this->pager_strategy->active_page = $this->active_page;
    }

    /**
     * removes previous page marker if present
     * updates $this->page_url
     *
     * @access private
     */
    private function cleanPageUrl()
    {
        switch ($this->page_method)
        {
            case "url":
                $term = "(/{$this->page_url_marker}/[0-9]+)";
                break;
            case "get":
                $term = "({$this->page_url_marker}=[0-9]+&?)";
                break;
        }
        $this->page_url = preg_replace("!{$term}!",'', $this->page_url);
    }

    /**
     * checks the request uri, post and get vars for page
     * url marker, depending upon the settings for $page_method
     *
     * @param object $req - request and arguments object
     * @access private
     * @return int|bool
     */
    private function checkRequestForPageNumber($req)
    {
        switch ($this->page_method)
        {
            case 'get':
                if (isset($req->get[$this->page_url_marker]))
                {
                    return intval($req->get[$this->page_url_marker]);
                }
                break;
            case 'url':
                if (preg_match("!/{$this->page_url_marker}/(\d+)!", $req->request_uri, $matches))
                {
                    return intval($matches[1]);
                }
                break;
        }
        return false;
    }

    /**
     * returns total number of pages the pager will show
     *
     * @access protected
     * @return int
     */
    protected function numberOfPages()
    {
        $this->prepare();
        return ceil($this->items_total / $this->items_per_page);
    }

    /**
     * returns a link based on page number, supplied text and title
     *
     * @param int $page
     * @param string $text
     * @param string $title
     * @access public
     * @return string
     */
    public function outputLink($page, $text, $title='')
    {
        switch ($this->page_method)
        {
            case 'url':
                if (strstr($this->page_url, '?'))
                {
                    $url_parts = explode('?', $this->page_url);
                    $url_parts[1] = '?' . $url_parts[1];
                }
                else
                {
                    $url_parts = array($this->page_url, '');
                }
                $url = ((substr($url_parts[0], -1) == '/') ? $url_parts[0] : $url_parts[0] . '/') . "{$this->page_url_marker}/{$page}{$url_parts[1]}";
                break;
            case 'get':
            default:
                $url = $this->page_url .
                    // add an ampersand if the link does not already end on a questionmark or ampersand
                    ((strstr($this->page_url, '?')) ?
                        ((substr($this->page_url,-1) == '&' || substr($this->page_url,-1) == '?') ?
                         '' :
                         "&")
                    : "?") .
                    // add the pagecounter variable
                    "{$this->page_url_marker}={$page}";
                break;
        }
        $url = htmlspecialchars($url, ENT_COMPAT | ENT_QUOTES, 'utf-8');
        return "<a class='page-link' href='{$url}'" . (($title) ? " title='{$title}'" : '') . ">{$text}</a>";
    }

    /**
     * returns a string formatted for the active page
     * i.e. for GET: $page_url_marker=$active_page
     * you can then stick this in links or urls as needed
     *
     * @access public
     * @return string
     */
    public function getActivePageMarker()
    {
        return "{$this->page_url_marker}={$this->active_page}";
    }

    /**
     * returns the subset of an array that represents the currently active page of the pager
     * in case of arrays that are too small, it returns the start of the array
     *
     * @param array $set - array of items to page
     *
     * @access public
     * @return array
     */
    public function getActiveSubset($set)
    {
        if (!is_array($set))
        {
            return array();
        }
        $this->prepare();

        $set_start = ((count($set) > $this->getActiveStart()) ? $this->getActiveStart() : 0);
        $set_length = ((count($set) > ($set_start + $this->items_per_page)) ? $this->items_per_page : count($set) - $set_start);
        return array_slice($set, $set_start, $set_length);
    }

    /**
     * returns the first item number (zero-based)
     *
     * @access public
     * @return int
     */
    public function getActiveStart()
    {
        return ($this->active_page - 1) * $this->items_per_page;
    }

    /**
     * returns the length of the active subset
     *
     * @access public
     * @return int
     */
    public function getActiveLength()
    {
        return ((($this->items_total - $this->getActiveStart()) > $this->items_per_page) ? $this->items_per_page: $this->items_total - $this->getActiveStart());
    }

    /**
     * returns number of items to page
     *
     * @access public
     * @return int
     */
    public function getTotalCount()
    {
        return $this->items_total;
    }

    /**
     * returns the offset of the first item in the active subset
     *
     * @access public
     * @return int
     */
    public function getOffset()
    {
        return ($this->active_page - 1) * $this->items_per_page;
    }
}
