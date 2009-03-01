<?php
    $words = new MOD_words();
    $ww = $this->ww;
    $layoutbits = new MOD_layoutbits();
    
    $commenter = MOD_member::getUsername();
    $member = $this->member;
    $Username = $member->Username;
    
    $TCom = $this->member->get_comments_commenter($_SESSION['IdMember']);
    $TCom = (isset($TCom[0])) ? $TCom[0] : false;
    $page_url = PVars::getObj('env')->baseuri . implode('/', PRequest::get()->request);
    
    $formkit = $this->layoutkit->formkit;
    $callback_tag = $formkit->setPostCallback('MembersController', 'commentCallback');
    
    // for previous comments
    $ttLenght = array ();
    $comments = $member->get_comments_commenter($_SESSION['IdMember']);
    ?>
    
    <h2><?=$words->get("AddComments")?></h2>
    
    <?php
    // Display the previous comment if any
    require_once 'comment_template.php';
    
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