<?php

namespace Rox\Main\Start;

use Rox\Framework\Controller;
use Symfony\Component\HttpFoundation\Response;

/**
* dashboard controller
*
* @package Dashboard
* @author Amnesiac84
*/
class StartController extends Controller
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
        $page = new StartPage($this->getRouting());
        $page->addParameters([ 'stats' => $this->_model->getStatistics()]);
        return new Response($page->render());
    }
}