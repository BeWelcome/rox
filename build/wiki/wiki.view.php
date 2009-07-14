<?php
/**
* Wiki view
*
* @package wiki
* @author The myTravelbook Team <http://www.sourceforge.net/projects/mytravelbook>
* @copyright Copyright (c) 2005-2006, myTravelbook Team
* @license http://www.gnu.org/licenses/gpl.html GNU General Public License (GPL)
* @version $Id$
*/

class WikiView extends PAppView {
	private $_model;
	
	public function __construct(Wiki &$model) {
		$this->_model =& $model;
	}
        /* This displays the custom teaser */
    public function teaser()
    {
        $User = APP_User::login();
        $words = new MOD_words();
?>
        <div id="teaser" class="clearfix">
            <h1><a href="wiki"><?php echo $words->getFormatted('WikiTitle'); ?></a></h1>
        </div>
<?php
    }
    public function stylesFullWidth()
    {
		 echo "<link rel=\"stylesheet\" href=\"styles/css/minimal/screen/basemod_minimal_col3.css\" type=\"text/css\"/>";
    }

}
?>
