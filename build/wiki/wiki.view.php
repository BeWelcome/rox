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

    public function userbar()
    {
        $words = new MOD_words();
?>
    <div class="row">
    </div>
    <div class="row">
        <h3><?php echo $words->getFormatted('WikiPages'); ?></h3>
        <ul>
              <li>
                <a href="wiki"><?php echo $words->getFormatted('WikiFrontPage'); ?></a>
              </li>
              <li>
                <a href="wiki/NewestPages"><?php echo $words->getFormatted('WikiNewestPages'); ?></a>
              </li>
              <li>
                <a href="wiki/RecentChanges"><?php echo $words->getFormatted('WikiRecentChanges'); ?></a> <a href="wiki/rss"><img src="images/icons/feed.png" alt="RSS Feed" /></a>
              </li>
              <li>
                <a href="wiki/WikiMarkup"><?php echo $words->getFormatted('WikiMarkup'); ?></a>
              </li>
        </ul>
</div>
        <div class="search-form row">
        <form name="powersearch" action="<?=ewiki_script("", "PowerSearch") ?>" method="GET">
        <input type="hidden" name="id" value="<?=$id?>">
        <input type="text" id="q" name="q" size="15" style="width: 95%"><br />
        <select name="where">
            <option value="content"><?=$words->getSilent('WikiSearch_PageTexts') ?></option>
            <option value="id"><?= $words->getSilent('WikiSearch_Titles') ?></option>
            <option value="author"><?= $words->getSilent('WikiSearch_AuthorNames') ?></option>
        </select>
        <input type="submit" value="<?= $words->getSilent('Search') ?>">
        </form>
        </div>
        <div class="row">
        </div>

        <p><?php echo $words->getFormatted('WikiIntroduction'); ?></p>

<?php
    }

}
?>
