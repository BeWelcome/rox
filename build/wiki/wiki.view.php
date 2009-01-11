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
              <div id="teaser_l">
                <h1><a href="wiki"><?php echo $words->getFormatted('WikiTitle'); ?></a></h1>
              </div>
              <div id="teaser_r">
                <p><?php echo $words->getFormatted('WikiIntroduction'); ?></p>
              </div>
        </div>
<?php
    }
    public function userbar()
    {
        $words = new MOD_words();
?>
        <h3><?php echo $words->getFormatted('Actions'); ?></h3>
        <ul>
              <li>
                <a href="wiki"><?php echo $words->getFormatted('WikiFrontPage'); ?></a>
              </li>
              <li>
                <a href="wiki/NewestPages"><?php echo $words->getFormatted('WikiNewestPages'); ?></a>
              </li>
        </ul>
<?php
    }

}
?>
