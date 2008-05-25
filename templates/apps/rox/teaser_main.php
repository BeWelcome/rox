<?php
/*

Copyright (c) 2007 BeVolunteer

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

$LayoutBits = new MOD_layoutbits();
$ToggleDonateBar = $LayoutBits->getParams('ToggleDonateBar');

?>

     <div id="teaser" class="clearfix teaser_main">
        <table>
        <tr>
        <td style="vertical-align: top">
            <h2><?=$words->getFormatted('HelloUsername', $_SESSION['Username'])?></h2>
            <div class="floatbox">
                <img src="<?=$thumbPathMember?>" id="MainUserpic" class="float_left" alt="ProfilePicture"/>
                <div class="floatbox" style="width: 300px">
                      <p class="floatbox"><a href="bw/mymessages.php"><img src="images/icons/icons1616/icon_contactmember.png" alt="Messages"/><?=$_mainPageNewMessagesMessage?></a></p>
                      <p class="floatbox"><a href="bw/viewcomments.php"><img src="images/icons/icons1616/icon_addcomments.png" alt="Comments"/> <?=$words->get('MainPageNewComments')?></a></p>
                      <p class="floatbox"><a href="bw/myvisitors.php"><img src="images/icons/icons1616/icon_myvisitors.png" alt="Visitors"/> <?=$words->get('MainPageNewVisitors')?></a></p>
                </div>
            </div>
        </td>
        <td></td>
        <td>
<?php
    /*
    **   deactivated for now
    echo "                        <div id=\"mapsearch\">\n";
    echo "                        <form>\n";
    echo "                              <fieldset> \n";
    echo "                              <input type=\"text\" name=\"searchtext\" size=\"10\" maxlength=\"30\" id=\"text-field\" />\n";
    echo "                              <input type=\"hidden\" name=\"action\" value=\"mapsearch\" />\n";
    echo "                              <input type=\"button\" value=\"Search\" class=\"button\" id=\"submit-button\" /><br />\n";
    echo "                              Search the map\n";
    echo "                            </fieldset>\n";
    echo "                        </form>\n";
    echo "                        </div>\n";
    */

if ($ToggleDonateBar) {
    // return horizontal donation bar
    require TEMPLATE_DIR.'apps/rox/donatebar_hor.php';
} else {
    /* Instead we use this temporary solution */
?>
                       <div id="mapsearch">
                        <form action="#">
                             <fieldset>
                                <h2 style="margin-top: 10px;"><a href="searchmembers/index"><?=$words->get('FindMembers')?></a></h2>
                          </fieldset>
                       </form>
                       </div>
<?php } ?>
        </td>
        </tr>
        </table>
    </div> <!-- teaser -->
