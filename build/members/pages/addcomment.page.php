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
        $member = $this->member;
        $profilemember = $this->profilemember;
        $Username = $profilemember->Username;
        $words = new MOD_words();
        $TCom = $this->model->get_comments_commenter($member->id);
        echo "<h1>",$words->get("AddComments"),"</h1>"; 
    	// Display the previous comment if any
    	$ttLenght = array ();
        $this->LastComments();

	// Display the form to propose to add a comment	
	echo "<form method=\"post\" name=\"addcomment\" OnSubmit=\"return DoVerifSubmit('addcomment');\">\n";
	?><table valign="center" style="font-size:12;"><?php
	echo "<tr><td colspan=2><h3>", $words->get("CommentQuality",$Username),"</h3><br />",$words->get("RuleForNeverMetComment"),"</td>";

	echo "<tr><td><select name=Quality>\n";
	echo "<option value=\"Neutral\" selected >"; // by default
	echo $words->get("CommentQuality_Neutral"), "</option>\n";

	echo "<option value=\"Good\"";
	if ($TCom->Quality == "Good")
		echo " selected ";
	echo ">", $words->get("CommentQuality_Good"), "</option>\n";

	echo "<option value=\"Bad\"";
	if ($TCom->Quality == "Bad")
		echo " selected ";
	echo ">", $words->get("CommentQuality_Bad"), "</option>\n";
	echo "</selected></td>";
	echo "<td><p class=\"grey\">", $words->get("CommentQualityDescription", $Username, $Username, $Username), "</p></td></tr>";

	$syshcvol = PVars::getObj('syshcvol');
	$tt = $syshcvol->LenghtComments;
	$max = count($tt);
	echo "<tr><td colspan=2><h3>", $words->get("CommentLength"), "</h3></td></tr>";
	echo "<tr><td><table valign=center style=\"font-size:12;\">";
	for ($ii = 0; $ii < $max; $ii++) {
	    $chkName = "Comment_" . $tt[$ii];
		echo '<tr><td><label for="' . $chkName . '">' . $words->get($chkName) . "</td>";
	    echo '<td><input type="checkbox" id="' . $chkName . '" name="' . $chkName . '"';
	    if (in_array($tt[$ii], $ttLenght))
	        echo " checked ";
	    echo ">\n</td>\n";
	}
	?>
</table></td>
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
<?php
	echo "function DoVerifSubmit(nameform) {\n";
	echo "nevermet=document.forms[nameform].elements['Comment_NeverMetInRealLife'].checked;\n";
	echo "	if ((document.forms[nameform].elements['Quality'].value!='Negative') && (nevermet)) {\n";
	echo "	   alert('",addslashes($words->get("RuleForNeverMetComment")),"');\n";
	echo "	   return (false);\n";
	echo "	}\n";
	echo "	return(true);\n";
	echo "}\n";
	?>
	</script>
	</div>
<?php
    }
    
    protected function LastComments()
    {
        $member = $this->member;
        $TCom = $this->model->get_comments_commenter($member->id);
        if (isset ($TCom->Quality)) { // if there allready a comment display it
		echo "<h3>",$words->get("PreviousComments"),"</h3>";    
		echo "<table valign=center style=\"font-size:12;\" class=\"framed highlight\">";
		echo "<tr><th colspan=3>", LinkWithUsername($Username), "</th>";
		$color = "black";
		if ($TCom->Quality == "Good") {
			$color = "#808000";
		}
		if ($TCom->Quality == "Bad") {
			$color = "red";
		}
		echo "<tr><td><strong>", $TCom->Commenter, "</strong><br />";
		echo "<em>", $TCom->TextWhere, "</em>";
		echo "<br /><font color=$color>", $TCom->TextFree, "</font>";
		echo "</td>";
		$ttLenght = explode(",", $TCom->Lenght);
		echo "<td width=\"30%\">";
		for ($jj = 0; $jj < count($ttLenght); $jj++) {
			if ($ttLenght[$jj]=="") continue; // Skip blank category comment : todo fix find the reason and fix this anomaly
			echo $words->get("Comment_" . $ttLenght[$jj]), "<br />";
		}

		echo "</td></table>\n";
		echo "<br />\n";
        }
    }
    
}


?>