<?php

namespace Rox\Main\Home;

use Rox\Main\Home\HomePage;
use Rox\Main\Home\HomeModel;
use Symfony\Component\Routing\Router;
use Symfony\Component\HttpFoundation\Response;

/**
* dashboard controller
*
* @package Dashboard
* @author Amnesiac84
*/
class HomeController extends \RoxControllerBase
{
    /**
     * @RoxModelBase Rox
     */
    private $_model;

    /**
     * @Router
     */
    public $_router;

// for some things we still need a class-scope view object
    private $_view;


    public function __construct()
    {
        parent::__construct();
        $this->_model = new HomeModel();
    }

    public function __destruct()
    {
        unset($this->_model);
    }

    public function showAction() {
        $page = new HomePage($this->routing);
        return new Response($page->render());
    }
}