<?php


class DebugPage extends RoxPageView
{
    protected function getPageTitle()
    {
        return 'Debug Page - BeWelcome';
    }
    
    protected function getSubmenuItems() {
        $words = $this->getWords();
        return array(
            array('debug', 'debug', 'Debug'),
            array('sqltest', 'debug/sqltest', 'Table Sync tb/bw'),
            array('dbsummary', 'debug/dbsummary', 'See all tables'),
        );
    }
    
    protected function getSubmenuActiveItem() {
        return 'debug';
    }
    
    protected function teaserHeadline()
    {
        echo 'Debug Page';
    }
    
    protected function column_col3()
    {
        echo '
        <h3>PVars Monitor</h3>
        Showing some of the non-sensitive PVars settings
        <ul>';
        foreach (array(
        array('env', 'baseuri'),
        ) as $keys) {
            $key_0 = $keys[0];
            $key_1 = $keys[1];
            echo '
            <li>PVars::getObj("'.$key_0.'")->'.$key_1.' = "'.PVars::getObj($key_0)->$key_1.'"</li>';
        }
        echo '
        </ul>';
        
        echo '
        <h3>$_SYSHCVOL Monitor</h3>
        Showing some of the non-sensitive $_SYSHCVOL settings
        <ul>';
        global $_SYSHCVOL;
        foreach (array(
            'SiteName',
            'MainDir',
            'EmailDomainName',
            'MessageSenderMail',
            'CommentNotificationSenderMail',
            'NotificationMail',
            'ferrorsSenderMail',
            'SignupSenderMail',
            'UpdateMandatorySenderMail',
            'AccepterSenderMail',
            'FeedbackSenderMail',
            'TestMail',
            'DISABLEERRORS',
        ) as $key) {
            if (isset($_SYSHCVOL[$key])) {
                echo '
                <li>$_SYSHCVOL["'.$key.'"] = "'.$_SYSHCVOL[$key].'"</li>';
            } else {
                echo '
                <li>$_SYSHCVOL["'.$key.'"] not set.</li>';
            }
        }
        echo '
        </ul>
        
        <br>
        
        <form action="debug" method="post">
        <input name="farbe" value="what color"/>
        <input type="submit" class="button" value="Submit"/>
        </form>
        ';
    }
}


?>