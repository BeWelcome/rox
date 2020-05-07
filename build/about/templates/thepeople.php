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
write to the Free Software Foundation, Inc., 59 Temple PlaceSuite 330, 
Boston, MA  02111-1307, USA.

*/
$words = new MOD_words();
?>
<div class="row">
        <div class="col-12">
            <h2><?php echo $words->get("ThePeople") ?></h2>
        </div>
    <div class="col-12 col-md-6">
        <h3><?php echo $words->get("ThePeople_Title1") ?></h3>
        <p><?php echo $words->get("ThePeople_Text1")?></p>
    </div>    
    <div class="col-12 col-md-6">
        <h3><?php echo $words->get("ThePeople_TitleInterviews") ?></h3>
        <p><?php echo $words->getFormatted('ThePeople_TextInterviews','<a href="http://www.bevolunteer.org/wiki/Interviews">','</a>')?></p>
    </div>
    <div class="col-12">
        <!-- bootstrap carousel !-->
        <div id="carousel-team" class="carousel slide" data-ride="carousel">
            <ol class="carousel-indicators">
                <li data-target="#carousel-team" data-slide-to="0" class="active"></li>
                <li data-target="#carousel-team" data-slide-to="1"></li>
                <li data-target="#carousel-team" data-slide-to="2"></li>
                <li data-target="#carousel-team" data-slide-to="3"></li>
                <li data-target="#carousel-team" data-slide-to="4"></li>
            </ol>
            <div class="carousel-inner">
                <div class="carousel-item active">
                    <img class="d-block w-100" src="http://farm3.static.flickr.com/2200/2204242456_f2a726c103.jpg">
                </div>
                <div class="carousel-item">
                    <img class="d-block w-100" src="http://farm3.static.flickr.com/2275/2204241012_756d5234e6.jpg">
                </div>
                <div class="carousel-item">
                    <img class="d-block w-100" src="http://farm3.static.flickr.com/2157/2204319026_8764a0573a.jpg">
                </div>
                <div class="carousel-item">
                    <img class="d-block w-100" src="http://farm3.static.flickr.com/2161/2206203954_9a511d50d2.jpg">
                </div>
                <div class="carousel-item">
                    <img class="d-block w-100" src="http://farm3.static.flickr.com/2179/2204240888_d90054b31a.jpg">
                </div>
            </div>
            <a class="carousel-control-prev" href="#carousel-team" role="button" data-slide="prev">
                <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                <span class="sr-only">Previous</span>
            </a>
            <a class="carousel-control-next" href="#carousel-team" role="button" data-slide="next">
                <span class="carousel-control-next-icon" aria-hidden="true"></span>
                <span class="sr-only">Next</span>
            </a>
        </div><!-- end: bootstrap carousel !-->
            <div class=" pt-1 caption">
                    <p class="small"><?php echo $words->getFormatted('ThePeople_PictureText1','<a title="unconference: A meeting with a non-fixed schedule that can be changed all the time.">','</a>') ?>
                    <?php echo $words->getFormatted('ThePeople_PictureText2','<a href="http://www.flickr.com/photos/22828233@N05/">','</a>') ?></p>
            </div>
    </div>
    <div class="col-12">
        <h3><?php echo $words->get("ThePeople_Title3") ?></h3>
        <p><?php echo $words->get("ThePeople_Text3")?></p>
    </div>
</div>
	 