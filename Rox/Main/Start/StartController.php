<?php

namespace Rox\Main\Start;

use Rox\Main\Start\StartModel;
use Rox\Main\Start\StartPage;
use Symfony\Component\Routing\Router;
use Symfony\Component\HttpFoundation\Response;

/**
* dashboard controller
*
* @package Dashboard
* @author Amnesiac84
*/
class StartController extends \RoxControllerBase
{

    /**
     * @RoxModelBase
     */
    private $_model;

    /**
     * @Router
     */
    public $routing;

    public function __construct()
    {
        parent::__construct();
        $this->_model = new StartModel();
    }

    public function __destruct()
    {
        unset($this->_model);
    }

    /**
     * Shows the public start page
     *
     * @return StartPage
     */
    public function showAction() {
        $page = new StartPage($this->routing);
        return new Response($page->render());
    }
}