<?php
/**
 * blog controller
 *
 * @package blog
 * @author The myTravelbook Team <http://www.sourceforge.net/projects/mytravelbook>
 * @copyright Copyright (c) 2005-2006, myTravelbook Team
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License (GPL)
 * @version $Id: blog.ctrl.php 56 2006-06-21 13:53:57Z roland $
 */
class ShoutsController extends PAppController {
    private $_model;
    private $_view;
    
    public function __construct() {
        parent::__construct();
        $this->_model = new Shouts();
        $this->_view =  new ShoutsView($this->_model);
    }
    
    public function __destruct() {
        unset($this->_model);
        unset($this->_view);
    }
    
    public function index()
    {
        $P = PVars::getObj('page');
        $vw = new ViewWrap($this->_view);
        $cw = new ViewWrap($this);
        
        // index is called when http request = ./blog
        if (PPostHandler::isHandling()) {
            return;
        }
        $request = PRequest::get()->request;
        $User = APP_User::login();
        if (!isset($request[1]))
            $request[1] = '';
        if ($request[1] == 'moveshoutsfromgallery')
            $P->content = $this->_model->moveShoutsFromGallery();
        return $P;
    }
    
    public function shoutsList($application, $object_id) 
    {
        $this->_view->showShoutsList($application, $object_id);
    }

}
?>
