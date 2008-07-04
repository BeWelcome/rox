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

    <div id="teaser" class="clearfix teaser_main">
        <h2><?=$words->getFormatted('HelloUsername', $_SESSION['Username'])?></h2>
        <div class="subcolumns">
            <div class="c38l">
                <div class="subcl">
                    <div class="floatbox">
                        <img src="<?=$thumbPathMember?>" id="MainUserpic" class="float_left" alt="ProfilePicture" style="margin-right: 10px;" />
                        <p>
                        <?php if (isset($_mainPageNewMessagesMessage)) { ?>
                            <a href="bw/mymessages.php"><img src="images/icons/icons1616/icon_contactmember.png" alt="Messages"/><?=$_mainPageNewMessagesMessage?></a><br />
                        <?php } ?>
                        Start now to <a href="searchmembers">Search for members</a> (by place or username) in the field on the right, <a href="addafriend">add a friend</a> or <a href="explore">explore BeWelcome</a>! If you hosting status changed, use the dropdown on the right or go <a href="searchmembers">edit your profile</a>
                        </p>
    
                    </div> <!-- floatbox -->
                </div> <!-- subcl -->
            </div> <!-- c38l -->

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

    /* Instead we use this temporary solution */
?>
            <div class="c62r">
                <div class="subcr">
                    <div id="mapsearch">
                    <!--
                        <ul class="search-options">
                            <li id="tab1" class="selected"><a href="#" onclick="javascript:setSearchOptions(1);"><img src="images/icons/user.png" > Members</a></li>
                            <li id="tab2"><a href="#" onclick="javascript:setSearchOptions(2);"><img src="images/icons/group.png"> Groups</a></li>
                            <li id="tab3"><a href="#" onclick="javascript:setSearchOptions(3);"><img src="images/icons/world.png"> Places</a></li>
                        </ul> 
                        -->
                        <div id="search-bar">
                            <form id="form1" name="form1" method="post" action="searchmembers/quicksearch">
                            <div><input name="searchtext" type="text" class="search-style" id="searchq" size="30" value="Search for hosts, travellers..." onfocus="this.value='';" /></div>
                            <input type="hidden" name="quicksearch_callbackId" value="1"/>
                            <input type="hidden" name="searchopt" id="searchopt" /><br />
                            <input type="submit" value="Search" id="btn-create-location" class="button"/>
                            </form>
                        </div>

                    </div> <!-- mapsearch -->
                </div> <!-- subcr -->
            </div> <!-- c62r -->
        </div> <!-- subcolumns -->
    </div> <!-- teaser -->

<style type="text/css">
p{padding:6px 0 20px 0;}
#search-bar{padding:0; clear:both;}
#search-bar div{  background:#FFC04A; padding:5px; }
#search-bar .search-style{font-size:16px; color:#333; border:solid 1px #CCCCCC; padding:4px;}
ul.search-options, ul.search-options li{padding:0; border:0; margin:0; list-style:none;}
ul.search-options{clear:both;}
#teaser ul.search-options li a,#teaser ul.search-options li a.hover,#teaser ul.search-options li a.focus{float:left; margin-right:1px; width:auto; background:#FFD689; padding:8px; color:#fff; text-decoration:none; font-weight:bold;}
#teaser .selected a{background:#FFC04A; color:#fff;}
#teaser ul.search-options li.selected a{background:#FFC04A; color:#fff;}
</style>
<script language="javascript">
function setSearchOptions(idElement){
	/* Total Tabs above the input field (in this case there are 3 tabs: web, images, videos) */
	tot_tab = 3;
	tab		= document.getElementById('tab'+idElement);
	search_option = document.getElementById('searchopt');
	for(i=1; i<=3; i++){
		if(i==idElement){
			tab.setAttribute("class","selected");
			search_option.value=idElement;
		} else {
			document.getElementById('tab'+i).setAttribute("class","");
		}
	}
}
</script>
