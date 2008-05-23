<?php
/** Online
 * 
 * @package online
 * @author lupochen
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License (GPL)
 * @version $Id$
 */

class OnlinePage extends PageWithActiveSkin
{
    
    protected function getPageTitle()
    {
        return 'BeWelcome - Online members';
    }

    protected function teaserContent()
    {
        $model = new OnlineModel();
        $TMembers=$model->GetMembers() ;
        $TGuests=$model->GetGuests() ;
        $TotMembers=$model->GetTotMembers() ;
        require TEMPLATE_DIR.'apps/online/teaser.php';
    }

    public function column_col3()
    {
        $model = new OnlineModel();
        $words = new MOD_words();
        PVars::getObj('page')->title = $words->getBuffered('WhoIsOnLinePage');
        $TMembers=$model->GetMembers() ;
        $TGuests=$model->GetGuests() ;
        $TotMembers=$model->GetTotMembers() ;
        require TEMPLATE_DIR.'apps/online/showonline.php';
        
    }
    
    public function leftSidebar()
    {
    ?>
        <h2><a class="bigbutton" href="chat" onclick="this.blur();"><span>Enter the chat</span></a></h2>
        <ul>
            <li></li>
        </ul>
    <?php
    }
}


?>