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
?>

<div id="teaser" class="clearfix teaser_main">
	<h1><?=$words->getSilent('HelloUsername',$_SESSION['Username'])?></h1>
<!-- Status input field - maybe that's an option later...
	<div class="floatbox" style="display:none;">
		<h1 style="float:left">
			<?=$words->getSilent('HelloUsername',$_SESSION['Username'])?>
		</h1>
		<form style="float:right" id="form1" name="form1" method="post" action="searchmembers/quicksearch">
		<div >
			<input border: 1px solid #f5f5f5" name="searchtext" type="text" class="search-style" id="searchq" size="30" onblur="if (this.value=='') {this.value=statusvalue}" value="" onfocus="statusvalue=this.value; this.value='';" />
			<select name="top5">
			<option name="whatever">I'm hosting</option>
			<option name="asdfho">Can't host</option>
			<option name="asdfho">Maybe</option>
			<option name="asdfho">I'm travelling</option>
			</select>
		</div>
		<input type="hidden" name="quicksearch_callbackId" value="1"/>
		<input type="hidden" name="searchopt" id="searchopt" />
		</form>
	</div>
-->
<div class="subcolumns">
    <div class="c33l">
    <div class="subcl">
    <ul id="personalmenu">
        <li><a id="tablink1" class="active-tab first" href="#tab1">All that's happening</a></li>
        <li><a id="tablink2" href="#tab2"><?=$words->getSilent('FindAHost')?></a></li>
        <li><a id="tablink3" href="#tab3"><?=$words->getSilent('CreateATrip')?></a></li>
        <li><a id="tablink4" href="#tab4"><?=$words->getSilent('CheckYourMessages')?>
	        <?php if (isset($_mainPageNewMessagesMessage)) { ?>
                (<img src="images/icons/icons1616/icon_contactmember.png" alt="Messages"/> <?=$_newMessagesNumber?>) 
            <?php } ?>
	    </a></li>
    </ul>
    </div> <!-- subcl -->
    </div> <!-- c38l -->

<div class="c66r">
    <div class="subcr">
    
			<div class="panel active-tab-body" id="tab1">
                    <?php
                    $notify_widget->render();
                    ?>
        			 <p>

                    </p>
                    
			</div> <!-- tab1 -->
			<div class="panel" id="tab2">

                    <div id="mapsearch">
	
                    <h2><?=$words->get('StartFindingAHost')?></h2>
                    <div id="search-bar">
                        <form id="form1" name="form1" method="get" action="searchmembers">
                        <input name="vars" type="text" class="search-style" id="searchq" size="30" onblur="if(this.value == '') this.value='Search for hosts, places...'" value="Search for hosts, places..." onfocus="this.value='';" />
                        <input type="hidden" name="searchopt" id="searchopt" />
                        <input type="submit" value="Search" id="btn-create-location" class="button"/>
                        </form>
                    </div>
                    <div id="browsecities_dropdown" style="display:none;">
                    <h3>Browse Countries</h3>
                    <select onchange="window.location.href=this.value; return false">
                    <?php foreach ($Countries as $continent => $countries_group) { ?>
	                     <optgroup label="<?=$continent?>">
	                    <?php foreach ($countries_group as $code => $country) { ?>  
		                     <option label="<?=$country['name']?>" value="places/<?=$code?>"><?=$country['name']?> <?=($country['number'] != 0) ? ('('.$country['number'].')') : ''?></option>
		                <?php } ?>
	                    </optgroup>
                    <?php } ?>
                    </select>
                    </div>
					<script type="text/javascript">
					$('browsecities_dropdown').show();
					</script>

                    </div> <!-- mapsearch -->
                    
			</div> <!-- tab2 -->
			<div class="panel" id="tab3">

					<h2>Create a trip</h2>
					<form method="post" action="trip/create" class="def-form">
                            <div class="row">
                                <label for="trip-name"><?=$words->get('TripLabel_name')?></label><br/>
                                <input type="text" id="trip-name" name="n" class="long"<?php
                    if (isset($vars['n']) && $vars['n'])
                        echo ' value="'.htmlentities($vars['n'], ENT_COMPAT, 'utf-8').'"';
                                ?>/>
                                <p class="desc"></p>
                            </div>
                            <div class="row">
                                <label for="trip-desc"><?=$words->get('TripLabel_desc')?></label><br/>
                                <textarea id="trip-desc" name="d" cols="40" rows="4"><?php
                    if (isset($vars['d']) && $vars['d'])
                        echo htmlentities($vars['d'], ENT_COMPAT, 'utf-8');
                                ?></textarea>
                                <p class="desc"><?=$words->get('TripDesc_desc')?></p>
                                
                                <input type="hidden" name="<?=$TripcallbackId?>" value="1"/>
                                <input type="submit" value="<?php echo $editing ? $words->get('TripSubmit_edit') : $words->get('TripSubmit_create');?>"/>
                            </div>
					</form>
					
			</div> <!-- tab3 -->
			<div class="panel" id="tab4">

                <?php $inbox_widget->render() ?>
                <p><a href="messages">more...</a></p>
					
			</div> <!-- tab4 -->

    </div> <!-- subcr -->
</div> <!-- c62r -->
</div> <!-- subcolumns -->
<div><?=$words->flushBuffer()?></div>
</div> <!-- teaser -->

<!--
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
-->
<script language="javascript">

function tabPersonal(idElement){
    /* Total Tabs above the input field (in this case there are 3 tabs: web, images, videos) */
    $$('#personalmenu li').invoke('hide');
    this.show();
}

function observeTabs(tab){
    /* Total Tabs above the input field (in this case there are 3 tabs: web, images, videos) */
    Event.observe(tab,'click',tabPersonal(tab));
}

//$$('#personalmenu li').each(Element,observeTabs);
Event.observe(window,'load',function(){ new Fabtabs('personalmenu'); },false);
</script>
