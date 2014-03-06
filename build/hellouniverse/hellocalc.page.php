<?php


/**
 * Hello universe page with a form that sends POST data.
 * Have a look at the superclasses for the layout definitions..
 *
 * @package hellouniverse
 * @author Andreas (lemon-head)
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License (GPL)
 * @version $Id$
 */
class HellouniverseCalculatorPage extends HellouniversePage
{
    protected function column_col3()
    {
        $page_url = PVars::getObj('env')->baseuri . implode('/', PRequest::get()->request);
        
        echo '
<h3>Hellouniverse Calculator!</h3>
        ';
        
        if (!$mem_redirect = $this->layoutkit->formkit->getMemFromRedirect()) {
            // nothing, this is a fresh calculator page
        } else {
            // result from calculation
            echo '
<p>
Result from last calculation: '.$mem_redirect->x.' + '.$mem_redirect->y.' = '.$mem_redirect->z.'
</p>
            ';
        }
        
        echo '
<p>
<form method="POST" action="'.$page_url.'">
'.$this->layoutkit->formkit->setPostCallback('HellouniverseController', 'calculatorCallback').'
<input name="x"/> + <input name="y"/> = ?
<input type="submit" class="button" value="send"/>
</form>
</p>
        ';
    }
    
    
    protected function teaserHeadline()
    {
        echo 'Hellouniverse Calculator';
    }
    
    
    protected function getPageTitle() {
        return 'Calculator - BW Hellouniverse';
    }
}


?>