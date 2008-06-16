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
<div class="subcolumns">
    <div class="c50l">
        <div class="subr" id="personallist">
            <!-- Another box -->
            <div class="box">
                <div class="corner"></div>
                
                <h3 class="first" id="two"><a><img class="float_right" onclick="this.parentNode.parentNode.parentNode.childNodes.item(5).toggle()" title="go to last post" alt="go to last post" src="images/icons/box-min1.png"/> <?php echo $words->getFormatted('searchmembersTitle') ?></a></h3>
                <div class="floatbox tog">
                    <form id="searchwidget" name="searchwidget">
                    <fieldset name="searchtop" id="searchtop">
                        <span class="small">
                            <!--<input type="radio" name="SelectedSearchField" value="Address" checked="checked">Address            <input type="radio" name="SelectedSearchField" value="Username">Username            <input type="radio" name="SelectedSearchField" value="TextToFind">Specific words in profile -->
                            <label for="Address">Enter location:</label>
                        </span><br/>
                        <input type="text" name="create-location" id="create-location" value="" size="25" />
                        <div id="location-suggestion"></div>
                        <input type="button" class="button" id="btn-create-location" value="Search" />
                        <span class="small">
                        <a onclick="new Effect.toggle('SearchAdvanced', 'blind');" id="linkadvanced" style="cursor: pointer;">Advanced search</a><br/>
                        </span>
                        

<script src="http://maps.google.com/maps?file=api&amp;v=2&amp;key=<?php
    $google_conf = PVars::getObj('config_google');
    if (!$google_conf || !$google_conf->maps_api_key) {
        throw new PException('Google config error!');
    }
    echo $google_conf->maps_api_key;

