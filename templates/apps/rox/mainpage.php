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
//new MOD_old_bw_func(); // Just to have the rox mecanism to include the needed functions


?>

<div class="subcolumns main_preposts">
    <div class="c25l">
        <div class="subc">
            <?php
                // Display the last created members with a picture
                $m=MOD_visits::get()->RetrieveLastAcceptedProfileWithAPicture() ;
            ?>
            <h3><?php echo $words->getFormatted('RecentMember') ?></h3> 
            <p class="floatbox UserpicFloated">
                <?php echo MOD_layoutbits::linkWithPicture($m->Username,$m->photo); ?>
                <?php echo '<a href="bw/member.php?cid='.$m->Username.'">'.$m->Username.'</a>' ?>
                <br/>
                <?php echo $m->countryname ?> 
            </p> 
        </div> 
    </div> 
    <div class="c75r"> 
        <h3><?php echo $words->get('RecentVisitsOfyourProfile') ?></h3> 
        <?php
            $DivForVisit[0]='c33l' ;
            $DivForVisit[1]='c33l' ;
            $DivForVisit[2]='c33r' ;
            
            // /*###   NEW   To be programmed: show the first visitor, then the second. !! Different div's (c50l, c50r)!  ###
            $last_visits=MOD_visits::get()->BuildLastVisits() ;
            for ($ii=0;$ii<count($last_visits);$ii++) {
                $m=$last_visits[$ii] ;
    	?>
        <div class="<?php echo $DivForVisit[$ii] ?>"> 
            <div class="subc">
                <p class="floatbox UserpicFloated">
                    <?php echo MOD_layoutbits::linkWithPicture($m->Username,$m->photo) ?>
                    <?php echo '<a href="bw/member.php?cid='.$m->Username.'">'.$m->Username.'</a>' ?>
                    <br />
                    <?php echo $m->countryname; ?>
                </p> 
            </div> 
        </div>
        <?php 
            }
        ?>
    </div>
</div>
            
<div class="subcolumns">
    <div class="c66l">
        <div class="subc">
            <h3><?php echo $words->get('News'); ?></h3>               
            <?php
                $newscount=MOD_news::get()->NewsCount() ; 
                for ($ii=$newscount;$ii>$newscount-5;$ii--) {
            ?>
            <h4 class="news"><?php echo $words->get('NewsTitle_'.$ii); ?></h4>
            <span class="small grey"><?php echo MOD_news::get()->NewsDate("NewsTitle_".$ii); ?></span>
            <p><?php echo $words->get('NewsText_'.$ii); ?></p>
            <?php 
                }
            ?>
        </div>
    </div>
    <div class="c33l">
        <div class="subc">
            <?php echo $Forums->showExternalLatest(); ?>
        </div>
		<div class="subc" >
			<h3><?php echo $words->getFormatted('MainMembersMap') ?></h3> 
			<?php
				$markerstr = "";
				foreach ($citylatlong as $key => $val) {
					if ($key!=0) {
						$markerstr .= "%7C";
					}
					$markerstr .= $val->latitude.",".$val->longitude.",green";
				}
				echo "<img alt=\"map with all members\" src=\"http://maps.google.com/staticmap?maptype=mobile&size=500x300&markers=".$markerstr."&key=".$google_conf->maps_api_key."\">\n";
			?>
		</div>
	
    </div>
</div>


