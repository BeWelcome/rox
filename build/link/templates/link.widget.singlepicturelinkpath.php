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
/**
 *
 * @author fvanhove@gmx.de, Andreas (lemon-head)
 * @see /htdocs/bw/layout/menus.php method VolMenu
 *
 * Some of these admin tools could allow severe violations of member privacy.
 * We should take care of that as soon as possible!
 * JeanYves : they are protected by the right system, the only important question IMHO is to
 * discuss case by case, if needed, the relevance and the need for displaying the information then provide
 * still IMHO, this need is real and the displayed data are not, in the context they are used, privacy abuse
 */
 
 /**
  * $words -- a way to get translated content for the page
  * totype: the type of connection (good/neutral/family...) from the left to the right member
  * reversetype: type of connection from the right to the left member
  **/
 
    $words = new MOD_words();
?>
    <div class="float_right">
      <div class="info">
        <h3><?php  echo $words->get('HowDoIKnow');?></h3>
            <?php foreach ($link->linkpath as $row) {
            ?>      
        <div class="clearfix">          
            <?php   foreach ($row as $e) {
            ?>
            <div class="float_left" style="padding-right: 15px">
                <p>
                <?php if (isset($e['totype']) && $e['totype'][0] != '0') {?> 
                    <img src="images/icons/icons1616/icon_next.png" /><br>
                    <?php echo implode(' - ',$e['totype']); ?>
                <?php }?>
                </p>
                <p>
                <?php if (isset($e['reversetype']) && $e['reversetype'][0] != '0') {?> 
                    <img src="images/icons/icons1616/icon_previous.png" /><br>
                    <?php echo implode(' - ',$e['reversetype']); ?>
                <?php }?>
                </p>
                
            </div> <!-- float_left -->
            <div class="float_left" style="padding-right: 15px">
                <p class="center">
                    <?php echo '<a href="bw/member.php?cid='.$e['memberdata']->Username.'">'.$e['memberdata']->Username.'</a>' ?><br />
                    <?php echo MOD_layoutbits::PIC_50_50($e['memberdata']->Username,'',$style='framed') ?><br />
                    <span class="small grey"><?php echo $e['memberdata']->Country; ?></span>
                </p>
            </div> <!-- float_left -->

    <?php } ?>
        </div> <!-- clearfix -->
<?php } ?>      
      </div> <!-- info index -->
  </div> <!-- index row2 -->
