<?php
/*
Copyright (c) 2007-2009 BeVolunteer

This file is part of BW Rox.

BW Rox is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

BW Rox is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, see <http://www.gnu.org/licenses/> or 
write to the Free Software Foundation, Inc., 59 Temple Place - Suite 330, 
Boston, MA  02111-1307, USA.
*/
?>

<p><?php echo $words->getFormatted("LastCommentsExplanation",$iiMax) ; ?></p>

<table class="full">
    <colgroup>
        <col width="12%" />
        <col width="12%" />
        <col width="12%" />
    </colgroup>


    <?php
    for ($ii = 0; $ii < $iiMax; $ii++) {
        $c = $data[$ii];
    ?>
    
    <tr class="<?php echo $styles[$ii%2] ?>">
        <td>
            <a href="members/<?php echo $c->UsernameFrom ?>">
                <img src="members/avatar/<?php echo $c->UsernameFrom ?>" class="framed" height="50px" width="50px" alt="<?php echo $c->UsernameFrom?>" />
            </a><br />
            <a class="username" href="members/<?php echo $c->UsernameFrom ?>"><?php echo $c->UsernameFrom ?></a>
            <a  href="members/<?php echo $c->UsernameFrom ?>/comments" title="<?php echo $words->getFormatted('ViewComments'); ?>">(<?php echo $c->FromNbComment ?>)</a><br />
            <?php echo $c->CountryNameFrom ?>
        </td>
        <td>
            <p class="<?php echo $c->Quality ?>"><img src="images/icons/tango/22x22/go-next.png" alt="comment to" /><br />
                <?php echo $words->getFormatted('CommentQuality_'.$c->Quality); ?>
            </p>
            <span class="small"><?php echo MOD_layoutbits::ago($c->unix_updated);?></span>
        </td>
        <td>
            <a href="members/<?php echo $c->UsernameTo ?>"><img src="members/avatar/<?php echo $c->UsernameTo?>" class="framed" height="50px" width="50px" alt="<?php echo $c->UsernameTo ?>" /></a><br />
            <a class="username" href="members/<?php echo $c->UsernameTo ?>"><?php echo $c->UsernameTo ?></a>
            <a href="members/<?php echo $c->UsernameTo ?>/comments" title="<?php echo $words->getFormatted('ViewComments'); ?>">(<?php echo $c->ToNbComment ?>)</a><br />
            <?php echo $c->CountryNameTo ?>
        </td>
        <td>
            <p><em><?php echo $c->TextWhere ?></em></p>
            <p><?php echo $c->TextFree ?></p>
            <p class="float_right">
                <?php
                if (empty($c->IdCommentHasVote)) { // If there is not yet any vote from teh current member for this comment
                    echo "<a  href=\"lastcomments/vote/",$c->IdComment,"\" title=\"".$words->getBuffered("VoteCommentIsSignificantExplanation")."\">",$words->getBuffered("VoteCommentIsSignificant"),"</a>" ;
                }
                else {
                    echo "<a  href=\"lastcomments/voteremove/",$c->IdComment,"\" title=\"".$words->getBuffered("VoteCommentIsSignificantExplanation")."\">",$words->getBuffered("VoteCommentIsSignificantRemove"),"</a>" ;
                }

        //      echo " \$this->BW_Right->hasRight(\"Comments\")=",$this->BW_Right->hasRight("Comments") ;
                if ( ($this->BW_Right->hasRight("Comments","UdpateComment"))  or ($this->BW_Right->hasRight("Comments","AdminComment"))){
                    echo "<br /><a  href=\"bw/admin/admincomments.php?action=editonecomment&IdComment=",$c->IdComment."\" title=\"modify this comment with admin rights\">edit</a>" ;
                }
                ?>
            </p>
        </td>
    </tr>
    <?php
    }
    ?>
</table>
