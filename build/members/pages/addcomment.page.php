<?php


class AddCommentPage extends ProfilePage
{
    
    protected function getSubmenuActiveItem()
    {
        return 'commmentsadd';
    }
    
    // checkCommentForm - NOT FINISHED YET !
    protected function checkCommentForm(&$vars)
    {
        $errors = array();
        
        $syshcvol = PVars::getObj('syshcvol');
        $max = count($syshcvol->LenghtComments);
        $tt = $syshcvol->LenghtComments;
        $LenghtComments = "";
        for ($ii = 0; $ii < $max; $ii++) {
            $var = $tt[$ii];
            if (isset ($vars["Comment_" . $var])) {
                if ($LenghtComments != "")
                    $LenghtComments = $LenghtComments . ",";
                $LenghtComments = $LenghtComments . $var;
            }
        }
        
        // sample!
        if (empty($vars['geonameid']) || empty($vars['countryname'])) {
            $errors[] = 'SignupErrorProvideLocation';
        }
        
        $TCom = $this->member->get_comments_commenter($_SESSION['IdMember']);
		
		$mReceiver= $this->getMemberWithId($vars['IdMember']);

        // Mark if an admin's check is needed for this comment (in case it is "bad")
		$AdminAction = "NothingNeeded";
		if ($vars['Quality'] == "Bad") {
			$AdminAction = "AdminCommentMustCheck";
            // notify OTRS
            //Load the files we'll need
            // require_once "bw/lib/swift/Swift.php";
            // require_once "bw/lib/swift/Swift/Connection/SMTP.php";
            // require_once "bw/lib/swift/Swift/Message/Encoder.php";
            // $swift =& new Swift(new Swift_Connection_SMTP("localhost"));
			// $subj = "Bad comment from  " .$mCommenter->Username.  " about " . fUsername($IdMember) ;
			// $text = "Please check the comments. A bad comment was posted by " . $mCommenter->Username.  " about " . fUsername($IdMember) . "\n";
			// $text .= $mCommenter->Username . "\n" . ww("CommentQuality_" . $Quality) . "\n" . GetStrParam("TextWhere") . "\n" . GetStrParam("Commenter");
			// bw_mail($_SYSHCVOL['CommentNotificationSenderMail'], $subj, $text, "", $_SYSHCVOL['CommentNotificationSenderMail'], $defLanguage, "no", "", "");
		}
		if (!isset ($TCom->id)) {
			$str = "
INSERT INTO
comments (IdToMember,IdFromMember,Lenght,Quality,TextWhere,TextFree,AdminAction,created)
values (" . $vars['IdMember'] . "," . $_SESSION['IdMember'] . ",'" . $LenghtComments . "','" . $vars['Quality'] . "','" . $vars['TextWhere'] . "','" . $vars['TextFree'] . "','" . $AdminAction . "',now())";

			MOD_log::get()->write("Adding a comment quality <b>" . $vars['Quality'] . "</b> on " . $mReceiver->Username, "Comment");
			$qry = $this->dao->query($str);
            if(!$qry) $return = false;
		    $TCom->id = mysql_insert_id();
		} else {
			MOD_log::get()->write("Updating comment on " . $mReceiver->Username, "Comment");
			$str = "update comments set AdminAction='" . $AdminAction . "',IdToMember=" . $vars['IdMember'] . ",IdFromMember=" . $_SESSION['IdMember'] . ",Lenght='" . $LenghtComments . "',Quality='" . $vars['Quality'] . "',TextFree='" . $vars['TextFree'] . "' where id=" . $TCom->id;
			$qry = $this->dao->query($str);
            if(!$qry) $return = false;
		}
    }
    
    
}


?>