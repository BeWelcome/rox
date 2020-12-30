<?php


/**
 * Hello universe page.
 * This is a base class for other pages in the same application.
 * We redefine the methods of RoxPageView to configure this page.
 * Here we make some more decorations.
 *
 * @package newsletter
 * @author JeanYves
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License (GPL)
 * @version $Id$
 */
class NewsletterPage extends RoxPageView
{
    public function __construct($_Data) {
        parent::__construct();
        $this->Data=$_Data;
    }

    /**
     * content of the middle column - this is the most important part
     */
    protected function column_col3()
    {
        $currentLocale = strtolower($this->getSession()->get('lang', 'en'));

        echo "<div class='row mb-2'><div class='col-12'>";
		foreach ($this->Data as $OneLetter) {
			echo "<p><strong>",$this->getWords()->get(strtolower('BroadCast_Title_'.$OneLetter->Name))."</strong>" ;
			foreach ($OneLetter->Lang as $Lang)  {
			    if (strtolower($Lang->ShortCode) === $currentLocale) {
                    echo "<a class='btn btn-sm btn-primary ml-1' href='newsletter/".$OneLetter->Name."/".$Lang->ShortCode."' title='".$Lang->Name."'>".strtolower($Lang->ShortCode)."</a>" ;
                } else {
                    echo "<a class='btn btn-sm btn-secondary ml-1' href='newsletter/".$OneLetter->Name."/".$Lang->ShortCode."' title='".$Lang->Name."'>".strtolower($Lang->ShortCode)."</a>" ;
                }
			}
			echo "</p>" ;
		}
		echo "</div></div>";
    }

    /**
     * which item in the top menu should be activated when showing this page?
     * Let's use the 'getanswers' menu item for this one.
     * @return string name of the menu
     */
    protected function getTopmenuActiveItem() {

    }

    /**
     * configure the teaser (the content of the orange bar)
     */
    protected function teaserHeadline() {
        echo 'BeWelcome news letters';
    }

    /**
     * configure the page title (what appears in your browser's title bar)
     * @return string the page title
     */
    protected function getPageTitle() {
        return $this->getWords()->getFormatted('NewsLetters');
    }

    /**
     * configure the sidebar
     */
    protected function leftSidebar()
    {

    }
}




?>
