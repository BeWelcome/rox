<?php


/**
 * Base class for all pages in messages application
 *
 * @package messages
 * @author Andreas (lemon-head)
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License (GPL)
 * @version $Id$
 */
class MessagesBasePage extends RoxPageView
{
    protected function getStylesheets() {
        $stylesheets = parent::getStylesheets();
        $stylesheets[] = 'styles/YAML/screen/custom/bw_basemod_2col_left.css';
        return $stylesheets;
    }
    
    protected function teaserContent() {
        // &gt; or &raquo; ?
        $username = isset($_SESSION['Username']) ? $_SESSION['Username'] : 'Nobody';
        ?><div id="teaser" class="clearfix">
        <div id="teaser_l1"> 
        <h1><a href="messages"><?=$username ?>'s Messages</a></h1>
        </div>
        </div><?php
    }
    
    public function leftSidebar()
    {
        // FIME: Make the menu work!
        $words = new MOD_words();
    ?>
        <script type="text/javascript" src="script/messaging.js"></script><script type="text/javascript">
        //<![cdata[
        function submitform(actionToDo){  if (window.confirm('<?=$words->get('Do you confirm this action ?')?>')){    submitformsub(actionToDo);  } else {    return false;  }}
        messagelinks = '    <h3><?=$words->get('Action')?></h3>';
        messagelinks += '    <ul class="linklist">';
        messagelinks += '<li class="icon marknospam16"><a href="#" onclick="return submitform' + "('notspam')" + ';"> <?=$words->get('markspam')?></a></li>';
        messagelinks += '<li class="icon markspam16"><a href="#" onclick="return submitform' + "('isspam')" + ';"> <?=$words->get('marknospam')?></a></li>';
        messagelinks += '<li class="icon delete16"><a href="#" onclick="return submitform' + "('delmsg')" + ';"> <?=$words->get('delmessage')?></a></li>';
        //if () {
        messagelinks += '<li><?=$words->get('SelectMessages')?> <a href="#" onclick="SelectMsg' + "('ALL')" + ';return false;"><?=$words->get('SelectAll')?></a> / <a href="#" onclick="SelectMsg' + "('NONE')" + ';return false;"><?=$words->get('SelectNone')?></a></li>';
        //} else {
        messagelinks += '<li><li class="icon reply16"><a href="#" onclick="SelectMsg' + "('ALL')" + ';return false;"><?=$words->get('SelectAll')?></a> / <a href="#" onclick="SelectMsg' + "('NONE')" + ';return false;"><?=$words->get('SelectNone')?></a></li>';
        //}
        messagelinks += '    </ul>';
        document.write(messagelinks);
        //]]>
        </script>

    
        <p><?=$words->getFormatted('ChatPeopleOnlineInvite',MOD_online::get()->howManyMembersOnline()); ?></p>
    <?php
    }
    
    protected function getSubmenuItems()
    {
        $words = $this->getWords();
        $items = array();
        $items[] = array('received', 'messages/received', 'Received');
        $items[] = array('sent', 'messages/sent', 'Sent');
        $items[] = array('spam', 'messages/spam', 'Spam');
        $items[] = array('drafts', 'messages/drafts', 'Drafts');
        return $items;
    }
}





?>