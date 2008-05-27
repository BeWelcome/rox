<?php



/**
 * Base class for all pages in forums application
 *
 * @package forums
 * @author Andreas (lemon-head)
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License (GPL)
 * @version $Id$
 */
class ForumBasePage extends RoxPageView
{
    protected function getStylesheets() {
        $stylesheets = parent::getStylesheets();
        $stylesheets[] = 'styles/YAML/screen/custom/forums.css';
        return $stylesheets;
    }
    
    protected function getTopmenuActiveItem()
    {
        return 'forums';
    }
    
    public function render()
    {
        if (!$this->model) {
            $this->model = new Forums();
        }
        parent::render();
    }
    
    
    protected function teaserHeadline()
    {
        echo 'Forum';
    }
    
    /*
    protected function teaserContent()
    {
        $boards = $this->model->getBoard();
        $topboards = $this->model->getTopLevelTags();
        $request = PRequest::get()->request;
        require TEMPLATE_DIR.'apps/forums/teaser.php';
    }
    */
}



?>