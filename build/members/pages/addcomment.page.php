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
        
        $TCom = $this->model->get_comments_commenter($_SESSION['IdMember']);
        
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
            <table valign="center" >
              <tr>
                <td colspan=2><h3><?=$words->get("CommentQuality",$Username)?></h3><br /><?=$words->get("RuleForNeverMetComment")?></td>
              </tr>
              <tr>
                <td>
                    <select name="Quality">
                        <option value="Good"
                        <?=(isset($TCom->Quality) && $TCom->Quality == "Good") ? " selected " : ""?>
                        >
                        <?=$words->get("CommentQuality_Good")?></option>
                        <option value="Neutral"
                        <?=(!isset($TCom->Quality) || $TCom->Quality == "Neutral") ? " selected " : ""?>
                        >
                        <?=$words->get("CommentQuality_Neutral")?></option>
                        <option value="Bad"
                        <?=(isset($TCom->Quality) && $TCom->Quality == "Bad") ? " selected " : ""?>
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
            <td><p class="grey"><?php echo $words->get("CommentLengthDescription", $Username, $Username, $Username) ?></p></td></tr>
        <tr><td colspan="2"><h3><label for="TextWhere"><?php echo $words->get("CommentsWhere") ?></label></h3></td></tr>
        <tr><td><textarea name="TextWhere" id="TextWhere" cols="40" rows="3"></textarea></td>
            <td><p class="grey"><?php echo $words->get("CommentsWhereDescription", $Username) ?></p></td></tr>
        <tr><td colspan="2"><h3><label for="Commenter"><?php echo $words->get("CommentsCommenter") ?></label></h3></td></tr>
        <tr><td><textarea name="Commenter" id="Commenter" cols="40" rows="8"></textarea></td>
            <td style="vertical-align=top"><p class="grey"><?php echo $words->get("CommentsCommenterDescription", $Username) ?></p></td></tr>

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
    
    protected function LastComments()
    {
        $member = $this->member;
        $TCom = $this->model->get_comments_commenter($_SESSION['IdMember']);

        if (isset ($TCom->Quality)) { // if there allready a comment display it
        ?>
    		<h3><?=$words->get("PreviousComments")?></h3>   
    		<table valign=center class="framed highlight">
    		<tr><th colspan=3><?=LinkWithUsername($Username)?></th>
            <?php
    		$color = "black";
    		if ($TCom->Quality == "Good") {
    			$color = "#808000";
    		}
    		if ($TCom->Quality == "Bad") {
    			$color = "red";
    		}
            ?>
    		<tr><td><strong><?=$TCom->Commenter?></strong><br />
    		<em><?=$TCom->TextWhere?></em>
    		<br /><font color=$color><?=$TCom->TextFree?></font>
    		</td>
    		<?php $ttLenght = explode(",", $TCom->Lenght); ?>
    		<td width="30%">
            <?php
    		for ($jj = 0; $jj < count($ttLenght); $jj++) {
    			if ($ttLenght[$jj]=="") continue; // Skip blank category comment : todo fix find the reason and fix this anomaly
    			echo $words->get("Comment_" . $ttLenght[$jj]), "<br />";
    		}
            ?>
    		</td></table>
    		<br />
        <?php
        }
    }
    
}


?>