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
 * @author Philipp
 *
 */
 
	$words = new MOD_words();
	$ii = 0;
?>
	<div class="picture-linkpath" id="<?php echo $cssID ?>">
			<?php foreach ($linkpath as $row) {
			?>		
        <div class="floatbox">			
			<?php if (count($row) > 2) { ?>
			<h3><?php  echo $words->get('HowDoIKnow');?></h3>
			<?php foreach ($row as $e) { ?>
			<?php if ($ii_new = $ii++ && ($ii_new != 1 || $ii++ != count($row))) {?> 
			<div class="float_left" style="margin: 15px 5px 0 0; vertical-align: middle">
				<p class="small grey">
				<?php if (isset($e['totype']) && $e['totype'][0] != '0') {?> 
					<img title="<?php echo implode(' - ',$e['totype']); ?>" src="images/icons/arrow_right.png" /><br />
				<?php }?>
				<?php if (isset($e['reversetype']) && $e['reversetype'][0] != '0') {?> 
					<img title="<?php echo implode(' - ',$e['reversetype']); ?>" src="images/icons/icons1616/arrow_left.png" />
				<?php }?>
				</p>	
			</div> <!-- float_left -->
			<?php }?>
            <div class="float_left" style="margin-right: 5px">
                <p class="center">
                <?php
				if (isset($_SESSION['Username']) && $e['memberdata']->Username == $_SESSION['Username']) {
					$username = $words->get('me');
					$myself = true;
				} else {
					$username = $e['memberdata']->Username;
					$myself = false;
				}
				?>
                    <?php echo MOD_layoutbits::PIC_30_30($e['memberdata']->Username,'',$style='') ?><br />
                    <span title="<?php echo $e['memberdata']->City; ?>, <?php echo $e['memberdata']->Country; ?>" class="username"><?php echo '<a href="members/'.$e['memberdata']->Username.'">'.$username.'</a>' ?></span>
                </p>
            </div> <!-- float_left -->
	<?php } }?>
        </div> <!-- floatbox -->
<?php } ?>		
    </div> <!-- picture-linkpath -->