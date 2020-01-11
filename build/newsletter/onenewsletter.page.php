<?php


/**
 * Hello universe page.
 * This is a base class for other pages in the same application.
 * We redefine the methods of RoxPageView to configure this page.
 * Here we make some more decorations.
 *
 * @package
 * @author JeanYves
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License (GPL)
 * @version $Id$
 */
class OneNewsLetterPage extends RoxPageView  /* HelloUniversePage doesn't work! */
{
    /**
    Constructor

    @$_Data has been previously filled with the dynamic data to display

    **/
    public function __construct($_Data) {
        parent::__construct();
        $this->Data=$_Data;
    }

    /**
     * content of the middle column - this is the most important part
     */
    protected function column_col3()
    {
        // $this->Data->Lang is set to the language to be displayed.
        //Search for it in the broadcast array and update the %s/%username% placeholder
        /* \todo Replace username and %s correctly */
        $member = $this->getWords()->getInLang('member', $this->Data->Lang);
        $username = $this->session->get('Username', $member);
        $body = "";
        for($i=0;$i<count($this->Data->BroadCast->Lang);$i++)
        {
            if($this->Data->BroadCast->Lang[$i]->ShortCode === $this->Data->Lang) {
                $body = $this->Data->BroadCast->Lang[$i]->Sentence;
            }
        }
        $body = str_replace('%s', $username, $body);
        $body = str_replace('%username%', $username, $body);
        $body = str_replace('%UserName%', $username, $body);
        $body = str_replace('%Username%', $username, $body);
        $body = str_replace('%%', '%', $body);
        if ($this->Data->CountSent > 10) {
            echo '<p>Sent to '.$this->Data->CountSent,' members</p>' ;
            echo '<p>',$body,'</p>' ;
        }
        else {
            echo '<p>',$body,'</p>' ;
        }

        if ($this->Data->CountToSend > 0)
            echo '<p>Still to be sent to '.$this->Data->CountToSend,' members</p>' ;
    }

    /**
     * which item in the top menu should be activated when showing this page?
     * Let's use the 'getanswers' menu item for this one.
     * @return string name of the menu
     */
    protected function getTopmenuActiveItem() {
        return 'getanswers';
    }

    /**
     * configure the teaser (the content of the orange bar)
     */
    protected function teaserHeadline() {
        echo $this->getWords()->get('BroadCast_Title_'.$this->Data->LetterName);
    }

    /**
     * configure the page title (what appears in your browser's title bar)
     * @return string the page title
     */
    protected function getPageTitle() {
		$ss=$this->getWords()->getInLang('BroadCast_Title_'.$this->Data->LetterName,$this->Data->Lang);
		return($ss) ;
    }

    /**
     * configure the sidebar
     */
    protected function leftSidebar()
    {

    }
} // end of OneNewsLetterPage

?>
