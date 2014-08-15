<?php

use Phinx\Migration\AbstractMigration;

/*****************
 * Class CommentNotificationNewWordcodes
 *
 * Create new wordcodes for comment notification
 *
 * See ticket: 2230
 */

class CommentNotificationNewWordcodes extends Rox\Tools\RoxMigration
{    
    /**
     * Migrate Up.
     */
    public function up()
    {
        $this->AddWordCode('CommentQualityGoodInSentence',
                           'positive',
                           'Qualification of positive comment as adjective: a ... comment');
        $this->AddWordCode('CommentQualityNeutralInSentence',
                           'neutral',
                           'Qualification of neutral comment as adjective: a ... comment');
        $this->AddWordCode('CommentQualityBadInSentence',
                           'negative',
                           'Qualification of negative comment as adjective: a ... comment');    

        $code1 = 'CommentNotificationMessageNew';
        $sentence1 = '<p>Hi %1$s,</p>
<p>%2$s has left you a %3$s comment. The comment says:</p>
<p><i>"%4$s"</i></p>
<p><a href="%5$s">Read more</a> about the comment.<br />
<a href="%6$s">Write or update</a> your comment about %2$s</p>
<p>Have a great time!<br />
The BeWelcome Team</p>
<p>PS. In case you have a problem with this comment please <a href="%7$s">contact us</a></p>';
        $description1 = 'Text of notification for a member who receives a comment';
        $this->AddWordCode($code1, $sentence1, $description1);

        $code2 = 'CommentNotificationMessageUpdate';
        $sentence2 = '<p>Hi %1$s,</p>
<p>%2$s has updated the comment about you. This is now a %3$s comment and says:</p>
<p><i>"%4$s"</i></p>
<p><a href="%5$s">Read more</a> about the comment.<br>
<a href="%6$s">Write or update</a> your comment about %2$s</p>
<p>Have a great time!<br />
The BeWelcome Team</p>
<p>PS. In case you have a problem with this comment please <a href="%7$s">contact us</a></p>';
        $description2 = 'Text of notification for a member when a comment is updated';
        $this->AddWordCode($code2, $sentence2, $description2);
    }

    /**
     * Migrate Down.
     */
    public function down()
    {
        $this->RemoveWordCode('CommentQualityGoodInSentence');
        $this->RemoveWordCode('CommentQualityNeutralInSentence');
        $this->RemoveWordCode('CommentQualityBadInSentence');
        $this->RemoveWordCode('CommentNotificationMessageNew');
        $this->RemoveWordCode('CommentNotificationMessageUpdate');
    }
}