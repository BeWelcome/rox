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

<div id="teaser" class="page-teaser clearfix">
    <div class="bw-row">
        <?echo $thumbPathMember;?>
        <div><h1>&nbsp&nbsp<?=$words->getSilent('HelloUsername',$this->_session->get('Username'))?></h1></div>
    </div>
    <div class="subcolumns" style="margin-bottom: 2em">
        <div class="c33l">
            <div class="subcl">
                <ul id="personalmenu" class="nav nav-pills nav-stacked">
                    <li <?php if(!$ShowDonateBar) { echo 'class="active"'; } ?>><a id="tablink1"  href="#tab1" data-toggle="pill"><?=$words->getSilent('NotifyWhatsHappening')?></a></li>
                    <li><a id="tablink2" href="#tab2" data-toggle="pill"><?=$words->getSilent('FindAHost')?></a></li>
                    <li <?php if($ShowDonateBar) { echo 'class="active"'; } ?>><a id="tablink3"  href="#tab3"data-toggle="pill"><?=$words->getSilent('HelpTheProject')?></a></li>
                    <li><a id="tablink5" href="#tab5" data-toggle="pill"><?=$words->getSilent('CheckYourMessages')?>
                    <?php if (isset($_mainPageNewMessagesMessage)) { ?>
                        <span style="color:#333333; font-weight:bold;">(<i title="My messages" class="fa fa-envelope"></i> <?=$_newMessagesNumber?>)</span>
                    <?php } ?>
                    </a></li>
                    <li><a id="tablink6" href="#tab6" data-toggle="pill"><?=$words->getSilent('InviteAFriend')?></a></li>
                </ul>
            </div> <!-- subcl -->
        </div> <!-- c33l -->

        <div class="c66r">
            <div class="tab-content">

                <div class="tab-pane <?php echo ($this->_session->has( 'Param' ) && ($this->_session->get('Param')->ToggleDonateBar)) ? '' : ' active' ?>" id="tab1">
                    <?php
                    $notify_widget->render();
                    ?>
                </div> <!-- tab1 -->

                <div class="tab-pane" id="tab2">
                    <div class="subcolumns">
                        <div class="c50l">
                            <div class="subcl">
                                <h3><?php echo $words->getFormatted('FindMembers') ?></h3>
                                <p><?php echo $words->get('StartPageSearchMembers');?></p>
                            </div> <!-- subcl -->
                        </div> <!-- c50l -->

                        <div class="c50r">
                            <div class="subcr">

                                <div id="browsecities_dropdown">
                                    <h3><?php echo $words->getFormatted('StartpageBrowseCountries') ?></h3>
                                    <select class="form-control" onchange="window.location.href=this.value; return false">
                                    <?php foreach ($Countries as $continent => $countries_group) {
                                        if ($continent=='AF') { $label =$words->getSilent('Africa');}
                                        if ($continent=='AN') { $label =$words->getSilent('Antarctica');}
                                        if ($continent=='EA') { $label = $words->getSilent('Eurasia');}
                                        if ($continent=='AM') { $label = $words->getSilent('Americas');}
                                        if ($continent=='OC') { $label = $words->getSilent('Oceania');}
                                        echo '<optgroup label="' . $label . '">';
                                        foreach ($countries_group as $country) { ?>
                                             <option label="<?=$country->name?>" value="places/<?=$country->name?>/<?=$country->country?>"><?=$country->name?> <?=($country->number != 0) ? ('('.$country->number.')') : ''?></option>
                                    <?php } ?>
                                    </optgroup>
                                    <?php } ?>
                                    </select>
                                </div> <!-- browsecities_dropdown -->
                                <script type="text/javascript">
                                $('browsecities_dropdown').show();
                                </script>

                            </div> <!-- subcr -->
                        </div> <!--c50r -->
                    </div> <!-- subclumns -->
                </div> <!-- tab2 -->

                <div class="tab-pane <?php echo ($this->_session->has( 'Param' ) && ($this->_session->get('Param')->ToggleDonateBar)) ? ' active ' : '' ?>" id="tab3">

                    <div class="subcolumns">
                        <div class="c50l">
                            <div class="subcl">

                                <? include 'userbar_donate.php' ; // the Donation bar ?>

                            </div> <!-- subcl -->
                        </div> <!-- c50l -->

                        <div class="c50r">
                            <div class="subcr">

                                <h3><?=$words->get('StartpageVolunteer')?></h3>
                                <p><?=$words->get('GetActiveIntro')?></p>
                                <a href="getactive" class="button" title="getactive"><?=$words->get('GetActive')?></a>

                            </div> <!-- subcr -->
                        </div> <!-- c50r -->
                    </div> <!-- subcolumns -->
                </div> <!-- tab3 -->

                <div class="tab-pane " id="tab3a">
                    <div class="subcolumns">
                        <div class="c50l">
                            <div class="subcl">

                                <h3><?=$words->get('HostingStatus')?></h3>
                                <form id="hostingstatus" name="hostingstatus" method="post" action="searchmembers/quicksearch">
                                    <select name="top5">
                                        <option>I'm hosting</option>
                                        <option>Can't host</option>
                                        <option>Maybe</option>
                                        <option>I'm travelling</option>
                                    </select>
                                    <input type="hidden" name="quicksearch_callbackId" value="1"/>
                                    <input type="hidden" name="searchoptstatus" id="searchoptstatus" />
                                </form>

                            </div> <!-- subcl -->
                        </div> <!-- c50l -->

                        <div class="c50r">
                            <div class="subcr">

                                <h3>Edit your profile</h3>
                                <p>Edit your profile now and describe in detail what you can offer, where you live, etc.</p>
                                <div id="hostrequest">
                                    <a href="requesthosting" class="button">EditMyProfile</a>
                                </div>

                            </div> <!-- subcr -->
                        </div> <!-- c50r -->
                    </div> <!-- subclumns -->
                </div> <!-- tab3a -->
                <div class="tab-pane " id="tab5">

                <?php $inbox_widget->render() ?>
                <p><a href="messages"><?php echo $words->get('PersonalStartMessageMore');?></a></p>

                </div> <!-- tab5 -->

                <div class="tab-pane " id="tab6">
                    <p class="big"><?php echo $words->get('InviteAFriendInfo');?></p>
                </div> <!-- tab5 -->

            </div> <!-- subcr -->
        </div> <!-- c66r -->
    </div> <!-- subcolumns -->

    <div><?=$words->flushBuffer()?></div>
</div> <!-- teaser -->
<script type="text/javascript">
$('#myTab a[href="#tab1"]').tab('show')
$('#myTab a[href="#tab2"]').tab('show')
$('#myTab a[href="#tab3"]').tab('show')
$('#myTab a[href="#tab5"]').tab('show')
$('#myTab a[href="#tab6"]').tab('show')
</script>