?>" type="text/javascript"></script>
         <script type="text/javascript">
         var map = null;
    

    var loaded = false;


    function setGeonameIdInForm(geonameid, latitude, longitude, geonamename, countrycode, admincode) {
        $('geonameid').value = geonameid;
        $('latitude').value = latitude;
        $('longitude').value = longitude;
        $('geonamename').value = geonamename;
        $('geonamecountrycode').value = countrycode;
        $('admincode').value = admincode;    
    }

    function removeHighlight() {
        var lis = $A($('locations').childNodes);
        lis.each(function(li) {
            Element.setStyle(li, {fontWeight:''});
        });
    }


    window.onunload = GUnload;
    </script>
    <input type="hidden" name="geonameid" id="geonameid" value="<?php 
            echo isset($vars['geonameid']) ? htmlentities($vars['geonameid'], ENT_COMPAT, 'utf-8') : ''; 
        ?>" />
    <input type="hidden" name="latitude" id="latitude" value="<?php 
            echo isset($vars['latitude']) ? htmlentities($vars['latitude'], ENT_COMPAT, 'utf-8') : ''; 
        ?>" />
    <input type="hidden" name="longitude" id="longitude" value="<?php 
            echo isset($vars['longitude']) ? htmlentities($vars['longitude'], ENT_COMPAT, 'utf-8') : ''; 
        ?>" />
    <input type="hidden" name="geonamename" id="geonamename" value="<?php 
            echo isset($vars['geonamename']) ? htmlentities($vars['geonamename'], ENT_COMPAT, 'utf-8') : ''; 
        ?>" />
    <input type="hidden" name="geonamecountrycode" id="geonamecountrycode" value="<?php 
            echo isset($vars['geonamecountrycode']) ? htmlentities($vars['geonamecountrycode'], ENT_COMPAT, 'utf-8') : ''; 
        ?>" />
    <input type="hidden" name="admincode" id="admincode" value="<?php 
            echo isset($vars['admincode']) ? htmlentities($vars['admincode'], ENT_COMPAT, 'utf-8') : ''; 
        ?>" />
                        
                    </fieldset>
      </form>
                </div>
                <div class="boxbottom"><div class="author"></div><div class="links"></div></div>
           </div>
            <!-- Another box -->
            <div class="box">
                <div class="corner"></div>
                
                <h3 class="first" id="two"><a><img class="float_right" onclick="this.parentNode.parentNode.parentNode.childNodes.item(5).toggle()" title="go to last post" alt="go to last post" src="images/icons/box-min1.png"/> <?php echo $words->getFormatted('WidgetLatestMessages') ?></a></h3>
                <div class="floatbox tog">
                    <?php $inbox_widget->render() ?>
                    <a href="bw/mymessages.php">more...</a>
                </div>
                <div class="boxbottom"><div class="author"></div><div class="links"></div></div>
           </div>
            <!-- Another box -->
            <div class="box">
                <div class="corner"></div>
                
                <h3 class="first" id="two"><a><img class="float_right" onclick="this.parentNode.parentNode.parentNode.childNodes.item(5).toggle()" title="go to last post" alt="go to last post" src="images/icons/box-min1.png"/> <?php echo $words->getFormatted('ForumRecentPostsLong') ?></a></h3>
                <div class="floatbox">
                    <?php echo $Forums->showExternalLatest(); ?>
                </div>
                <div class="boxbottom"><div class="author"></div><div class="links"></div></div>
           </div>
            <!-- Another box -->
            <div class="box">
                <div class="corner"></div>
                
                <h3 class="first" id="two"><a><img class="float_right" onclick="this.parentNode.parentNode.parentNode.childNodes.item(5).toggle()" title="go to last post" alt="go to last post" src="images/icons/box-min1.png"/> <?php echo $words->getFormatted('News') ?></a></h3>
                <div class="floatbox">
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
                <div class="boxbottom"><div class="author"></div><div class="links"></div></div>
           </div>
        </div>
    </div> 
    <div class="c50r" > 
        <div class="subc" id="personallist2">
            <!-- Another box -->
            <div class="box">
                <div class="corner"></div>
                
                <h3 class="first" id="two"><a><img class="float_right" onclick="this.parentNode.parentNode.parentNode.childNodes.item(5).toggle()" title="go to last post" alt="go to last post" src="images/icons/box-min1.png"/> <?php echo $words->getFormatted('RecentMember') ?></a></h3>
                <div class="floatbox">
                <?php
                    // Display the last created members with a picture
                    $m=MOD_visits::get()->RetrieveLastAcceptedProfileWithAPicture() ;
                ?>
                <p class="floatbox UserpicFloated">
                    <?php echo MOD_layoutbits::PIC_30_30($m->Username,'',$style='float_left framed'); ?>
                    <?php echo '<a href="bw/member.php?cid='.$m->Username.'">'.$m->Username.'</a>' ?>
                    <br/>
                    <?php echo $m->countryname ?> 
                </p> 
                <h3><a href="bw/myvisitors.php"><?php echo $words->get('RecentVisitsOfyourProfile') ?></a></h3> 
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
                    <div class="subr">
                        <p class="floatbox UserpicFloated">
                            <?php echo MOD_layoutbits::PIC_30_30($m->Username,'',$style='float_left framed') ?>
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
                <div class="boxbottom"><div class="author"></div><div class="links"></div></div>
           </div>
            <!-- Another box -->
            <div class="box">
                <div class="corner"></div>
                
                <h3 class="first" id="two"><a><img class="float_right" onclick="this.parentNode.parentNode.parentNode.childNodes.item(5).toggle()" title="go to last post" alt="go to last post" src="images/icons/box-min1.png"/> <?php echo $words->getFormatted('MainMembersMap') ?></a></h3>
                <div class="floatbox">
        			<?php
        				$markerstr = "";
        				foreach ($citylatlong as $key => $val) {
        					if ($key!=0) {
        						$markerstr .= "%7C";
        					}
        					$markerstr .= $val->latitude.",".$val->longitude.",green";
        				}
        				echo "<img alt=\"map with all members\" src=\"http://maps.google.com/staticmap?maptype=mobile&size=400x250&markers=".$markerstr."&key=".$google_conf->maps_api_key."\">\n";
        			?>
                </div>
                <div class="boxbottom"><div class="author"></div><div class="links"></div></div>
           </div>
           
        </div> 
    </div>
</div>

<script type="text/javascript">

Sortable.create('personallist', {
    tag:'div',
    containment: ['personallist','personallist2'],
    constraint: false,
    dropOnEmpty: true /*,
	onUpdate:function(){
		new Ajax.Updater('list-info', 'trip/reorder/', {
			onComplete:function(request){
				new Effect.Highlight('triplist',{});
				params = Sortable.serialize('triplist').toQueryParams();
				points = Object.values(params).toString().split(',');
				setPolyline();
				
			}, 
			parameters:Sortable.serialize('triplist'), 
			evalScripts:true, 
			asynchronous:true,
			method: 'get'
		})
	}*/
})
Sortable.create('personallist2', {
    tag:'div',
    containment: ['personallist','personallist2'],
    constraint: false,
    dropOnEmpty: true /*,
	onUpdate:function(){
		new Ajax.Updater('list-info', 'trip/reorder/', {
			onComplete:function(request){
				new Effect.Highlight('triplist',{});
				params = Sortable.serialize('triplist').toQueryParams();
				points = Object.values(params).toString().split(',');
				setPolyline();
				
			}, 
			parameters:Sortable.serialize('triplist'), 
			evalScripts:true, 
			asynchronous:true,
			method: 'get'
		})
	}*/
})
</script>
<script type="text/javascript">//<!--
BlogSuggest.initialize('searchwidget');
//-->
</script>

