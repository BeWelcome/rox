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
$words = new MOD_words();
?>

<div id="index">
  <div class="subcolumns">
    <div class="c50l">
      <div class="subcl">
    
          <div class="subcolumns">
            <div class="c25l">
              <div class="subcl">
            <a href="tour/share"><img src="images/tour/arrow_door_mini.png" alt="<?php echo $words->get('IndexPageWord_share');?>" /></a>
              </div> <!-- subcl -->
            </div> <!-- c50l -->

            <div class="c75r">
              <div class="subcr">
                <h3><?php echo $words->get('IndexPageWord_share');?></h3>
                <p><?php echo $words->get('IndexPageWord_shareText');?></p>
              </div>
            </div>
          </div>
          <div class="subcolumns">
            <div class="c25l">
              <div class="subcl">
            <a href="tour/trips"><img src="images/tour/arrow_plan_mini.png" alt="<?php echo $words->get('IndexPageWord_plan');?>" /></a>
              </div> <!-- subcl -->
            </div> <!-- c50l -->

            <div class="c75r">
              <div class="subcr">
                <h3><?php echo $words->get('IndexPageWord_plan');?></h3>
                <p><?php echo $words->get('IndexPageWord_planText');?></p>
              </div>
            </div>
          </div>

      </div> <!-- subcl -->
    </div> <!-- c50l -->

    <div class="c50r">
      <div class="subcr">
          <div class="floatbox">
<?php 
$Rox = new Rox();
$members = $Rox->getMembersStartpage(2,'random');
for ($ii=0;$ii<count($members);$ii++) {
        $m=$members[$ii] ;
?>
<div class="subcolumns">
  <div class="c25l">
    <div class="subcl" style="margin: 1em 0 1em 0">
    <?php echo MOD_layoutbits::PIC_50_50($m->Username,'','float_right framed') ?>
    </div> <!-- subcl -->
  </div> <!-- c50l -->

  <div class="c75r">
    <div class="subcr" style="margin: 1em 2em 1em 0">
        <div class="userinfo">
            <h3><?php echo '<a rel="nofollow" href="members/'.$m->Username.'">'.$words->get('IndexPageWord_shareplace',$m->Username).'</a>' ?></h3>
            <p>in <?php echo $m->cityname; ?>, <?php echo $m->countryname; ?></p>
          </div>
      </div>
    </div>
</div>

<?php } ?>
<div class="subcolumns">
  <div class="c25l">
    <div class="subcl" style="margin: 1em 0 1em 0">
    </div> <!-- subcl -->
  </div> <!-- c50l -->

  <div class="c75r">
    <div class="subcr" style="margin: 1em 2em 1em 0">
        <p style="padding-top: 5px"><?=$words->get('IndexPageWord_MembersText','<a href="searchmembers">','</a>')?></p>
      </div>
    </div>
</div>
      </div> <!-- subcr -->
    </div> <!-- c50r -->
  </div> <!-- subcolumns index_row1 -->
</div> <!-- index -->
