<?php
/** 
 * Online Controller
 * 
 * @package online
 * @author lupochen
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License (GPL)
 * @version $Id$
 */


class OnlineController extends RoxControllerBase
{

    public function index()
    {
        $model = new OnlineModel();

        $loggedInMember = $model->getLoggedInMember();
        if (!$loggedInMember) {
            return $this->redirectAbsolute(PVars::getObj('env')->baseuri);
        }
        $rights = $loggedInMember->getOldRights();
        if (array_key_exists('SafetyTeam', $rights)) {
            $page = new OnlinePage();
            $page->model = $model;
            return $page;
        }
        return $this->redirectAbsolute(PVars::getObj('env')->baseuri);
    }
    
    protected function createOnlineOtherPage($args)
    {
        return new OnlineOtherPage();
    }
    
}
?>