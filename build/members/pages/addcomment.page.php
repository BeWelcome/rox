<?php


class AddCommentPage extends MemberPage
{
    
    protected function leftSidebar()
    {
    	$words = $this->getWords();
        $member = $this->member;
        ?>
          <h3><?=$words->get('Actions')?></h3>
          <ul class="linklist">
            <li class="icon contactmember16">
              <a href="contactmember.php?cid=<?=$member->id?>"><?=$words->get('ContactMember');?></a>
            </li>
            <li class="icon addcomment16">
              <a href="members/<?=$member->Username?>/comments/add"><?=$words->get('addcomments');?></a>
            </li>
          </ul>
        <?php
    }
    
    
    protected function getSubmenuActiveItem()
    {
        return 'comments';
    }
    
    
    protected function column_col3()
    {
        $words = new MOD_words();
        
        $commenter = MOD_member::getUsername();
        $member = $this->member;
        $Username = $member->Username;
        
        $TCom = $this->member->get_comments_commenter($_SESSION['IdMember']);
        $TCom = $TCom[0];
        $page_url = PVars::getObj('env')->baseuri . implode('/', PRequest::get()->request);
        
        $formkit = $this->layoutkit->formkit;
        $callback_tag = $formkit->setPostCallback('MembersController', 'commentCallback');
        ?>
        
        <h1><?=$words->get("AddComments")?></h1>
        
        <?php
    	// Display the previous comment if any
    	$ttLenght = array ();
        $this->LastComments();
        
        // Display the form to propose to add a comment	
        ?>
        <form method="post" name="addcomment" OnSubmit="return DoVerifSubmit('addcomment');">
        <?=$callback_tag ?>
        <input name="IdMember" value="<?=$member->id?>" type="hidden" />
            <table valign="center" >
              <tr>
                <td colspan=2><h3><?=$words->get("CommentQuality",$Username)?></h3><br /><?=$words->get("RuleForNeverMetComment")?></td>
              </tr>
              <tr>
                <td>
                    <select name="Quality">
                        <option value="Good"
                        <?=(isset($TCom->comQuality) && $TCom->comQuality == "Good") ? " selected " : ""?>
                        >
                        <?=$words->get("CommentQuality_Good")?></option>
                        <option value="Neutral"
                        <?=(!isset($TCom->comQuality) || $TCom->comQuality == "Neutral") ? " selected " : ""?>
                        >
                        <?=$words->get("CommentQuality_Neutral")?></option>
                        <option value="Bad"
                        <?=(isset($TCom->comQuality) && $TCom->comQuality == "Bad") ? " selected " : ""?>
                        >
                        <?=$words->get("CommentQuality_Bad")?></option>
                    </selected>
                </td>
                <td>
                    <p class="grey"><?=$words->get("CommentQualityDescription", $Username, $Username, $Username)?></p>
                </td>
              </tr>
        <?php
        	$syshcvol = PVars::getObj('syshcvol');
        	$tt = $syshcvol->LenghtComments;
        	$max = count($tt);
        ?>
        	<tr>
            <td colspan=2>
                <h3><?$words->get("CommentLength")?></h3>
            </td>
            </tr>
        	<tr>
            <td>
            <table valign=center >
            <?php
        	for ($ii = 0; $ii < $max; $ii++) {
        	    $chkName = "Comment_" . $tt[$ii];
        		echo '<tr><td><label for="' . $chkName . '">' . $words->get($chkName) . "</td>";
        	    echo '<td><input type="checkbox" id="' . $chkName . '" name="' . $chkName . '"';
        	    if (in_array($tt[$ii], $ttLenght))
        	    echo " checked ";
        	    echo ">\n</td>\n";
        	}
        	?>
            </table>
            </td>
            <td>
            <p class="grey"><?php echo $words->get("CommentLengthDescription", $Username, $Username, $Username) ?></p>
            </td>
        </tr>
        <tr>
            <td colspan="2"><h3><label for="TextWhere"><?php echo $words->get("CommentsWhere") ?></label></h3></td>
        </tr>
        <tr>
            <td><textarea name="TextWhere" id="TextWhere" cols="40" rows="3"></textarea></td>
            <td><p class="grey"><?php echo $words->get("CommentsWhereDescription", $Username) ?></p></td>
        </tr>
        <tr>
            <td colspan="2"><h3><label for="Commenter"><?php echo $words->get("CommentsCommenter") ?></label></h3></td>
        </tr>
        <tr>
            <td><textarea name="TextFree" id="TextFree" cols="40" rows="8"></textarea></td>
            <td style="vertical-align=top"><p class="grey"><?php echo $words->get("CommentsCommenterDescription", $Username) ?></p></td>
        </tr>
        <tr><td align="center" colspan="2"><input type="hidden" value="<?php echo $IdMember?>" name="cid">
        	<input type="hidden" name="action" value="add">
         	<input type="submit" id="submit" name="valide" value="submit"></td>
        </tr>
        </table>
        </form>

        <script type="text/javascript">
        	function DoVerifSubmit(nameform) {
        	nevermet=document.forms[nameform].elements['Comment_NeverMetInRealLife'].checked;
        		if ((document.forms[nameform].elements['Quality'].value!='Negative') && (nevermet)) {
        		   alert('",addslashes($words->get("RuleForNeverMetComment")),"');
        		   return (false);
        		}
        		return(true);
        	}
        </script>
        </div>
        <?php
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
			$qry = $this->dao->query($str);
            if(!$qry) $return = false;
		    $TCom->id = mysql_insert_id();
		} else {
			$str = "update comments set AdminAction='" . $AdminAction . "',IdToMember=" . $vars['IdMember'] . ",IdFromMember=" . $_SESSION['IdMember'] . ",Lenght='" . $LenghtComments . "',Quality='" . $vars['Quality'] . "',TextFree='" . $vars['TextFree'] . "' where id=" . $TCom->id;
			$qry = $this->dao->query($str);
            if(!$qry) $return = false;
		}
    }
    
    
    protected function LastComments()
    {
        $words = $this->getWords();
        $ww = $this->ww;
        $member = $this->member;
        $comments = $member->get_comments_commenter($_SESSION['IdMember']);
        // var_dump($TCom);

        if (isset ($comments[0]->comQuality)) { // if there allready a comment display it
        ?>
<?php
        $c = $comments[0];
		$quality = "neutral";
		if ($c->comQuality == "Good") {
			$quality = "good";
		}
		if ($c->comQuality == "Bad") {
			$quality = "bad";
		}
        $tt = explode(",", $c->Lenght);
    // var_dump($c);
?>
        <style>
        div.neutral a.username{
			color: #000000;
		}
        div.good a.username{
			color: green;
		}
        div.bad a.username{
			color: red;
		}
        </style>
    		<h3><?=$words->get("PreviousComments")?></h3>  
  <div class="subcolumns">

    <div class="c75l" >
      <div class="subcl <?=$quality?>" >
        <a href="people/<?=$c->Username?>"  title="See admin's profile" >
           <img class="float_left framed"  src="/"  height="50px"  width="50px"  alt="Profile" >
        </a>
        <div style="display: block; float: left; width: 70%">
        <p>
          <strong> from <a href="people/<?=$c->Username?>" class="username"><?=$c->Username?></a> </strong>
        </p>
        <p>
          <small><?=$c->TextFree?></small>
        </p>
        <p>
          <em><?=$c->TextWhere?></em>
        </p>
        <hr />
        </div>
      </div>
    </div>
    <div class="c25r" >
      <div class="subcr" >
        <ul class="linklist" >
            <li>
                <?php
                    for ($jj = 0; $jj < count($tt); $jj++) {
                        if ($tt[$jj]=="") continue; // Skip blank category comment : todo fix find the reason and fix this anomaly
                        echo "                    <li>", $words->get("Comment_" . $tt[$jj]), "</li>\n";
                    }
                ?>
                <?=$c->Lenght?>
            </li>
            <li>
                <a href="feedback.php?IdCategory=4"><img src="images/icons/error.png" alt="Report a problem with this comment" ></a>
            </li>
        </ul>
      </div>
    </div>
  </div>
        <?php
        }
    }
    
}


?>